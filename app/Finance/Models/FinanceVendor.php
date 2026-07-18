<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceVendor extends FinanceModel
{
    use SoftDeletes;

    protected $table = 'finance_vendors';

    protected function casts(): array
    {
        return [
            'tax_number' => 'encrypted',
            'registration_number' => 'encrypted',
            'bank_name' => 'encrypted',
            'account_title' => 'encrypted',
            'account_number' => 'encrypted',
            'iban' => 'encrypted',
            'payment_terms_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(FinanceExpense::class, 'vendor_id');
    }
}
