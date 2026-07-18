<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceBudgetAlert extends FinanceModel
{
    public $timestamps = false;

    protected $table = 'finance_budget_alerts';

    protected function casts(): array
    {
        return [
            'utilization_percent' => 'integer',
            'actual_amount' => 'decimal:4',
            'budget_amount' => 'decimal:4',
            'triggered_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(FinanceBudget::class, 'budget_id');
    }
}
