<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceApprovalRule extends FinanceModel
{
    use SoftDeletes;

    protected $table = 'finance_approval_rules';

    protected function casts(): array
    {
        return [
            'minimum_amount' => 'decimal:4',
            'maximum_amount' => 'decimal:4',
            'required_approvals' => 'integer',
            'approver_admin_ids' => 'array',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
