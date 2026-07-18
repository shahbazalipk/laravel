<?php

use App\Finance\Enums\FinanceAbility;
use App\Http\Controllers\Admin\Finance\AccountController;
use App\Http\Controllers\Admin\Finance\ApprovalController;
use App\Http\Controllers\Admin\Finance\BillController;
use App\Http\Controllers\Admin\Finance\BudgetController;
use App\Http\Controllers\Admin\Finance\CategoryController;
use App\Http\Controllers\Admin\Finance\DashboardController;
use App\Http\Controllers\Admin\Finance\ExpenseController;
use App\Http\Controllers\Admin\Finance\FinanceExportController;
use App\Http\Controllers\Admin\Finance\FinanceReportController;
use App\Http\Controllers\Admin\Finance\IncomeController;
use App\Http\Controllers\Admin\Finance\InvoiceController;
use App\Http\Controllers\Admin\Finance\PaymentController;
use App\Http\Controllers\Admin\Finance\ReconciliationController;
use App\Http\Controllers\Admin\Finance\RefundController;
use App\Http\Controllers\Admin\Finance\TransactionController;
use App\Http\Controllers\Admin\Finance\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'event.admin', 'tenant.required', 'module.enabled:finance'])
    ->prefix('admin/finance')
    ->name('admin.finance.')
    ->group(function (): void {
        Route::middleware('module.ability:finance,'.FinanceAbility::VIEW)->group(function (): void {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
            Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
            Route::get('income', [IncomeController::class, 'index'])->name('income.index');
            Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
            Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('bills', [BillController::class, 'index'])->name('bills.index');
            Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('refunds', [RefundController::class, 'index'])->name('refunds.index');
            Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index');
        });

        Route::post('accounts', [AccountController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_ACCOUNTS)
            ->name('accounts.store');
        Route::get('income/create', [IncomeController::class, 'create'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_INCOME)
            ->name('income.create');
        Route::post('income', [IncomeController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_INCOME)
            ->name('income.store');
        Route::get('expenses/create', [ExpenseController::class, 'create'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_EXPENSES)
            ->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_EXPENSES)
            ->name('expenses.store');
        Route::post('vendors', [VendorController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::CONFIGURE)
            ->name('vendors.store');
        Route::post('categories', [CategoryController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::CONFIGURE)
            ->name('categories.store');
        Route::get('invoices/create', [InvoiceController::class, 'create'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_INCOME)
            ->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_INCOME)
            ->name('invoices.store');
        Route::get('bills/create', [BillController::class, 'create'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_EXPENSES)
            ->name('bills.create');
        Route::post('bills', [BillController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_EXPENSES)
            ->name('bills.store');
        Route::post('payments', [PaymentController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::PAY)
            ->name('payments.store');
        Route::post('refunds', [RefundController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::REFUND)
            ->name('refunds.store');
        Route::post('budgets', [BudgetController::class, 'store'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_BUDGETS)
            ->name('budgets.store');
        Route::get('approvals', [ApprovalController::class, 'index'])
            ->middleware('module.ability:finance,'.FinanceAbility::APPROVE)
            ->name('approvals.index');
        Route::post('approvals/expenses/{expense}', [ApprovalController::class, 'submitExpense'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_EXPENSES)
            ->name('approvals.expenses.submit');
        Route::post('approvals/bills/{bill}', [ApprovalController::class, 'submitBill'])
            ->middleware('module.ability:finance,'.FinanceAbility::MANAGE_EXPENSES)
            ->name('approvals.bills.submit');
        Route::post('approvals/{approval}/decide', [ApprovalController::class, 'decide'])
            ->middleware('module.ability:finance,'.FinanceAbility::APPROVE)
            ->name('approvals.decide');
        Route::get('exports', FinanceExportController::class)
            ->middleware('module.ability:finance,'.FinanceAbility::EXPORT)
            ->name('exports.download');
        Route::middleware('module.ability:finance,'.FinanceAbility::RECONCILE)
            ->prefix('reconciliation')
            ->name('reconciliation.')
            ->group(function (): void {
                Route::get('/', [ReconciliationController::class, 'index'])->name('index');
                Route::post('/', [ReconciliationController::class, 'store'])->name('store');
                Route::post('imports', [ReconciliationController::class, 'import'])->name('imports.store');
                Route::post('rates', [ReconciliationController::class, 'storeRate'])->name('rates.store');
                Route::get('{reconciliation}', [ReconciliationController::class, 'show'])->name('show');
                Route::post('{reconciliation}/entries/{entry}/suggest', [ReconciliationController::class, 'suggest'])
                    ->name('matches.suggest');
                Route::post('{reconciliation}/matches/{match}/confirm', [ReconciliationController::class, 'confirm'])
                    ->name('matches.confirm');
                Route::post('{reconciliation}/adjustments', [ReconciliationController::class, 'adjust'])
                    ->name('adjustments.store');
                Route::post('{reconciliation}/complete', [ReconciliationController::class, 'complete'])
                    ->name('complete');
            });
        Route::get('reports', FinanceReportController::class)
            ->middleware('module.ability:finance,'.FinanceAbility::VIEW_REPORTS)
            ->name('reports.index');
    });
