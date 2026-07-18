<?php

namespace App\Finance\Models;

class FinanceExchangeRate extends FinanceModel
{
    protected $table = 'finance_exchange_rates';

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'effective_date' => 'date',
        ];
    }
}
