@extends('online.wizard.layout')

@section('title', 'Confirmation')

@section('content')
    @php
        $appliedPromo = ! empty($pricing['promo_code']) ? $pricing['promo_code'] : null;
    @endphp

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Confirm & pay</h2>
        <p class="mt-1 text-sm text-slate-500">Review your details before submitting. Paid categories will show pending payment instructions.</p>
    </div>

    <div class="mb-6 space-y-4" data-testid="wizard-review">
        <div class="rounded-2xl border border-slate-200 p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Contact</h3>
                <a href="{{ $wizardStepRoute('email') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
            </div>
            <p class="text-sm text-slate-900">{{ $draft->email }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Information</h3>
                <a href="{{ $wizardStepRoute('information') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
            </div>
            <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Name</dt>
                    <dd class="font-medium text-slate-900">{{ ($payload['first_name'] ?? '').' '.($payload['last_name'] ?? '') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Phone</dt>
                    <dd class="font-medium text-slate-900">{{ $payload['phone'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Company</dt>
                    <dd class="font-medium text-slate-900">{{ $payload['company_name'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Industry</dt>
                    <dd class="font-medium text-slate-900">{{ $industry?->name ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Category & total</h3>
                <a href="{{ $wizardStepRoute('category') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="font-semibold text-slate-900">{{ $category?->name ?? '—' }}</p>
                    @if($pricing)
                        <p class="mt-1 text-xs text-slate-500">
                            Base {{ number_format($pricing['base_price'], 2) }}
                            @if(($pricing['discount_amount'] ?? 0) > 0)
                                · Discount {{ number_format($pricing['discount_amount'], 2) }}
                            @endif
                            @if($pricing['tax_amount'] > 0)
                                + VAT {{ number_format($pricing['tax_amount'], 2) }}
                            @endif
                        </p>
                    @endif
                </div>
                @if($pricing)
                    <p class="text-2xl font-bold text-indigo-700" data-testid="wizard-total">
                        {{ number_format($pricing['total_amount'], 2) }} {{ $pricing['currency'] }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-5">
        @include('online.partials.promo-code-field', [
            'testidPrefix' => 'wizard',
            'appliedPromoCode' => $appliedPromo,
            'appliedDiscount' => $pricing['discount_amount'] ?? 0,
            'currency' => $pricing['currency'] ?? ($event->currency ?? ''),
            'applyUrl' => $wizardStepRoute('confirmation', 'promo.apply'),
            'removeUrl' => $wizardStepRoute('confirmation', 'promo.remove'),
            'inputValue' => old('promo_code', $payload['promo_code'] ?? ''),
            'useHiddenWhenApplied' => false,
        ])
    </div>

    <form method="POST"
          action="{{ $wizardStepRoute('confirmation', 'store') }}"
          class="space-y-5"
          data-testid="wizard-confirmation-form">
        @csrf

        @if($appliedPromo)
            <input type="hidden" name="promo_code" value="{{ $appliedPromo }}">
        @endif

        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
            <input type="checkbox"
                   name="terms_accepted"
                   value="1"
                   required
                   class="mt-1"
                   data-testid="wizard-terms">
            <span>I accept the terms and conditions *</span>
        </label>

        @if($pricing && (float) $pricing['total_amount'] > 0)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                After you submit, your registration will remain pending until payment is confirmed by the event team.
            </div>
        @endif

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
            <a href="{{ $wizardStepRoute('information') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                    data-testid="wizard-complete">
                {{ $pricing && (float) $pricing['total_amount'] > 0 ? 'Submit registration' : 'Complete registration' }}
            </button>
        </div>
    </form>
@endsection
