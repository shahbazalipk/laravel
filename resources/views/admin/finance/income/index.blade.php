@extends('admin.layout')

@section('title', 'Income')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Receivables</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Income</h1>
        <p class="mt-2 text-sm text-slate-500">Expected event revenue from registrations, sponsors, exhibitors and manual sources.</p>
    </div>
    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::MANAGE_INCOME))
        <a href="{{ route('admin.finance.income.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700" data-testid="create-income">
            + New income
        </a>
    @endif
</div>

@include('admin.finance._nav')

<form method="GET" class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_180px_120px_auto]">
    <label class="sr-only" for="income-search">Search income</label>
    <input id="income-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search number, title or payer..."
           class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
    <select name="status" aria-label="Income status" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
        <option value="">All statuses</option>
        @foreach(['draft', 'pending_approval', 'approved', 'partially_received', 'fully_received', 'overdue', 'cancelled', 'refunded'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
        @endforeach
    </select>
    <input name="currency" value="{{ request('currency') }}" maxlength="3" placeholder="Currency" aria-label="Currency"
           class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm uppercase outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
    <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800">Filter</button>
</form>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-testid="finance-income-list">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Income</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Source</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Due</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Expected</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Received</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($incomeRecords as $income)
                    <tr>
                        <td class="px-5 py-4 sm:px-6">
                            <p class="font-semibold text-slate-900">{{ $income->title }}</p>
                            <p class="mt-1 font-mono text-xs text-slate-500">{{ $income->number }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-slate-700">{{ $income->payer_name ?: ucfirst(str_replace('_', ' ', $income->source_type)) }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $income->category?->name ?: 'Uncategorized' }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $income->due_date?->format('M j, Y') ?: '—' }}</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-900">{{ format_money($income->expected_amount, 2, $income->currency) }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-emerald-600">{{ format_money($income->received_amount, 2, $income->currency) }}</td>
                        <td class="px-5 py-4 sm:px-6">
                            @php
                                $statusClass = match($income->status->value) {
                                    'approved' => 'bg-blue-50 text-blue-700',
                                    'fully_received' => 'bg-emerald-50 text-emerald-700',
                                    'overdue' => 'bg-orange-50 text-orange-700',
                                    'cancelled', 'refunded' => 'bg-slate-100 text-slate-600',
                                    default => 'bg-amber-50 text-amber-700',
                                };
                            @endphp
                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $income->status->value)) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-500">No income records match the current filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">{{ $incomeRecords->links() }}</div>
@endsection
