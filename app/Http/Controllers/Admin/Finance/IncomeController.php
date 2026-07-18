<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceCategory;
use App\Finance\Models\FinanceIncome;
use App\Finance\Services\FinanceRecordService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinanceIncomeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeController extends Controller
{
    public function index(Request $request): View
    {
        $incomeRecords = FinanceIncome::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($nested) => $nested
                    ->where('number', 'like', $search)
                    ->orWhere('title', 'like', $search)
                    ->orWhere('payer_name', 'like', $search));
            })
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('currency'),
                fn ($query) => $query->where(
                    'currency',
                    strtoupper($request->string('currency')->toString())
                )
            )
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.finance.income.index', compact('incomeRecords'));
    }

    public function create(): View
    {
        $categories = FinanceCategory::query()
            ->where('kind', 'income')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.finance.income.create', compact('categories'));
    }

    public function store(
        StoreFinanceIncomeRequest $request,
        FinanceRecordService $records,
    ): RedirectResponse {
        $data = $request->validated();
        $data['currency'] = strtoupper($data['currency']);
        $records->createIncome($data);

        return redirect()
            ->route('admin.finance.income.index')
            ->with('success', 'Income record created as a draft.');
    }
}
