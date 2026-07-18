<?php

namespace App\Finance\Models;

use App\Finance\Enums\FinanceRecordStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceIncome extends FinanceModel
{
    use SoftDeletes;

    protected $table = 'finance_income_records';

    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:4',
            'received_amount' => 'decimal:4',
            'exchange_rate' => 'decimal:8',
            'tax_amount' => 'decimal:4',
            'discount_amount' => 'decimal:4',
            'due_date' => 'date',
            'status' => FinanceRecordStatus::class,
            'approved_at' => 'immutable_datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }
}
