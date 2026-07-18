<?php

namespace App\Finance\Services;

use App\Finance\Enums\FinanceTransactionDirection;
use App\Finance\Enums\FinanceTransactionType;
use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceInvoice;
use App\Finance\Models\FinancePayment;
use App\Finance\Models\FinancePaymentAllocation;
use App\Finance\Models\FinanceRefund;
use App\Shared\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinancePaymentService
{
    public function __construct(
        private readonly MoneyService $money,
        private readonly FinanceNumberService $numbers,
        private readonly RecordFinanceTransaction $transactions,
        private readonly AuditLogger $audit,
    ) {}

    public function recordFor(Model $document, array $data): FinancePayment
    {
        [$type, $direction, $outstanding] = $this->documentData($document);
        $amount = $this->money->positive($data['amount']);
        if (bccomp($amount, $outstanding, 4) === 1) {
            throw ValidationException::withMessages(['amount' => 'Payment exceeds the outstanding amount.']);
        }

        $account = FinanceAccount::query()->where('is_active', true)->findOrFail($data['account_id']);
        if ($account->currency !== $document->currency) {
            throw ValidationException::withMessages(['account_id' => 'Account and document currencies must match.']);
        }

        return DB::transaction(function () use ($document, $data, $type, $direction, $amount, $account): FinancePayment {
            $payment = FinancePayment::query()->create([
                'number' => $this->numbers->next('payment', 'PAY'),
                'account_id' => $account->id,
                'direction' => $direction,
                'amount' => $amount,
                'allocated_amount' => $amount,
                'currency' => $document->currency,
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'counterparty' => $data['counterparty'] ?? null,
                'payment_date' => $data['payment_date'],
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'recorded_by' => session('admin_id'),
            ]);

            FinancePaymentAllocation::query()->create([
                'payment_id' => $payment->id,
                'allocatable_type' => $type,
                'allocatable_id' => $document->id,
                'allocatable_public_id' => $document->public_id,
                'amount' => $amount,
                'allocated_at' => now(),
                'allocated_by' => session('admin_id'),
            ]);

            $newPaid = bcadd($document->paid_amount, $amount, 4);
            $fullyPaid = bccomp($newPaid, $document->total_amount, 4) >= 0;
            $statusField = $type === 'invoice' ? 'status' : 'payment_status';
            $document->forceFill([
                'paid_amount' => $newPaid,
                $statusField => $fullyPaid ? 'paid' : 'partially_paid',
            ])->save();

            $this->transactions->record([
                'account_id' => $account->id,
                'direction' => $direction,
                'type' => FinanceTransactionType::Payment->value,
                'amount' => $amount,
                'currency' => $document->currency,
                'source_type' => 'finance_payment',
                'source_id' => $payment->id,
                'source_public_id' => $payment->public_id,
                'source_key' => 'finance-payment:'.$payment->public_id,
                'reference' => $payment->reference,
                'occurred_at' => $payment->payment_date,
                'description' => "Payment allocated to {$type} {$document->number}",
            ]);

            $this->audit->record('finance', 'payment.recorded', $payment, after: [
                'document_type' => $type,
                'document_public_id' => $document->public_id,
                'amount' => $amount,
                'currency' => $document->currency,
            ]);

            return $payment->load(['account', 'allocations']);
        });
    }

    public function refund(FinanceInvoice $invoice, array $data): FinanceRefund
    {
        $amount = $this->money->positive($data['amount']);
        if (bccomp($amount, $invoice->paid_amount, 4) === 1) {
            throw ValidationException::withMessages(['amount' => 'Refund exceeds the invoice paid amount.']);
        }

        $account = FinanceAccount::query()->where('is_active', true)->findOrFail($data['account_id']);
        if ($account->currency !== $invoice->currency) {
            throw ValidationException::withMessages(['account_id' => 'Account and invoice currencies must match.']);
        }

        return DB::transaction(function () use ($invoice, $data, $amount, $account): FinanceRefund {
            $refund = FinanceRefund::query()->create([
                'number' => $this->numbers->next('refund', 'REF'),
                'account_id' => $account->id,
                'source_type' => 'invoice',
                'source_public_id' => $invoice->public_id,
                'amount' => $amount,
                'currency' => $invoice->currency,
                'reason' => $data['reason'],
                'reference' => $data['reference'] ?? null,
                'status' => 'completed',
                'refunded_at' => now(),
                'refunded_by' => session('admin_id'),
            ]);

            $remainingPaid = bcsub($invoice->paid_amount, $amount, 4);
            $invoice->forceFill([
                'paid_amount' => $remainingPaid,
                'status' => bccomp($remainingPaid, '0', 4) === 0 ? 'refunded' : 'partially_paid',
            ])->save();

            $this->transactions->record([
                'account_id' => $account->id,
                'direction' => FinanceTransactionDirection::Outgoing->value,
                'type' => FinanceTransactionType::Refund->value,
                'amount' => $amount,
                'currency' => $invoice->currency,
                'source_type' => 'finance_refund',
                'source_id' => $refund->id,
                'source_public_id' => $refund->public_id,
                'source_key' => 'finance-refund:'.$refund->public_id,
                'reference' => $refund->reference,
                'description' => "Refund for invoice {$invoice->number}",
            ]);

            $this->audit->record('finance', 'refund.completed', $refund, after: [
                'invoice_public_id' => $invoice->public_id,
                'amount' => $amount,
                'currency' => $invoice->currency,
            ]);

            return $refund->load('account');
        });
    }

    private function documentData(Model $document): array
    {
        return match (true) {
            $document instanceof FinanceInvoice => [
                'invoice',
                FinanceTransactionDirection::Incoming->value,
                bcsub($document->total_amount, $document->paid_amount, 4),
            ],
            $document instanceof FinanceBill && $document->approval_status === 'approved' => [
                'bill',
                FinanceTransactionDirection::Outgoing->value,
                bcsub($document->total_amount, $document->paid_amount, 4),
            ],
            $document instanceof FinanceBill => throw ValidationException::withMessages([
                'amount' => 'The bill must be approved before payment.',
            ]),
            default => throw new \InvalidArgumentException('Unsupported payment document.'),
        };
    }
}
