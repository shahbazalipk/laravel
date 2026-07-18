<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceExchangeRate;
use App\Finance\Models\FinanceReconciliation;
use App\Finance\Models\FinanceReconciliationMatch;
use App\Finance\Models\FinanceStatementEntry;
use App\Finance\Models\FinanceStatementImport;
use App\Finance\Services\ExchangeRateService;
use App\Finance\Services\FinanceReconciliationService;
use App\Finance\Services\StatementImportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\ImportFinanceStatementRequest;
use App\Http\Requests\Admin\Finance\StoreFinanceAdjustmentRequest;
use App\Http\Requests\Admin\Finance\StoreFinanceExchangeRateRequest;
use App\Http\Requests\Admin\Finance\StoreFinanceReconciliationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReconciliationController extends Controller
{
    public function index(): View
    {
        $accounts = FinanceAccount::query()->where('is_active', true)->orderBy('name')->get();
        $imports = FinanceStatementImport::query()->with('account')->latest()->limit(15)->get();
        $reconciliations = FinanceReconciliation::query()->with('account')->latest('period_end')->get();
        $rates = FinanceExchangeRate::query()->latest('effective_date')->limit(20)->get();

        return view('admin.finance.reconciliation.index', compact(
            'accounts',
            'imports',
            'reconciliations',
            'rates',
        ));
    }

    public function show(FinanceReconciliation $reconciliation): View
    {
        $reconciliation->load(['account', 'matches.statementEntry', 'matches.transaction']);
        $entries = FinanceStatementEntry::query()
            ->where('account_id', $reconciliation->account_id)
            ->whereBetween('transaction_date', [$reconciliation->period_start, $reconciliation->period_end])
            ->with(['matches.transaction'])
            ->orderBy('transaction_date')
            ->paginate(50);

        return view('admin.finance.reconciliation.show', compact('reconciliation', 'entries'));
    }

    public function store(
        StoreFinanceReconciliationRequest $request,
        FinanceReconciliationService $service,
    ): RedirectResponse {
        $reconciliation = $service->create($request->validated());

        return redirect()
            ->route('admin.finance.reconciliation.show', $reconciliation)
            ->with('success', 'Reconciliation started.');
    }

    public function import(
        ImportFinanceStatementRequest $request,
        StatementImportService $imports,
    ): RedirectResponse {
        $account = FinanceAccount::query()->where('is_active', true)->findOrFail($request->integer('account_id'));
        $imports->import($account, $request->file('statement'), $request->validated());

        return back()->with('success', 'Statement imported.');
    }

    public function storeRate(
        StoreFinanceExchangeRateRequest $request,
        ExchangeRateService $rates,
    ): RedirectResponse {
        $rates->set($request->validated());

        return back()->with('success', 'Exchange rate saved.');
    }

    public function suggest(
        FinanceReconciliation $reconciliation,
        FinanceStatementEntry $entry,
        FinanceReconciliationService $service,
    ): RedirectResponse {
        abort_unless((int) $entry->account_id === (int) $reconciliation->account_id, 404);
        $service->suggest($entry);

        return back()->with('success', 'Matching suggestions refreshed.');
    }

    public function confirm(
        FinanceReconciliation $reconciliation,
        FinanceReconciliationMatch $match,
        FinanceReconciliationService $service,
    ): RedirectResponse {
        $service->confirm($reconciliation, $match);

        return back()->with('success', 'Transaction match confirmed.');
    }

    public function adjust(
        StoreFinanceAdjustmentRequest $request,
        FinanceReconciliation $reconciliation,
        FinanceReconciliationService $service,
    ): RedirectResponse {
        $service->addAdjustment($reconciliation, $request->validated());

        return back()->with('success', 'Reconciliation adjustment recorded.');
    }

    public function complete(
        FinanceReconciliation $reconciliation,
        FinanceReconciliationService $service,
    ): RedirectResponse {
        $service->complete($reconciliation);

        return back()->with('success', 'Reconciliation completed.');
    }
}
