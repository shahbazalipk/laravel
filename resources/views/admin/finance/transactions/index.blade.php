@extends('admin.layout')

@section('title', 'Finance Transactions')

@section('content')
<div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Cash ledger</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Transactions</h1>
    <p class="mt-2 text-sm text-slate-500">Append-only cash movements from registrations, income, expenses, refunds and adjustments.</p>
</div>

@include('admin.finance._nav')

<form method="GET" class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_220px_160px_auto]">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search number or reference..." aria-label="Search transactions"
           class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
    <select name="account_id" aria-label="Account" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
        <option value="">All accounts</option>
        @foreach($accounts as $account)
            <option value="{{ $account->id }}" @selected(request('account_id') == $account->id)>{{ $account->name }} · {{ $account->currency }}</option>
        @endforeach
    </select>
    <select name="direction" aria-label="Direction" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
        <option value="">Both directions</option>
        <option value="incoming" @selected(request('direction') === 'incoming')>Incoming</option>
        <option value="outgoing" @selected(request('direction') === 'outgoing')>Outgoing</option>
    </select>
    <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800">Filter</button>
</form>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-testid="finance-transactions-list">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Transaction</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Account</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Source</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Reference</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-5 py-4 sm:px-6">
                            <p class="font-semibold text-slate-900">{{ $transaction->number }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $transaction->occurred_at->format('M j, Y · g:i A') }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-semibold text-slate-700">{{ $transaction->account?->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $transaction->currency }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm capitalize text-slate-700">{{ str_replace('_', ' ', $transaction->type->value) }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ str_replace('_', ' ', $transaction->source_type) }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $transaction->reference ?: '—' }}</td>
                        <td class="px-5 py-4 text-right sm:px-6">
                            <span class="whitespace-nowrap text-sm font-bold {{ $transaction->direction->value === 'incoming' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $transaction->direction->value === 'incoming' ? '+' : '−' }}{{ format_money($transaction->amount, 2, $transaction->currency) }}
                            </span>
                            <p class="mt-1 text-xs capitalize text-slate-500">{{ $transaction->status->value }}</p>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-16 text-center text-sm text-slate-500">No ledger transactions match the current filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">{{ $transactions->links() }}</div>
@endsection
