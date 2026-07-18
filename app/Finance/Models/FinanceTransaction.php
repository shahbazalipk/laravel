<?php

namespace App\Finance\Models;

use App\Finance\Enums\FinanceTransactionDirection;
use App\Finance\Enums\FinanceTransactionStatus;
use App\Finance\Enums\FinanceTransactionType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class FinanceTransaction extends FinanceModel
{
    public $timestamps = false;

    protected $table = 'finance_transactions';

    protected function casts(): array
    {
        return [
            'direction' => FinanceTransactionDirection::class,
            'type' => FinanceTransactionType::class,
            'status' => FinanceTransactionStatus::class,
            'amount' => 'decimal:4',
            'base_amount' => 'decimal:4',
            'exchange_rate' => 'decimal:8',
            'occurred_at' => 'immutable_datetime',
            'metadata' => 'array',
            'reconciled_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        parent::booted();

        static::updating(function (self $transaction): void {
            $mutableReconciliationFields = [
                'reconciliation_id',
                'reconciliation_status',
                'reconciled_by',
                'reconciled_at',
            ];

            if (array_diff(array_keys($transaction->getDirty()), $mutableReconciliationFields) !== []) {
                throw new LogicException('Finance transactions are immutable. Record a reversal instead.');
            }
        });

        static::deleting(function (): never {
            throw new LogicException('Finance transactions cannot be deleted.');
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(FinanceReconciliation::class, 'reconciliation_id');
    }
}
