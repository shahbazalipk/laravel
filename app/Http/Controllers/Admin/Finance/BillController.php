<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceCategory;
use App\Finance\Models\FinanceVendor;
use App\Finance\Services\FinancialDocumentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinanceBillRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(): View
    {
        $bills = FinanceBill::query()->with(['vendor', 'items'])->latest('bill_date')->paginate(25);

        return view('admin.finance.bills.index', compact('bills'));
    }

    public function create(): View
    {
        $vendors = FinanceVendor::query()->where('is_active', true)->orderBy('name')->get();
        $categories = FinanceCategory::query()
            ->where('kind', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.finance.bills.create', compact('vendors', 'categories'));
    }

    public function store(
        StoreFinanceBillRequest $request,
        FinancialDocumentService $documents,
    ): RedirectResponse {
        $documents->createBill($request->validated());

        return redirect()->route('admin.finance.bills.index')->with('success', 'Vendor bill created.');
    }
}
