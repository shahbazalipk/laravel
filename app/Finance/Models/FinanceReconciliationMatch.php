<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceReconciliationMatch extends FinanceModel
{
    protected $table = 'finance_reconciliation_matches';

    protected function casts(): array
    {
        return [
            'confidence_score' => 'integer',
            'match_reasons' => 'array',
            'confirmed_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function statementEntry(): BelongsTo
    {
        return $this->belongsTo(FinanceStatementEntry::class, 'statement_entry_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'transaction_id');
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(FinanceReconciliation::class, 'reconciliation_id');
    }
}
