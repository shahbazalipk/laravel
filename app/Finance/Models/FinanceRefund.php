<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceRefund extends FinanceModel
{
    protected $table = 'finance_refunds';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'refunded_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }
}
