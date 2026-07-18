<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Enums\FinanceRecordStatus;
use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceExpense;
use App\Finance\Models\FinanceIncome;
use App\Finance\Models\FinanceTransaction;
use App\Finance\Services\AccountBalanceService;
use App\Finance\Services\FinanceDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        FinanceDashboardService $dashboard,
        AccountBalanceService $balances,
    ): View {
        $availableCurrencies = FinanceAccount::query()
            ->where('is_active', true)
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency');
        $currency = strtoupper(
            $request->string('currency')->toString()
                ?: ($availableCurrencies->first() ?? current_event_currency())
        );
        $summary = $dashboard->summary($currency);
        $accounts = FinanceAccount::query()
            ->where('is_active', true)
            ->where('currency', $currency)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(function (FinanceAccount $account) use ($balances): FinanceAccount {
                $account->setAttribute('calculated_balance', $balances->calculate($account));

                return $account;
            });
        $recentTransactions = FinanceTransaction::query()
            ->with('account')
            ->where('currency', $currency)
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();
        $pendingExpenses = FinanceExpense::query()
            ->whereIn('status', [
                FinanceRecordStatus::PendingApproval->value,
                FinanceRecordStatus::Approved->value,
            ])
            ->orderBy('due_date')
            ->limit(5)
            ->get();
        $overdueIncome = FinanceIncome::query()
            ->where('currency', $currency)
            ->whereDate('due_date', '<', today())
            ->whereNotIn('status', [
                FinanceRecordStatus::FullyReceived->value,
                FinanceRecordStatus::Cancelled->value,
                FinanceRecordStatus::Refunded->value,
            ])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('admin.finance.dashboard', compact(
            'summary',
            'accounts',
            'recentTransactions',
            'pendingExpenses',
            'overdueIncome',
            'currency',
            'availableCurrencies',
        ));
    }
}
