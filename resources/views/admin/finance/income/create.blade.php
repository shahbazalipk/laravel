@extends('admin.layout')

@section('title', 'New Income')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-7 flex items-start gap-4">
        <a href="{{ route('admin.finance.income.index') }}" class="mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50" aria-label="Back to income">←</a>
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Receivables</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">New income record</h1>
            <p class="mt-2 text-sm text-slate-500">Capture expected revenue. The record starts as a draft for review.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
            <p class="font-bold">Please correct the errors below.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.finance.income.store') }}" class="space-y-6" data-testid="finance-income-form">
        @csrf
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="font-bold text-slate-900">Income details</h2>
                <p class="mt-1 text-sm text-slate-500">Identify the revenue source and expected payer.</p>
            </div>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="income-title" class="mb-1.5 block text-sm font-semibold text-slate-700">Title <span class="text-rose-500">*</span></label>
                    <input id="income-title" name="title" value="{{ old('title') }}" required
                           class="w-full rounded-xl border px-3.5 py-2.5 text-sm shadow-sm outline-none focus:ring-4 {{ $errors->has('title') ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('title')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="income-category" class="mb-1.5 block text-sm font-semibold text-slate-700">Category</label>
                    <select id="income-category" name="category_id" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="income-payer" class="mb-1.5 block text-sm font-semibold text-slate-700">Payer</label>
                    <input id="income-payer" name="payer_name" value="{{ old('payer_name') }}" placeholder="Company or person"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="income-email" class="mb-1.5 block text-sm font-semibold text-slate-700">Payer email</label>
                    <input id="income-email" type="email" name="payer_email" value="{{ old('payer_email') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="income-due" class="mb-1.5 block text-sm font-semibold text-slate-700">Due date</label>
                    <input id="income-due" type="date" name="due_date" value="{{ old('due_date') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="font-bold text-slate-900">Amounts</h2>
                <p class="mt-1 text-sm text-slate-500">Use the original transaction currency. Conversion is captured separately later.</p>
            </div>
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label for="income-amount" class="mb-1.5 block text-sm font-semibold text-slate-700">Expected amount <span class="text-rose-500">*</span></label>
                    <input id="income-amount" type="number" step="0.0001" min="0.0001" name="expected_amount" value="{{ old('expected_amount') }}" required
                           class="w-full rounded-xl border px-3.5 py-2.5 text-sm shadow-sm outline-none focus:ring-4 {{ $errors->has('expected_amount') ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('expected_amount')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="income-currency" class="mb-1.5 block text-sm font-semibold text-slate-700">Currency</label>
                    <input id="income-currency" name="currency" value="{{ old('currency', current_event_currency()) }}" maxlength="3" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="income-received" class="mb-1.5 block text-sm font-semibold text-slate-700">Already received</label>
                    <input id="income-received" type="number" step="0.0001" min="0" name="received_amount" value="{{ old('received_amount', '0.00') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="income-tax" class="mb-1.5 block text-sm font-semibold text-slate-700">Tax amount</label>
                    <input id="income-tax" type="number" step="0.0001" min="0" name="tax_amount" value="{{ old('tax_amount', '0.00') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="income-discount" class="mb-1.5 block text-sm font-semibold text-slate-700">Discount</label>
                    <input id="income-discount" type="number" step="0.0001" min="0" name="discount_amount" value="{{ old('discount_amount', '0.00') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <label for="income-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Description</label>
            <textarea id="income-description" name="description" rows="4" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('description') }}</textarea>
            <label for="income-notes" class="mb-1.5 mt-5 block text-sm font-semibold text-slate-700">Internal notes</label>
            <textarea id="income-notes" name="internal_notes" rows="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('internal_notes') }}</textarea>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.finance.income.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</a>
            <button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">Save draft</button>
        </div>
    </form>
</div>
@endsection
