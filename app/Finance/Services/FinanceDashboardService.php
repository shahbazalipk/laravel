<?php

namespace App\Finance\Services;

use App\Finance\Enums\FinanceRecordStatus;
use App\Finance\Enums\FinanceTransactionDirection;
use App\Finance\Enums\FinanceTransactionStatus;
use App\Finance\Enums\FinanceTransactionType;
use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceExpense;
use App\Finance\Models\FinanceIncome;
use App\Finance\Models\FinanceTransaction;

class FinanceDashboardService
{
    /** @return array<string, string|int> */
    public function summary(?string $currency = null): array
    {
        $currency = strtoupper(
            $currency
                ?? FinanceAccount::query()->where('is_default', true)->value('currency')
                ?? 'USD'
        );
        $expectedIncome = $this->sum(
            FinanceIncome::query()
                ->where('currency', $currency)
                ->whereNotIn('status', [
                    FinanceRecordStatus::Draft->value,
                    FinanceRecordStatus::Cancelled->value,
                    FinanceRecordStatus::Rejected->value,
                ]),
            'expected_amount',
        );
        $receivedIncome = $this->transactionSum(FinanceTransactionDirection::Incoming, $currency);
        $approvedExpenses = $this->sum(
            FinanceExpense::query()
                ->where('currency', $currency)
                ->whereIn('status', [
                    FinanceRecordStatus::Approved->value,
                    FinanceRecordStatus::ScheduledForPayment->value,
                    FinanceRecordStatus::PartiallyPaid->value,
                    FinanceRecordStatus::Paid->value,
                    FinanceRecordStatus::Reconciled->value,
                ]),
            'approved_amount',
        );
        $paidExpenses = $this->transactionSum(FinanceTransactionDirection::Outgoing, $currency);
        $refunds = $this->sum(
            FinanceTransaction::query()
                ->where('status', FinanceTransactionStatus::Completed->value)
                ->where('type', FinanceTransactionType::Refund->value)
                ->where('currency', $currency),
            'amount',
        );
        $openingBalances = $this->sum(
            FinanceAccount::query()
                ->where('is_active', true)
                ->where('currency', $currency),
            'opening_balance',
        );
        $currentBalance = bcsub(bcadd($openingBalances, $receivedIncome, 4), $paidExpenses, 4);
        $outstandingExpenses = $this->nonNegativeSubtract($approvedExpenses, $paidExpenses);

        return [
            'currency' => $currency,
            'total_expected_income' => $expectedIncome,
            'total_received_income' => $receivedIncome,
            'total_pending_income' => $this->nonNegativeSubtract($expectedIncome, $receivedIncome),
            'total_approved_expenses' => $approvedExpenses,
            'total_paid_expenses' => $paidExpenses,
            'total_outstanding_expenses' => $outstandingExpenses,
            'total_refunds' => $refunds,
            'current_balance' => $currentBalance,
            'available_balance' => $this->nonNegativeSubtract(
                $currentBalance,
                $outstandingExpenses,
            ),
            'net_profit_or_loss' => bcsub($receivedIncome, $paidExpenses, 4),
            'unreconciled_transactions' => FinanceTransaction::query()
                ->where('status', FinanceTransactionStatus::Completed->value)
                ->where('currency', $currency)
                ->count(),
        ];
    }

    private function transactionSum(FinanceTransactionDirection $direction, string $currency): string
    {
        return $this->sum(
            FinanceTransaction::query()
                ->where('status', FinanceTransactionStatus::Completed->value)
                ->where('direction', $direction->value)
                ->where('currency', $currency),
            'amount',
        );
    }

    private function sum($query, string $column): string
    {
        return bcadd((string) $query->sum($column), '0', 4);
    }

    private function nonNegativeSubtract(string $left, string $right): string
    {
        $difference = bcsub($left, $right, 4);

        return bccomp($difference, '0', 4) === -1 ? '0.0000' : $difference;
    }
}
