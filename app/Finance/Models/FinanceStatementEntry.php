<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceStatementEntry extends FinanceModel
{
    protected $table = 'finance_statement_entries';

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'value_date' => 'date',
            'amount' => 'decimal:4',
            'balance' => 'decimal:4',
            'raw_data' => 'array',
        ];
    }

    public function statementImport(): BelongsTo
    {
        return $this->belongsTo(FinanceStatementImport::class, 'statement_import_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(FinanceReconciliationMatch::class, 'statement_entry_id');
    }
}
