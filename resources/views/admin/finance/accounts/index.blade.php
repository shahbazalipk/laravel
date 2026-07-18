@extends('admin.layout')

@section('title', 'Financial Accounts')

@section('content')
<div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Finance settings</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Financial accounts</h1>
    <p class="mt-2 text-sm text-slate-500">Configure bank, cash and payment gateway accounts. Sensitive fields are encrypted.</p>
</div>

@include('admin.finance._nav')

@php($canManageAccounts = app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::MANAGE_ACCOUNTS))
<div class="grid gap-6 {{ $canManageAccounts ? 'xl:grid-cols-[minmax(0,1fr)_380px]' : '' }}">
    <section class="grid content-start gap-4 sm:grid-cols-2" data-testid="finance-accounts-list">
        @forelse($accounts as $account)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-bold text-slate-900">{{ $account->name }}</h2>
                            @if($account->is_default)
                                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-indigo-700">Default</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs font-medium capitalize text-slate-500">{{ str_replace('_', ' ', $account->type) }} · {{ $account->currency }}</p>
                    </div>
                    <span class="h-2.5 w-2.5 rounded-full {{ $account->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $account->is_active ? 'Active' : 'Inactive' }}"></span>
                </div>
                <p class="mt-7 text-2xl font-bold tracking-tight text-slate-950">{{ format_money($account->calculated_balance, 2, $account->currency) }}</p>
                <p class="mt-1 text-xs text-slate-500">Calculated balance</p>
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-xs">
                    <span class="text-slate-500">Opening balance</span>
                    <span class="font-semibold text-slate-700">{{ format_money($account->opening_balance, 2, $account->currency) }}</span>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center sm:col-span-2">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-xl text-indigo-600">₿</div>
                <h2 class="mt-4 font-bold text-slate-900">Create your first account</h2>
                <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">Accounts receive payments, fund expenses and provide the basis for reconciliation.</p>
            </div>
        @endforelse
    </section>

    @if($canManageAccounts)
    <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Add account</h2>
        <p class="mt-1 text-sm text-slate-500">Use the account currency for every linked transaction.</p>

        @if($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
                <p class="font-semibold">Please correct the highlighted fields.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.finance.accounts.store') }}" class="mt-6 space-y-5" data-testid="finance-account-form">
            @csrf
            <div>
                <label for="account-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Account name <span class="text-rose-500">*</span></label>
                <input id="account-name" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-xl border px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:ring-4 {{ $errors->has('name') ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                @error('name')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="account-type" class="mb-1.5 block text-sm font-semibold text-slate-700">Type</label>
                    <select id="account-type" name="type" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        @foreach(['bank' => 'Bank', 'cash' => 'Cash', 'petty_cash' => 'Petty cash', 'payment_gateway' => 'Gateway', 'credit_card' => 'Credit card', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="account-currency" class="mb-1.5 block text-sm font-semibold text-slate-700">Currency</label>
                    <input id="account-currency" name="currency" value="{{ old('currency', current_event_currency()) }}" maxlength="3" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
            </div>
            <div>
                <label for="opening-balance" class="mb-1.5 block text-sm font-semibold text-slate-700">Opening balance</label>
                <input id="opening-balance" type="number" step="0.0001" name="opening_balance" value="{{ old('opening_balance', '0.00') }}"
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="bank-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Bank name</label>
                    <input id="bank-name" name="bank_name" value="{{ old('bank_name') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="account-title" class="mb-1.5 block text-sm font-semibold text-slate-700">Account title</label>
                    <input id="account-title" name="account_title" value="{{ old('account_title') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
            </div>
            <div>
                <label for="account-number" class="mb-1.5 block text-sm font-semibold text-slate-700">Account number</label>
                <input id="account-number" name="account_number" value="{{ old('account_number') }}" autocomplete="off"
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            </div>
            <div>
                <label for="account-iban" class="mb-1.5 block text-sm font-semibold text-slate-700">IBAN</label>
                <input id="account-iban" name="iban" value="{{ old('iban') }}" autocomplete="off"
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            </div>
            <label class="flex items-start gap-3 rounded-xl bg-slate-50 p-3">
                <input type="hidden" name="is_default" value="0">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default')) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span>
                    <span class="block text-sm font-semibold text-slate-700">Default for this currency</span>
                    <span class="mt-0.5 block text-xs text-slate-500">Automatic registration payments use the default account.</span>
                </span>
            </label>
            <input type="hidden" name="is_active" value="1">
            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">
                Create account
            </button>
        </form>
    </aside>
    @endif
</div>
@endsection
