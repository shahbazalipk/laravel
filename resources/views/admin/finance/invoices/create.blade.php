@extends('admin.layout')
@section('title', 'New Invoice')
@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-7 flex items-start gap-4"><a href="{{ route('admin.finance.invoices.index') }}" class="mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm" aria-label="Back to invoices">←</a><div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Receivables</p><h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Create invoice</h1><p class="mt-2 text-sm text-slate-500">Prepare an itemized customer invoice.</p></div></div>
    @if($errors->any())<div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert"><p class="font-bold">{{ $errors->first() }}</p></div>@endif
    <form method="POST" action="{{ route('admin.finance.invoices.store') }}" class="space-y-6" data-testid="finance-invoice-form">
        @csrf
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="border-b border-slate-100 pb-4 font-bold text-slate-900">Customer and terms</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2"><label for="invoice-customer" class="mb-1.5 block text-sm font-semibold text-slate-700">Customer</label><input id="invoice-customer" name="customer_name" value="{{ old('customer_name') }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm"></div>
                <div class="lg:col-span-2"><label for="invoice-email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label><input id="invoice-email" type="email" name="customer_email" value="{{ old('customer_email') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold text-slate-700">Invoice date</label><input type="date" name="invoice_date" value="{{ old('invoice_date', today()->toDateString()) }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold text-slate-700">Due date</label><input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold text-slate-700">Currency</label><input name="currency" value="{{ old('currency', current_event_currency()) }}" required maxlength="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase"></div>
                <div><label class="mb-1.5 block text-sm font-semibold text-slate-700">Status</label><select name="status" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm"><option value="draft">Draft</option><option value="issued">Issued</option></select></div>
                <div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-semibold text-slate-700">Billing address</label><textarea name="billing_address" rows="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">{{ old('billing_address') }}</textarea></div>
                <div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-semibold text-slate-700">Payment terms</label><textarea name="payment_terms" rows="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">{{ old('payment_terms') }}</textarea></div>
            </div>
        </section>
        @include('admin.finance.documents._line-items')
        <div class="flex justify-end gap-3"><a href="{{ route('admin.finance.invoices.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700">Cancel</a><button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white">Create invoice</button></div>
    </form>
</div>
@endsection
