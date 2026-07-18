@extends('admin.layout')

@section('title', 'Finance Categories')

@section('content')
<div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Finance settings</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Categories</h1>
    <p class="mt-2 text-sm text-slate-500">A consistent classification structure for income, expenses, budgets and reports.</p>
</div>

@include('admin.finance._nav')

@php($canConfigureFinance = app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::CONFIGURE))
<div class="grid gap-6 {{ $canConfigureFinance ? 'xl:grid-cols-[minmax(0,1fr)_380px]' : '' }}">
    <section class="grid content-start gap-5 md:grid-cols-2" data-testid="finance-categories-list">
        @foreach(['income' => 'Income categories', 'expense' => 'Expense categories'] as $kind => $heading)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-900">{{ $heading }}</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($categories->where('kind', $kind) as $category)
                        <div class="flex items-center justify-between gap-4 px-5 py-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="h-3 w-3 flex-none rounded-full" style="background-color: {{ $category->color ?: ($kind === 'income' ? '#10b981' : '#f43f5e') }}"></span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $category->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $category->parent?->name ?: $category->code }}</p>
                                </div>
                            </div>
                            <span class="h-2 w-2 flex-none rounded-full {{ $category->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-slate-500">No {{ $kind }} categories.</p>
                    @endforelse
                </div>
            </article>
        @endforeach
    </section>

    @if($canConfigureFinance)
    <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Add category</h2>
        <p class="mt-1 text-sm text-slate-500">Codes remain stable for reports and imports.</p>
        @if($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-700" role="alert">Please correct the form errors.</div>
        @endif
        <form method="POST" action="{{ route('admin.finance.categories.store') }}" class="mt-6 space-y-5" data-testid="finance-category-form">
            @csrf
            <div>
                <label for="category-kind" class="mb-1.5 block text-sm font-semibold text-slate-700">Category type</label>
                <select id="category-kind" name="kind" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                    <option value="income" @selected(old('kind') === 'income')>Income</option>
                    <option value="expense" @selected(old('kind') === 'expense')>Expense</option>
                </select>
            </div>
            <div>
                <label for="category-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Name <span class="text-rose-500">*</span></label>
                <input id="category-name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="category-code" class="mb-1.5 block text-sm font-semibold text-slate-700">Code <span class="text-rose-500">*</span></label>
                <input id="category-code" name="code" value="{{ old('code') }}" required placeholder="venue_rental" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 font-mono text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                @error('code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="category-parent" class="mb-1.5 block text-sm font-semibold text-slate-700">Parent category</label>
                <select id="category-parent" name="parent_id" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                    <option value="">No parent</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" data-kind="{{ $category->kind }}" @selected(old('parent_id') == $category->id)>{{ ucfirst($category->kind) }} · {{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="category-color" class="mb-1.5 block text-sm font-semibold text-slate-700">Color</label>
                <input id="category-color" type="color" name="color" value="{{ old('color', '#4f46e5') }}" class="h-11 w-full rounded-xl border border-slate-300 bg-white p-1.5">
            </div>
            <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">Create category</button>
        </form>
    </aside>
    @endif
</div>
@endsection
