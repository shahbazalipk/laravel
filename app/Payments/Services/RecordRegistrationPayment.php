<?php

namespace App\Payments\Services;

use App\Models\Registration;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Events\RegistrationPaymentRecorded;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordRegistrationPayment
{
    public function __construct(
        private RegistrationPaymentTotals $totals,
        private AuditService $auditService
    ) {}

    public function recordPayment(Registration $registration, array $data): RegistrationPaymentEntry
    {
        return $this->record($registration, PaymentEntryType::Payment, $data);
    }

    public function recordRefund(
        Registration $registration,
        RegistrationPaymentEntry $sourcePayment,
        array $data
    ): RegistrationPaymentEntry {
        if ($sourcePayment->registration_id !== $registration->id) {
            throw new InvalidArgumentException('Refund source does not belong to this registration.');
        }

        if (! $sourcePayment->isPayment() || ! $sourcePayment->isSucceeded()) {
            throw new InvalidArgumentException('Only successful payments can be refunded.');
        }

        return $this->record($registration, PaymentEntryType::Refund, array_merge($data, [
            'reverses_entry_id' => $sourcePayment->id,
            'currency' => $sourcePayment->currency,
        ]), $sourcePayment);
    }

    public function recordReversal(
        Registration $registration,
        RegistrationPaymentEntry $sourceEntry,
        array $data = []
    ): RegistrationPaymentEntry {
        if ($sourceEntry->registration_id !== $registration->id) {
            throw new InvalidArgumentException('Reversal source does not belong to this registration.');
        }

        if (! $sourceEntry->isSucceeded()) {
            throw new InvalidArgumentException('Only succeeded entries can be reversed.');
        }

        if ($sourceEntry->isReversal()) {
            throw new InvalidArgumentException('Reversal entries cannot be reversed again.');
        }

        $alreadyReversed = RegistrationPaymentEntry::query()
            ->where('reverses_entry_id', $sourceEntry->id)
            ->where('type', PaymentEntryType::Reversal->value)
            ->where('status', PaymentEntryStatus::Succeeded->value)
            ->exists();

        if ($alreadyReversed) {
            throw new InvalidArgumentException('This entry has already been reversed.');
        }

        return $this->record($registration, PaymentEntryType::Reversal, array_merge($data, [
            'amount' => $sourceEntry->amount,
            'currency' => $sourceEntry->currency,
            'method' => $sourceEntry->method,
            'reference' => $data['reference'] ?? $sourceEntry->reference,
            'reverses_entry_id' => $sourceEntry->id,
            'status' => PaymentEntryStatus::Succeeded->value,
        ]), $sourceEntry);
    }

    private function record(
        Registration $registration,
        PaymentEntryType $type,
        array $data,
        ?RegistrationPaymentEntry $sourceEntry = null
    ): RegistrationPaymentEntry {
        return DB::transaction(function () use ($registration, $type, $data, $sourceEntry) {
            /** @var Registration $locked */
            $locked = Registration::query()
                ->whereKey($registration->id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = round((float) ($data['amount'] ?? 0), 2);
            $status = PaymentEntryStatus::from($data['status'] ?? PaymentEntryStatus::Succeeded->value);
            $currency = strtoupper((string) ($data['currency'] ?? $locked->currency ?? 'AED'));

            if ($amount <= 0) {
                throw new InvalidArgumentException('Amount must be greater than zero.');
            }

            if ($locked->currency && strtoupper($locked->currency) !== $currency) {
                throw new InvalidArgumentException('Payment currency must match the registration currency.');
            }

            if ($type === PaymentEntryType::Refund && $status === PaymentEntryStatus::Succeeded) {
                $totals = $this->totals->calculate($locked);
                $refundable = $sourceEntry
                    ? $this->refundableAmountForPayment($locked, $sourceEntry)
                    : $totals['net_paid'];

                if ($amount - $refundable > 0.009) {
                    throw new InvalidArgumentException(
                        'Refund amount exceeds the refundable balance of '.number_format($refundable, 2).'.'
                    );
                }
            }

            if ($type === PaymentEntryType::Reversal && $sourceEntry) {
                $amount = round((float) $sourceEntry->amount, 2);
            }

            $entry = RegistrationPaymentEntry::create([
                'event_id' => $locked->event_id,
                'org_id' => $locked->org_id,
                'registration_id' => $locked->id,
                'type' => $type,
                'status' => $status,
                'amount' => $amount,
                'currency' => $currency,
                'method' => $data['method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'reverses_entry_id' => $data['reverses_entry_id'] ?? null,
                'recorded_by_type' => $data['recorded_by_type'] ?? (session('admin_type') ?: null),
                'recorded_by_id' => $data['recorded_by_id'] ?? session('admin_id'),
                'recorded_by_name' => $data['recorded_by_name'] ?? session('admin_name'),
                'recorded_by_email' => $data['recorded_by_email'] ?? session('admin_email'),
                'metadata' => $data['metadata'] ?? null,
            ]);

            $this->syncRegistrationSummary($locked);

            $this->auditService->log(
                'created',
                $entry,
                [
                    'registration_id' => $locked->id,
                    'type' => $type->value,
                    'status' => $status->value,
                    'amount' => $amount,
                ],
                "Recorded {$type->value} of {$amount} {$currency} for registration {$locked->registration_number}"
            );

            event(new RegistrationPaymentRecorded(
                $entry->public_id,
                (int) $entry->event_id,
                (int) $entry->org_id,
            ));

            return $entry->fresh();
        });
    }

    public function syncRegistrationSummary(Registration $registration): void
    {
        $totals = $this->totals->calculate($registration);
        $latestSucceeded = $registration->paymentEntries()
            ->where('status', PaymentEntryStatus::Succeeded->value)
            ->reorder()
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->first();

        $registration->forceFill([
            'payment_status' => $totals['summary_status']->value,
            'payment_method' => $latestSucceeded?->method ?? $registration->payment_method,
            'payment_reference' => $latestSucceeded?->reference ?? $registration->payment_reference,
            'payment_date' => $latestSucceeded?->occurred_at ?? $registration->payment_date,
            'currency' => $totals['currency'],
        ])->save();
    }

    public function refundableAmountForPayment(
        Registration $registration,
        RegistrationPaymentEntry $payment
    ): float {
        if (! $payment->isPayment() || ! $payment->isSucceeded()) {
            return 0.0;
        }

        $alreadyRefundedOrReversed = (float) RegistrationPaymentEntry::query()
            ->where('registration_id', $registration->id)
            ->where('reverses_entry_id', $payment->id)
            ->where('status', PaymentEntryStatus::Succeeded->value)
            ->whereIn('type', [PaymentEntryType::Refund->value, PaymentEntryType::Reversal->value])
            ->sum('amount');

        return round(max(0, (float) $payment->amount - $alreadyRefundedOrReversed), 2);
    }
}
