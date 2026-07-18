<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceReconciliation extends FinanceModel
{
    use SoftDeletes;

    protected $table = 'finance_reconciliations';

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'statement_opening_balance' => 'decimal:4',
            'statement_closing_balance' => 'decimal:4',
            'calculated_closing_balance' => 'decimal:4',
            'difference_amount' => 'decimal:4',
            'completed_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(FinanceReconciliationMatch::class, 'reconciliation_id');
    }
}
