<?php

namespace App\Finance\Integrations;

use App\Finance\Enums\FinanceTransactionDirection;
use App\Finance\Enums\FinanceTransactionStatus;
use App\Finance\Enums\FinanceTransactionType;
use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceTransaction;
use App\Finance\Services\RecordFinanceTransaction;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Shared\Tenancy\TenantContext;
use InvalidArgumentException;

class RegistrationPaymentProjector
{
    public function __construct(
        private RecordFinanceTransaction $transactions,
    ) {}

    public function project(
        RegistrationPaymentEntry $entry,
        ?FinanceAccount $account = null,
    ): FinanceTransaction {
        TenantContext::fromConfig()->assertMatches((int) $entry->event_id, (int) $entry->org_id);
        $entry->loadMissing('reversesEntry');

        $account ??= FinanceAccount::query()
            ->where('currency', strtoupper($entry->currency))
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            throw new InvalidArgumentException(
                "A default {$entry->currency} financial account is required before importing registration payments."
            );
        }

        return $this->transactions->record([
            'account_id' => $account->id,
            'direction' => $this->direction($entry)->value,
            'type' => match ($entry->type) {
                PaymentEntryType::Payment => FinanceTransactionType::Payment->value,
                PaymentEntryType::Refund => FinanceTransactionType::Refund->value,
                PaymentEntryType::Reversal => FinanceTransactionType::Reversal->value,
            },
            'status' => $entry->status === PaymentEntryStatus::Succeeded
                ? FinanceTransactionStatus::Completed->value
                : FinanceTransactionStatus::Failed->value,
            'amount' => $entry->amount,
            'currency' => $entry->currency,
            'source_type' => 'registration_payment_entry',
            'source_id' => $entry->id,
            'source_public_id' => $entry->public_id,
            'source_key' => 'registration-payment:'.$entry->public_id,
            'reference' => $entry->reference,
            'occurred_at' => $entry->occurred_at,
            'description' => 'Registration payment ledger entry',
            'recorded_by_type' => $entry->recorded_by_type,
            'recorded_by_id' => $entry->recorded_by_id,
            'metadata' => [
                'registration_id' => $entry->registration_id,
                'registration_payment_type' => $entry->type->value,
                'reverses_entry_public_id' => $entry->reversesEntry?->public_id,
            ],
        ]);
    }

    private function direction(RegistrationPaymentEntry $entry): FinanceTransactionDirection
    {
        return match ($entry->type) {
            PaymentEntryType::Payment => FinanceTransactionDirection::Incoming,
            PaymentEntryType::Refund => FinanceTransactionDirection::Outgoing,
            PaymentEntryType::Reversal => $entry->reversesEntry?->type === PaymentEntryType::Refund
                ? FinanceTransactionDirection::Incoming
                : FinanceTransactionDirection::Outgoing,
        };
    }
}
