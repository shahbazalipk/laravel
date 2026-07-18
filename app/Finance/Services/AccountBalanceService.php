<?php

namespace App\Finance\Services;

use App\Finance\Enums\FinanceTransactionDirection;
use App\Finance\Enums\FinanceTransactionStatus;
use App\Finance\Models\FinanceAccount;

class AccountBalanceService
{
    public function calculate(FinanceAccount $account): string
    {
        $incoming = (string) $account->transactions()
            ->where('status', FinanceTransactionStatus::Completed->value)
            ->where('direction', FinanceTransactionDirection::Incoming->value)
            ->sum('amount');

        $outgoing = (string) $account->transactions()
            ->where('status', FinanceTransactionStatus::Completed->value)
            ->where('direction', FinanceTransactionDirection::Outgoing->value)
            ->sum('amount');

        return bcsub(
            bcadd((string) $account->opening_balance, $incoming, 4),
            $outgoing,
            4,
        );
    }
}
