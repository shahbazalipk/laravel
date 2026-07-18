<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceBudget extends FinanceModel
{
    use SoftDeletes;

    protected $table = 'finance_budgets';

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'planned_income' => 'decimal:4',
            'planned_expense' => 'decimal:4',
            'warning_threshold' => 'integer',
            'critical_threshold' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(FinanceBudgetAlert::class, 'budget_id');
    }
}
