<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceInvoice;
use App\Finance\Models\FinanceRefund;
use App\Finance\Services\FinancePaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinanceRefundRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function index(): View
    {
        $refunds = FinanceRefund::query()->with('account')->latest('refunded_at')->paginate(25);
        $invoices = FinanceInvoice::query()->where('paid_amount', '>', 0)->orderByDesc('invoice_date')->get();
        $accounts = FinanceAccount::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.finance.refunds.index', compact('refunds', 'invoices', 'accounts'));
    }

    public function store(
        StoreFinanceRefundRequest $request,
        FinancePaymentService $payments,
    ): RedirectResponse {
        $invoice = FinanceInvoice::query()
            ->where('public_id', $request->validated('invoice_public_id'))
            ->firstOrFail();
        $payments->refund($invoice, $request->validated());

        return back()->with('success', 'Refund completed.');
    }
}
