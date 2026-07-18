<?php

namespace App\Finance\Enums;

enum FinanceTransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Voided = 'voided';
}
