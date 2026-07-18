<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceAdjustment extends FinanceModel
{
    public $timestamps = false;

    protected $table = 'finance_adjustments';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(FinanceReconciliation::class, 'reconciliation_id');
    }
}
