@extends('admin.layout')
@section('title', 'New Vendor Bill')
@section('content')
<div class="mx-auto max-w-5xl">
<div class="mb-7 flex items-start gap-4"><a href="{{ route('admin.finance.bills.index') }}" class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white" aria-label="Back to bills">←</a><div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Payables</p><h1 class="mt-1 text-3xl font-bold text-slate-950">Create vendor bill</h1><p class="mt-2 text-sm text-slate-500">Capture an obligation before approval and payment.</p></div></div>
@if($errors->any())<div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert"><p class="font-bold">{{ $errors->first() }}</p></div>@endif
<form method="POST" action="{{ route('admin.finance.bills.store') }}" class="space-y-6" data-testid="finance-bill-form">@csrf
<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7"><h2 class="border-b border-slate-100 pb-4 font-bold">Vendor and terms</h2><div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
<div class="lg:col-span-2"><label class="mb-1.5 block text-sm font-semibold">Vendor</label><select name="vendor_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm"><option value="">Select vendor</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->name }}</option>@endforeach</select></div>
<div class="lg:col-span-2"><label class="mb-1.5 block text-sm font-semibold">Vendor invoice number</label><input name="vendor_invoice_number" value="{{ old('vendor_invoice_number') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm"></div>
<div><label class="mb-1.5 block text-sm font-semibold">Bill date</label><input type="date" name="bill_date" value="{{ old('bill_date', today()->toDateString()) }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm"></div>
<div><label class="mb-1.5 block text-sm font-semibold">Due date</label><input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm"></div>
<div><label class="mb-1.5 block text-sm font-semibold">Currency</label><input name="currency" value="{{ old('currency', current_event_currency()) }}" maxlength="3" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase"></div>
<div><label class="mb-1.5 block text-sm font-semibold">Category</label><select name="category_id" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm"><option value="">No category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
<div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-semibold">Department</label><input name="department" value="{{ old('department') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm"></div>
<div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-semibold">Notes</label><textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">{{ old('notes') }}</textarea></div>
</div></section>
@include('admin.finance.documents._line-items')
<div class="flex justify-end gap-3"><a href="{{ route('admin.finance.bills.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold">Cancel</a><button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white">Create bill</button></div>
</form></div>
@endsection
