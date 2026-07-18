@extends('admin.layout')

@section('title', 'New Expense')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-7 flex items-start gap-4">
        <a href="{{ route('admin.finance.expenses.index') }}" class="mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50" aria-label="Back to expenses">←</a>
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Payables</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">New expense request</h1>
            <p class="mt-2 text-sm text-slate-500">Capture an operational cost. Approval and payment are separate controlled steps.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
            <p class="font-bold">Please correct the errors below.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.finance.expenses.store') }}" class="space-y-6" data-testid="finance-expense-form">
        @csrf
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="font-bold text-slate-900">Expense details</h2>
                <p class="mt-1 text-sm text-slate-500">Describe what is being purchased and who will be paid.</p>
            </div>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="expense-title" class="mb-1.5 block text-sm font-semibold text-slate-700">Title <span class="text-rose-500">*</span></label>
                    <input id="expense-title" name="title" value="{{ old('title') }}" required
                           class="w-full rounded-xl border px-3.5 py-2.5 text-sm shadow-sm outline-none focus:ring-4 {{ $errors->has('title') ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('title')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="expense-category" class="mb-1.5 block text-sm font-semibold text-slate-700">Category</label>
                    <select id="expense-category" name="category_id" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="expense-vendor" class="mb-1.5 block text-sm font-semibold text-slate-700">Vendor</label>
                    <select id="expense-vendor" name="vendor_id" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        <option value="">No vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="expense-date" class="mb-1.5 block text-sm font-semibold text-slate-700">Expense date <span class="text-rose-500">*</span></label>
                    <input id="expense-date" type="date" name="expense_date" value="{{ old('expense_date', today()->toDateString()) }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="expense-due" class="mb-1.5 block text-sm font-semibold text-slate-700">Payment due</label>
                    <input id="expense-due" type="date" name="due_date" value="{{ old('due_date') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="font-bold text-slate-900">Requested amount</h2>
                <p class="mt-1 text-sm text-slate-500">Approval may authorize a different amount in the next workflow stage.</p>
            </div>
            <div class="mt-6 grid gap-5 sm:grid-cols-3">
                <div>
                    <label for="expense-amount" class="mb-1.5 block text-sm font-semibold text-slate-700">Amount <span class="text-rose-500">*</span></label>
                    <input id="expense-amount" type="number" step="0.0001" min="0.0001" name="expected_amount" value="{{ old('expected_amount') }}" required
                           class="w-full rounded-xl border px-3.5 py-2.5 text-sm shadow-sm outline-none focus:ring-4 {{ $errors->has('expected_amount') ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('expected_amount')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="expense-currency" class="mb-1.5 block text-sm font-semibold text-slate-700">Currency</label>
                    <input id="expense-currency" name="currency" value="{{ old('currency', current_event_currency()) }}" maxlength="3" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="expense-tax" class="mb-1.5 block text-sm font-semibold text-slate-700">Tax amount</label>
                    <input id="expense-tax" type="number" step="0.0001" min="0" name="tax_amount" value="{{ old('tax_amount', '0.00') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <label for="expense-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Business purpose</label>
            <textarea id="expense-description" name="description" rows="4" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('description') }}</textarea>
            <label for="expense-notes" class="mb-1.5 mt-5 block text-sm font-semibold text-slate-700">Internal notes</label>
            <textarea id="expense-notes" name="internal_notes" rows="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('internal_notes') }}</textarea>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.finance.expenses.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</a>
            <button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">Save draft</button>
        </div>
    </form>
</div>
@endsection
