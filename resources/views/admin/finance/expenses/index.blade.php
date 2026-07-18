@extends('admin.layout')

@section('title', 'Expenses')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Payables</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Expenses</h1>
        <p class="mt-2 text-sm text-slate-500">Operational costs, claims and vendor obligations awaiting review or payment.</p>
    </div>
    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::MANAGE_EXPENSES))
        <a href="{{ route('admin.finance.expenses.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700" data-testid="create-expense">
            + New expense
        </a>
    @endif
</div>

@include('admin.finance._nav')

<form method="GET" class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_180px_200px_auto]">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search number or title..." aria-label="Search expenses"
           class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
    <select name="status" aria-label="Expense status" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
        <option value="">All statuses</option>
        @foreach(['draft', 'pending_approval', 'approved', 'scheduled_for_payment', 'partially_paid', 'paid', 'overdue', 'rejected', 'cancelled', 'reconciled'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
        @endforeach
    </select>
    <select name="vendor_id" aria-label="Vendor" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
        <option value="">All vendors</option>
        @foreach($vendors as $vendor)
            <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
        @endforeach
    </select>
    <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800">Filter</button>
</form>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-testid="finance-expenses-list">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Expense</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Vendor</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Requested</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Paid</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($expenses as $expense)
                    <tr>
                        <td class="px-5 py-4 sm:px-6">
                            <p class="font-semibold text-slate-900">{{ $expense->title }}</p>
                            <p class="mt-1 font-mono text-xs text-slate-500">{{ $expense->number }} · {{ $expense->category?->name ?: 'Uncategorized' }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $expense->vendor?->name ?: '—' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $expense->expense_date->format('M j, Y') }}</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-900">{{ format_money($expense->expected_amount, 2, $expense->currency) }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-rose-600">{{ format_money($expense->paid_amount, 2, $expense->currency) }}</td>
                        <td class="px-5 py-4 sm:px-6">
                            @php
                                $statusClass = match($expense->status->value) {
                                    'approved' => 'bg-blue-50 text-blue-700',
                                    'paid', 'reconciled' => 'bg-emerald-50 text-emerald-700',
                                    'overdue' => 'bg-orange-50 text-orange-700',
                                    'rejected' => 'bg-rose-50 text-rose-700',
                                    'cancelled' => 'bg-slate-100 text-slate-600',
                                    default => 'bg-amber-50 text-amber-700',
                                };
                            @endphp
                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $expense->status->value)) }}</span>
                            @if($expense->status->value === 'draft' && app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::MANAGE_EXPENSES))
                                <form method="POST" action="{{ route('admin.finance.approvals.expenses.submit', $expense) }}" class="mt-2">
                                    @csrf
                                    <button class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Submit for approval</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-500">No expense records match the current filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">{{ $expenses->links() }}</div>
@endsection
