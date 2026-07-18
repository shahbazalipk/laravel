<?php

namespace App\Finance\Models;

use App\Finance\Enums\FinanceRecordStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceExpense extends FinanceModel
{
    use SoftDeletes;

    protected $table = 'finance_expense_records';

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'due_date' => 'date',
            'expected_amount' => 'decimal:4',
            'approved_amount' => 'decimal:4',
            'paid_amount' => 'decimal:4',
            'tax_amount' => 'decimal:4',
            'exchange_rate' => 'decimal:8',
            'status' => FinanceRecordStatus::class,
            'submitted_at' => 'immutable_datetime',
            'approved_at' => 'immutable_datetime',
            'paid_at' => 'immutable_datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(FinanceVendor::class, 'vendor_id');
    }
}
