<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceBudget;
use App\Finance\Models\FinanceCategory;
use App\Finance\Services\FinanceBudgetService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinanceBudgetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(FinanceBudgetService $service): View
    {
        $budgets = FinanceBudget::query()
            ->with(['category', 'alerts'])
            ->orderByDesc('period_start')
            ->get()
            ->map(function (FinanceBudget $budget) use ($service): FinanceBudget {
                $budget->setAttribute('summary', $service->summary($budget));
                $service->evaluateAlerts($budget);

                return $budget;
            });
        $categories = FinanceCategory::query()
            ->where('kind', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.finance.budgets.index', compact('budgets', 'categories'));
    }

    public function store(
        StoreFinanceBudgetRequest $request,
        FinanceBudgetService $budgets,
    ): RedirectResponse {
        $budgets->create($request->validated());

        return back()->with('success', 'Budget created.');
    }
}
