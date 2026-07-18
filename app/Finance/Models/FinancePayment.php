<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancePayment extends FinanceModel
{
    use SoftDeletes;

    protected $table = 'finance_payments';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'allocated_amount' => 'decimal:4',
            'payment_date' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(FinancePaymentAllocation::class, 'payment_id');
    }
}
