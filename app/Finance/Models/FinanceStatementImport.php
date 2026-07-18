<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceStatementImport extends FinanceModel
{
    protected $table = 'finance_statement_imports';

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'opening_balance' => 'decimal:4',
            'closing_balance' => 'decimal:4',
            'errors' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FinanceStatementEntry::class, 'statement_import_id');
    }
}
