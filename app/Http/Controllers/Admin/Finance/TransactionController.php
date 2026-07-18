<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = FinanceTransaction::query()
            ->with('account')
            ->when(
                $request->filled('account_id'),
                fn ($query) => $query->where('account_id', $request->integer('account_id'))
            )
            ->when(
                $request->filled('direction'),
                fn ($query) => $query->where('direction', $request->string('direction')->toString())
            )
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($nested) => $nested
                    ->where('number', 'like', $search)
                    ->orWhere('reference', 'like', $search)
                    ->orWhere('description', 'like', $search));
            })
            ->orderByDesc('occurred_at')
            ->paginate(50)
            ->withQueryString();
        $accounts = FinanceAccount::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'currency']);

        return view('admin.finance.transactions.index', compact('transactions', 'accounts'));
    }
}
