@extends('admin.layout')

@section('title', 'Finance Vendors')

@section('content')
<div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Directory</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Vendors</h1>
    <p class="mt-2 text-sm text-slate-500">Central payment and contact records for event suppliers.</p>
</div>

@include('admin.finance._nav')

@php($canConfigureFinance = app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::CONFIGURE))
<div class="grid gap-6 {{ $canConfigureFinance ? 'xl:grid-cols-[minmax(0,1fr)_400px]' : '' }}">
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-testid="finance-vendors-list">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Vendor</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Contact</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Terms</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Expenses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vendors as $vendor)
                        <tr>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="font-semibold text-slate-900">{{ $vendor->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $vendor->type ?: 'General supplier' }}{{ $vendor->city ? ' · '.$vendor->city : '' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                <p>{{ $vendor->contact_name ?: '—' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $vendor->email ?: $vendor->phone }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $vendor->payment_terms_days !== null ? $vendor->payment_terms_days.' days' : '—' }}
                                @if($vendor->default_currency)<span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold">{{ $vendor->default_currency }}</span>@endif
                            </td>
                            <td class="px-5 py-4 text-right text-sm font-bold text-slate-900 sm:px-6">{{ $vendor->expenses_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-16 text-center text-sm text-slate-500">No vendors yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $vendors->links() }}</div>
    </section>

    @if($canConfigureFinance)
    <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Add vendor</h2>
        <p class="mt-1 text-sm text-slate-500">Bank and tax identifiers are encrypted at rest.</p>
        @if($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-700" role="alert">Please correct the form errors.</div>
        @endif
        <form method="POST" action="{{ route('admin.finance.vendors.store') }}" class="mt-6 space-y-4" data-testid="finance-vendor-form">
            @csrf
            <div>
                <label for="vendor-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Vendor name <span class="text-rose-500">*</span></label>
                <input id="vendor-name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="vendor-type" class="mb-1.5 block text-sm font-semibold text-slate-700">Type</label>
                    <input id="vendor-type" name="type" value="{{ old('type') }}" placeholder="Venue, catering..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="vendor-contact" class="mb-1.5 block text-sm font-semibold text-slate-700">Contact</label>
                    <input id="vendor-contact" name="contact_name" value="{{ old('contact_name') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="vendor-email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                    <input id="vendor-email" type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="vendor-phone" class="mb-1.5 block text-sm font-semibold text-slate-700">Phone</label>
                    <input id="vendor-phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="vendor-currency" class="mb-1.5 block text-sm font-semibold text-slate-700">Currency</label>
                    <input id="vendor-currency" name="default_currency" maxlength="3" value="{{ old('default_currency', current_event_currency()) }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="vendor-terms" class="mb-1.5 block text-sm font-semibold text-slate-700">Terms (days)</label>
                    <input id="vendor-terms" type="number" min="0" name="payment_terms_days" value="{{ old('payment_terms_days', 30) }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
            </div>
            <details class="rounded-xl border border-slate-200 p-4">
                <summary class="cursor-pointer text-sm font-semibold text-slate-700">Protected financial details</summary>
                <div class="mt-4 space-y-4">
                    <input name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank name" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                    <input name="account_title" value="{{ old('account_title') }}" placeholder="Account title" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                    <input name="account_number" value="{{ old('account_number') }}" placeholder="Account number" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                    <input name="iban" value="{{ old('iban') }}" placeholder="IBAN" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase">
                </div>
            </details>
            <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">Create vendor</button>
        </form>
    </aside>
    @endif
</div>
@endsection
