<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceApprovalRequest;
use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceExpense;
use App\Finance\Services\FinanceApprovalService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\DecideFinanceApprovalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $approvals = FinanceApprovalRequest::query()
            ->with('rule')
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->latest('submitted_at')
            ->paginate(30);

        return view('admin.finance.approvals.index', compact('approvals'));
    }

    public function submitExpense(
        FinanceExpense $expense,
        FinanceApprovalService $approvals,
    ): RedirectResponse {
        $approvals->submit($expense);

        return back()->with('success', 'Expense submitted for approval.');
    }

    public function submitBill(
        FinanceBill $bill,
        FinanceApprovalService $approvals,
    ): RedirectResponse {
        $approvals->submit($bill);

        return back()->with('success', 'Bill submitted for approval.');
    }

    public function decide(
        DecideFinanceApprovalRequest $request,
        FinanceApprovalRequest $approval,
        FinanceApprovalService $approvals,
    ): RedirectResponse {
        $approvals->decide(
            $approval,
            $request->validated('decision'),
            $request->validated('comments'),
        );

        return back()->with('success', 'Approval decision recorded.');
    }
}
