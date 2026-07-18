<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceInvoice;
use App\Finance\Models\FinancePayment;
use App\Finance\Services\FinancePaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinancePaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = FinancePayment::query()->with(['account', 'allocations'])->latest('payment_date')->paginate(25);
        $accounts = FinanceAccount::query()->where('is_active', true)->orderBy('name')->get();
        $invoices = FinanceInvoice::query()
            ->whereNotIn('status', ['paid', 'cancelled', 'refunded'])
            ->orderBy('due_date')
            ->get();
        $bills = FinanceBill::query()
            ->where('approval_status', 'approved')
            ->whereNotIn('payment_status', ['paid', 'cancelled'])
            ->with('vendor')
            ->orderBy('due_date')
            ->get();

        return view('admin.finance.payments.index', compact('payments', 'accounts', 'invoices', 'bills'));
    }

    public function store(
        StoreFinancePaymentRequest $request,
        FinancePaymentService $payments,
    ): RedirectResponse {
        $data = $request->validated();
        $document = $data['document_type'] === 'invoice'
            ? FinanceInvoice::query()->where('public_id', $data['document_public_id'])->firstOrFail()
            : FinanceBill::query()->where('public_id', $data['document_public_id'])->firstOrFail();

        $payments->recordFor($document, $data);

        return back()->with('success', 'Payment recorded and allocated.');
    }
}
