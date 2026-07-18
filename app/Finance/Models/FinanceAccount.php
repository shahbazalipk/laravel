<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceAccount extends FinanceModel
{
    use SoftDeletes;

    protected $table = 'finance_accounts';

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:4',
            'account_title' => 'encrypted',
            'account_number' => 'encrypted',
            'iban' => 'encrypted',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class, 'account_id');
    }
}
