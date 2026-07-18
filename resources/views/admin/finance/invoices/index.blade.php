@extends('admin.layout')

@section('title', 'Invoices')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Receivables</p><h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Invoices</h1><p class="mt-2 text-sm text-slate-500">Issue customer invoices and track outstanding balances.</p></div>
    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::MANAGE_INCOME))
        <a href="{{ route('admin.finance.invoices.create') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-center text-sm font-bold text-white shadow-sm hover:bg-indigo-700">New invoice</a>
    @endif
</div>
@include('admin.finance._nav')
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-testid="finance-invoices-list">
    <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50"><tr>@foreach(['Invoice', 'Customer', 'Due', 'Total', 'Paid', 'Outstanding', 'Status'] as $heading)<th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $heading }}</th>@endforeach</tr></thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($invoices as $invoice)
                <tr><td class="px-5 py-4 font-semibold text-slate-900">{{ $invoice->number }}</td><td class="px-5 py-4 text-sm text-slate-600">{{ $invoice->customer_name }}</td><td class="px-5 py-4 text-sm text-slate-600">{{ $invoice->due_date?->format('M j, Y') ?: '—' }}</td><td class="px-5 py-4 text-sm font-bold text-slate-900">{{ format_money($invoice->total_amount, 2, $invoice->currency) }}</td><td class="px-5 py-4 text-sm text-emerald-600">{{ format_money($invoice->paid_amount, 2, $invoice->currency) }}</td><td class="px-5 py-4 text-sm font-bold text-amber-700">{{ format_money(bcsub($invoice->total_amount, $invoice->paid_amount, 4), 2, $invoice->currency) }}</td><td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-600">{{ str_replace('_', ' ', $invoice->status) }}</span></td></tr>
            @empty
                <tr><td colspan="7" class="px-6 py-14 text-center text-sm text-slate-500">No invoices created.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</section>
<div class="mt-6">{{ $invoices->links() }}</div>
@endsection
