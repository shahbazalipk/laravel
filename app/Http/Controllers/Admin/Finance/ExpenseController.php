<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceCategory;
use App\Finance\Models\FinanceExpense;
use App\Finance\Models\FinanceVendor;
use App\Finance\Services\FinanceRecordService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinanceExpenseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $expenses = FinanceExpense::query()
            ->with(['category', 'vendor'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($nested) => $nested
                    ->where('number', 'like', $search)
                    ->orWhere('title', 'like', $search));
            })
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('vendor_id'),
                fn ($query) => $query->where('vendor_id', $request->integer('vendor_id'))
            )
            ->orderByDesc('expense_date')
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();
        $vendors = FinanceVendor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.finance.expenses.index', compact('expenses', 'vendors'));
    }

    public function create(): View
    {
        $categories = FinanceCategory::query()
            ->where('kind', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $vendors = FinanceVendor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.finance.expenses.create', compact('categories', 'vendors'));
    }

    public function store(
        StoreFinanceExpenseRequest $request,
        FinanceRecordService $records,
    ): RedirectResponse {
        $data = $request->validated();
        $data['currency'] = strtoupper($data['currency']);
        $records->createExpense($data);

        return redirect()
            ->route('admin.finance.expenses.index')
            ->with('success', 'Expense request created as a draft.');
    }
}
