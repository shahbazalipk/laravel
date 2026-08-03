@php
    use App\Payments\Enums\RegistrationPaymentSummaryStatus;
    $summaryStatus = $paymentSummary['summary_status'] instanceof RegistrationPaymentSummaryStatus
        ? $paymentSummary['summary_status']
        : RegistrationPaymentSummaryStatus::tryFrom($registration->payment_status) ?? RegistrationPaymentSummaryStatus::Pending;
    $currency = $paymentSummary['currency'] ?? ($registration->currency ?: 'AED');
    $registrationTotal = (float) $paymentSummary['registration_total'];
    $netPaid = (float) $paymentSummary['net_paid'];
    $balanceDue = (float) $paymentSummary['balance_due'];
    $paymentProgress = $registrationTotal > 0
        ? min(100, max(0, ($netPaid / $registrationTotal) * 100))
        : ($netPaid > 0 ? 100 : 0);
    $isSettled = $balanceDue <= 0;
@endphp

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
         data-testid="payment-summary-card">
    <div class="border-b border-slate-100 px-5 py-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 7.125A2.625 2.625 0 014.875 4.5h14.25a2.625 2.625 0 012.625 2.625v9.75a2.625 2.625 0 01-2.625 2.625H4.875a2.625 2.625 0 01-2.625-2.625v-9.75z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 9h19.5M17.25 15.75h.008v.008h-.008v-.008z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-slate-900">Payment Summary</h3>
                    <p class="mt-0.5 text-xs leading-5 text-slate-500">Live totals from the payment ledger</p>
                </div>
            </div>
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $summaryStatus->badgeClasses() }}"
                  data-testid="payment-summary-status">
                <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                {{ $summaryStatus->label() }}
            </span>
        </div>
    </div>

    <div class="px-5 py-5">
        <div class="rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 p-5 text-white shadow-sm"
             data-testid="payment-balance-hero">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-widest text-slate-300">
                        {{ $isSettled ? 'Account balance' : 'Balance due' }}
                    </p>
                    <p class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl" data-testid="payment-balance-due">
                        {{ number_format($balanceDue, 2) }}
                        <span class="text-sm font-semibold text-slate-300">{{ $currency }}</span>
                    </p>
                </div>
                <div class="rounded-xl bg-white/10 p-2.5 ring-1 ring-white/10">
                    @if($isSettled)
                        <svg class="h-5 w-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75l2.25 2.25L15 9.75m6 2.25a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>
            </div>

            <div class="mt-5">
                <div class="mb-2 flex items-center justify-between text-xs">
                    <span class="text-slate-300">Payment progress</span>
                    <span class="font-semibold text-white" data-testid="payment-progress-label">
                        {{ number_format($paymentProgress, 0) }}%
                    </span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-white/15"
                     role="progressbar"
                     aria-label="Payment progress"
                     aria-valuemin="0"
                     aria-valuemax="100"
                     aria-valuenow="{{ number_format($paymentProgress, 0, '.', '') }}"
                     data-testid="payment-progress">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-300 transition-all duration-500"
                         style="width: {{ number_format($paymentProgress, 2, '.', '') }}%"></div>
                </div>
                <div class="mt-2 flex justify-between text-xs text-slate-400">
                    <span>Paid {{ number_format($netPaid, 2) }} {{ $currency }}</span>
                    <span>Total {{ number_format($registrationTotal, 2) }} {{ $currency }}</span>
                </div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-3.5">
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                    Registration total
                </div>
                <p class="mt-2 text-base font-bold text-slate-900" data-testid="payment-total">
                    {{ number_format($registrationTotal, 2) }}
                    <span class="text-xs font-semibold text-slate-500">{{ $currency }}</span>
                </p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 p-3.5">
                <div class="flex items-center gap-2 text-xs font-medium text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Gross paid
                </div>
                <p class="mt-2 text-base font-bold text-emerald-900" data-testid="payment-gross-paid">
                    {{ number_format($paymentSummary['gross_paid'], 2) }}
                    <span class="text-xs font-semibold text-emerald-700">{{ $currency }}</span>
                </p>
            </div>
            <div class="rounded-xl border border-rose-100 bg-rose-50/70 p-3.5">
                <div class="flex items-center gap-2 text-xs font-medium text-rose-700">
                    <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                    Refunded
                </div>
                <p class="mt-2 text-base font-bold text-rose-900" data-testid="payment-refunded">
                    {{ number_format($paymentSummary['refunded'], 2) }}
                    <span class="text-xs font-semibold text-rose-700">{{ $currency }}</span>
                </p>
            </div>
            <div class="rounded-xl border border-blue-100 bg-blue-50/70 p-3.5">
                <div class="flex items-center gap-2 text-xs font-medium text-blue-700">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                    Net paid
                </div>
                <p class="mt-2 text-base font-bold text-blue-900" data-testid="payment-net-paid">
                    {{ number_format($netPaid, 2) }}
                    <span class="text-xs font-semibold text-blue-700">{{ $currency }}</span>
                </p>
            </div>
        </div>

    @if($paymentSummary['overpayment'] > 0)
        <div class="mt-4 flex gap-3 rounded-xl border border-blue-200 bg-blue-50 p-3.5 text-sm text-blue-800">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"></path>
            </svg>
            <div>
                <p class="font-semibold">Overpayment recorded</p>
                <p class="mt-0.5 text-xs leading-5 text-blue-700">
                    {{ number_format($paymentSummary['overpayment'], 2) }} {{ $currency }} is available for refund.
                </p>
            </div>
        </div>
    @endif

    <details class="group mt-4 rounded-xl border border-slate-200 bg-white" data-testid="payment-details">
        <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700">
            <span>Price & latest payment details</span>
            <svg class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </summary>
        <div class="space-y-3 border-t border-slate-100 px-4 py-4 text-sm">
            <div class="flex justify-between gap-3">
                <span class="text-slate-500">Base price</span>
                <span class="font-medium text-slate-900">{{ number_format($registration->base_price, 2) }} {{ $currency }}</span>
            </div>
            @if((float) ($registration->discount_amount ?? 0) > 0)
                <div class="flex justify-between gap-3">
                    <span class="text-slate-500">
                        Discount
                        @if($registration->promo_code)
                            <span class="font-mono text-xs text-emerald-700">({{ $registration->promo_code }})</span>
                        @endif
                    </span>
                    <span class="font-medium text-emerald-700">−{{ number_format($registration->discount_amount, 2) }} {{ $currency }}</span>
                </div>
            @endif
            @if($registration->tax_amount > 0)
                <div class="flex justify-between gap-3">
                    <span class="text-slate-500">
                        Tax ({{ $registration->registrationCategory->vat_percentage ?? $registration->event->vat_percentage ?? 0 }}%)
                    </span>
                    <span class="font-medium text-slate-900">{{ number_format($registration->tax_amount, 2) }} {{ $currency }}</span>
                </div>
            @endif
            <div class="border-t border-slate-100"></div>
        @if($registration->payment_method)
            <div class="flex justify-between gap-3">
                <span class="text-slate-500">Latest method</span>
                <span class="text-right font-medium text-slate-900">{{ $registration->payment_method }}</span>
            </div>
        @endif
        @if($registration->payment_reference)
            <div class="flex justify-between gap-3">
                <span class="text-slate-500">Latest reference</span>
                <span class="break-all text-right font-mono text-xs font-medium text-slate-900">{{ $registration->payment_reference }}</span>
            </div>
        @endif
        @if($registration->payment_date)
            <div class="flex justify-between gap-3">
                <span class="text-slate-500">Latest payment</span>
                <span class="text-right font-medium text-slate-900">{{ $registration->payment_date->format('M d, Y H:i') }}</span>
            </div>
        @endif
        </div>
    </details>

    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-4" data-testid="registration-promo-card">
        <div class="mb-3 flex items-start justify-between gap-3">
            <div>
                <h4 class="text-sm font-semibold text-slate-800">Promo code</h4>
                <p class="mt-0.5 text-xs text-slate-500">Redeem using the same rules as online registration.</p>
            </div>
            @if($registration->promo_code)
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 font-mono text-xs font-semibold text-emerald-800"
                      data-testid="registration-applied-promo">
                    {{ $registration->promo_code }}
                </span>
            @endif
        </div>

        @if($registration->promo_code)
            <div class="mb-3 rounded-lg border border-emerald-100 bg-white px-3 py-2 text-sm text-slate-700">
                Applied discount:
                <span class="font-semibold text-emerald-700">
                    {{ number_format((float) $registration->discount_amount, 2) }} {{ $currency }}
                </span>
            </div>
            <form action="{{ route('admin.registrations.promo.remove', $registration) }}"
                  method="POST"
                  onsubmit="return confirm('Remove the applied promo and restore category pricing?');"
                  class="mb-3">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50"
                        data-testid="registration-remove-promo">
                    Remove promo
                </button>
            </form>
        @endif

        <form action="{{ route('admin.registrations.promo.redeem', $registration) }}"
              method="POST"
              class="space-y-3"
              data-testid="registration-redeem-promo-form">
            @csrf
            <div>
                <label for="registration_promo_code" class="mb-1 block text-xs font-medium text-slate-600">
                    {{ $registration->promo_code ? 'Replace with another code' : 'Enter promo code' }}
                </label>
                <input id="registration_promo_code"
                       type="text"
                       name="promo_code"
                       value="{{ old('promo_code') }}"
                       maxlength="50"
                       autocomplete="off"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('promo_code') border-red-500 @enderror"
                       placeholder="e.g. RETURNING26"
                       data-testid="registration-promo-code-input">
                @error('promo_code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @if($registration->promo_code)
                <label class="flex items-start gap-2 text-xs text-slate-600">
                    <input type="checkbox"
                           name="replace_existing"
                           value="1"
                           class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                           data-testid="registration-promo-replace"
                           {{ old('replace_existing') ? 'checked' : '' }}>
                    <span>Replace the currently applied promo code</span>
                </label>
            @endif
            <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    data-testid="registration-redeem-promo">
                Redeem promo code
            </button>
        </form>
    </div>

    <button type="button"
            onclick="document.getElementById('logPaymentModal').classList.remove('hidden')"
            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-indigo-200 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            data-testid="open-log-payment-modal">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"></path>
        </svg>
        Log a payment
    </button>
    </div>
</section>
