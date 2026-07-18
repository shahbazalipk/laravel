<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceAccount;
use App\Finance\Services\AccountBalanceService;
use App\Finance\Services\FinanceAccountService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinanceAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(AccountBalanceService $balances): View
    {
        $accounts = FinanceAccount::query()
            ->orderByDesc('is_default')
            ->orderBy('currency')
            ->orderBy('name')
            ->get()
            ->map(function (FinanceAccount $account) use ($balances): FinanceAccount {
                $account->setAttribute('calculated_balance', $balances->calculate($account));

                return $account;
            });

        return view('admin.finance.accounts.index', compact('accounts'));
    }

    public function store(
        StoreFinanceAccountRequest $request,
        FinanceAccountService $accounts,
    ): RedirectResponse {
        $accounts->create($request->validated());

        return redirect()
            ->route('admin.finance.accounts.index')
            ->with('success', 'Financial account created.');
    }
}
