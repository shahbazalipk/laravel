<?php

namespace App\Finance\Services;

use App\Finance\Enums\FinanceTransactionDirection;
use App\Finance\Enums\FinanceTransactionStatus;
use App\Finance\Enums\FinanceTransactionType;
use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceTransaction;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordFinanceTransaction
{
    public function __construct(
        private MoneyService $money,
        private FinanceNumberService $numbers,
        private AuditLogger $audit,
    ) {}

    public function record(array $data): FinanceTransaction
    {
        return DB::transaction(function () use ($data): FinanceTransaction {
            $sourceKey = trim((string) ($data['source_key'] ?? ''));
            if ($sourceKey === '') {
                throw new InvalidArgumentException('A source key is required for idempotency.');
            }
            if (mb_strlen($sourceKey) > 200) {
                throw new InvalidArgumentException('The source key cannot exceed 200 characters.');
            }

            $existing = FinanceTransaction::query()->where('source_key', $sourceKey)->first();
            if ($existing) {
                return $existing;
            }

            $account = FinanceAccount::query()->lockForUpdate()->findOrFail($data['account_id']);
            if (! $account->is_active) {
                throw new InvalidArgumentException('The selected financial account is inactive.');
            }

            $amount = $this->money->positive($data['amount']);
            $currency = $this->money->currency($data['currency'] ?? $account->currency);
            if ($currency !== $account->currency) {
                throw new InvalidArgumentException('Transaction currency must match the financial account currency.');
            }

            $baseCurrency = $this->money->currency($data['base_currency'] ?? $currency);
            $exchangeRate = $this->money->positive($data['exchange_rate'] ?? 1, 8);

            $transaction = FinanceTransaction::query()->create([
                'account_id' => $account->id,
                'number' => $this->numbers->next('transaction', 'FTX'),
                'direction' => FinanceTransactionDirection::from($data['direction']),
                'type' => FinanceTransactionType::from($data['type']),
                'status' => FinanceTransactionStatus::from(
                    $data['status'] ?? FinanceTransactionStatus::Completed->value
                ),
                'amount' => $amount,
                'currency' => $currency,
                'base_amount' => $this->money->multiply($amount, $exchangeRate),
                'base_currency' => $baseCurrency,
                'exchange_rate' => $exchangeRate,
                'source_type' => $data['source_type'],
                'source_id' => $data['source_id'] ?? null,
                'source_public_id' => $data['source_public_id'] ?? null,
                'source_key' => $sourceKey,
                'reference' => $data['reference'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
                'description' => $data['description'] ?? null,
                'recorded_by_type' => $data['recorded_by_type'] ?? session('admin_type'),
                'recorded_by_id' => $data['recorded_by_id'] ?? session('admin_id'),
                'metadata' => $data['metadata'] ?? null,
            ]);

            $this->audit->record(
                'finance',
                'transaction.recorded',
                $transaction,
                after: [
                    'number' => $transaction->number,
                    'direction' => $transaction->direction->value,
                    'type' => $transaction->type->value,
                    'status' => $transaction->status->value,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'account_id' => $account->id,
                ],
                idempotencyKey: 'transaction:'.$sourceKey,
            );

            return $transaction;
        }, 3);
    }
}
