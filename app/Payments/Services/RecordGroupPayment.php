<?php

namespace App\Payments\Services;

use App\Models\Group;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Models\GroupPaymentEntry;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordGroupPayment
{
    public function __construct(
        private GroupPaymentTotals $totals,
        private AuditService $auditService
    ) {}

    public function recordPayment(Group $group, array $data): GroupPaymentEntry
    {
        return $this->record($group, PaymentEntryType::Payment, $data);
    }

    public function recordRefund(
        Group $group,
        GroupPaymentEntry $sourcePayment,
        array $data
    ): GroupPaymentEntry {
        if ((int) $sourcePayment->event_group_id !== (int) $group->id) {
            throw new InvalidArgumentException('Refund source does not belong to this group.');
        }

        if (! $sourcePayment->isPayment() || ! $sourcePayment->isSucceeded()) {
            throw new InvalidArgumentException('Only successful payments can be refunded.');
        }

        return $this->record($group, PaymentEntryType::Refund, array_merge($data, [
            'reverses_entry_id' => $sourcePayment->id,
            'currency' => $sourcePayment->currency,
        ]), $sourcePayment);
    }

    public function recordReversal(
        Group $group,
        GroupPaymentEntry $sourceEntry,
        array $data = []
    ): GroupPaymentEntry {
        if ((int) $sourceEntry->event_group_id !== (int) $group->id) {
            throw new InvalidArgumentException('Reversal source does not belong to this group.');
        }

        if (! $sourceEntry->isSucceeded()) {
            throw new InvalidArgumentException('Only succeeded entries can be reversed.');
        }

        if ($sourceEntry->isReversal()) {
            throw new InvalidArgumentException('Reversal entries cannot be reversed again.');
        }

        $alreadyReversed = GroupPaymentEntry::query()
            ->where('reverses_entry_id', $sourceEntry->id)
            ->where('type', PaymentEntryType::Reversal->value)
            ->where('status', PaymentEntryStatus::Succeeded->value)
            ->exists();

        if ($alreadyReversed) {
            throw new InvalidArgumentException('This entry has already been reversed.');
        }

        return $this->record($group, PaymentEntryType::Reversal, array_merge($data, [
            'amount' => $sourceEntry->amount,
            'currency' => $sourceEntry->currency,
            'method' => $sourceEntry->method,
            'reference' => $data['reference'] ?? $sourceEntry->reference,
            'reverses_entry_id' => $sourceEntry->id,
            'status' => PaymentEntryStatus::Succeeded->value,
        ]), $sourceEntry);
    }

    private function record(
        Group $group,
        PaymentEntryType $type,
        array $data,
        ?GroupPaymentEntry $sourceEntry = null
    ): GroupPaymentEntry {
        return DB::transaction(function () use ($group, $type, $data, $sourceEntry) {
            /** @var Group $locked */
            $locked = Group::query()
                ->whereKey($group->id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = round((float) ($data['amount'] ?? 0), 2);
            $status = PaymentEntryStatus::from($data['status'] ?? PaymentEntryStatus::Succeeded->value);
            $currency = strtoupper((string) ($data['currency'] ?? $locked->billingCurrency()));

            if ($amount <= 0) {
                throw new InvalidArgumentException('Amount must be greater than zero.');
            }

            if ($locked->billingCurrency() && strtoupper($locked->billingCurrency()) !== $currency) {
                throw new InvalidArgumentException('Payment currency must match the group currency.');
            }

            if ($type === PaymentEntryType::Refund && $status === PaymentEntryStatus::Succeeded) {
                $refundable = $sourceEntry
                    ? $this->refundableAmountForPayment($locked, $sourceEntry)
                    : $this->totals->calculate($locked)['net_paid'];

                if ($amount - $refundable > 0.009) {
                    throw new InvalidArgumentException(
                        'Refund amount exceeds the refundable balance of '.number_format($refundable, 2).'.'
                    );
                }
            }

            if ($type === PaymentEntryType::Reversal && $sourceEntry) {
                $amount = round((float) $sourceEntry->amount, 2);
            }

            $entry = GroupPaymentEntry::create([
                'event_id' => $locked->event_id,
                'org_id' => $locked->org_id,
                'event_group_id' => $locked->id,
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

            $this->syncGroupSummary($locked);

            $this->auditService->log(
                'created',
                $entry,
                [
                    'event_group_id' => $locked->id,
                    'type' => $type->value,
                    'status' => $status->value,
                    'amount' => $amount,
                ],
                "Recorded {$type->value} of {$amount} {$currency} for group {$locked->group_name}"
            );

            return $entry->fresh();
        });
    }

    public function syncGroupSummary(Group $group): void
    {
        $totals = $this->totals->calculate($group);
        $latestSucceeded = $group->paymentEntries()
            ->where('status', PaymentEntryStatus::Succeeded->value)
            ->reorder()
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->first();

        $group->forceFill([
            'payment_status' => $totals['summary_status']->value,
            'payment_method' => $latestSucceeded?->method ?? $group->payment_method,
            'payment_reference' => $latestSucceeded?->reference ?? $group->payment_reference,
            'payment_date' => $latestSucceeded?->occurred_at ?? $group->payment_date,
            'currency' => $totals['currency'],
        ])->save();
    }

    public function refundableAmountForPayment(Group $group, GroupPaymentEntry $payment): float
    {
        if (! $payment->isPayment() || ! $payment->isSucceeded()) {
            return 0.0;
        }

        $alreadyRefundedOrReversed = (float) GroupPaymentEntry::query()
            ->where('event_group_id', $group->id)
            ->where('reverses_entry_id', $payment->id)
            ->where('status', PaymentEntryStatus::Succeeded->value)
            ->whereIn('type', [PaymentEntryType::Refund->value, PaymentEntryType::Reversal->value])
            ->sum('amount');

        return round(max(0, (float) $payment->amount - $alreadyRefundedOrReversed), 2);
    }
}
