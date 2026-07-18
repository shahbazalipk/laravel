<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceApprovalRequest extends FinanceModel
{
    protected $table = 'finance_approval_requests';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'required_approvals' => 'integer',
            'approval_count' => 'integer',
            'submitted_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(FinanceApprovalRule::class, 'rule_id');
    }
}
