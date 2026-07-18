<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceInvoice;
use App\Finance\Services\FinancialDocumentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinanceInvoiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        $invoices = FinanceInvoice::query()->with('items')->latest('invoice_date')->paginate(25);

        return view('admin.finance.invoices.index', compact('invoices'));
    }

    public function create(): View
    {
        return view('admin.finance.invoices.create');
    }

    public function store(
        StoreFinanceInvoiceRequest $request,
        FinancialDocumentService $documents,
    ): RedirectResponse {
        $documents->createInvoice($request->validated());

        return redirect()->route('admin.finance.invoices.index')->with('success', 'Invoice created.');
    }
}
