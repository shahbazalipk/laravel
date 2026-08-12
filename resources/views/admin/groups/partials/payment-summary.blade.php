@php
    use App\Payments\Enums\RegistrationPaymentSummaryStatus;
    $summaryStatus = $paymentSummary['summary_status'] instanceof RegistrationPaymentSummaryStatus
        ? $paymentSummary['summary_status']
        : RegistrationPaymentSummaryStatus::tryFrom($group->payment_status) ?? RegistrationPaymentSummaryStatus::Pending;
    $currency = $paymentSummary['currency'] ?? ($group->billingCurrency());
    $groupTotal = (float) $paymentSummary['group_total'];
    $netPaid = (float) $paymentSummary['net_paid'];
    $balanceDue = (float) $paymentSummary['balance_due'];
    $paymentProgress = $groupTotal > 0 ? min(100, max(0, ($netPaid / $groupTotal) * 100)) : ($netPaid > 0 ? 100 : 0);
    $isSettled = $balanceDue <= 0;
@endphp

<section id="group-payments"
         class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
         data-testid="group-payment-summary-card">
    <div class="border-b border-slate-100 px-5 py-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Group Payment Summary</h3>
                <p class="mt-0.5 text-xs text-slate-500">Billing total: {{ number_format($groupTotal, 2) }} {{ $currency }}</p>
            </div>
            <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $summaryStatus->badgeClasses() }}">
                {{ $summaryStatus->label() }}
            </span>
        </div>
    </div>

    <div class="px-5 py-5">
        <div class="rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 p-5 text-white">
            <p class="text-xs font-medium uppercase tracking-widest text-slate-300">
                {{ $isSettled ? 'Settled' : 'Balance due' }}
            </p>
            <p class="mt-2 text-2xl font-bold" data-testid="group-payment-balance-due">
                {{ number_format($balanceDue, 2) }} <span class="text-sm font-semibold text-slate-300">{{ $currency }}</span>
            </p>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/15">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-300"
                     style="width: {{ number_format($paymentProgress, 2, '.', '') }}%"></div>
            </div>
            <div class="mt-2 flex justify-between text-xs text-slate-400">
                <span>Paid {{ number_format($netPaid, 2) }}</span>
                <span>Total {{ number_format($groupTotal, 2) }}</span>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-3.5">
                <p class="text-xs text-slate-500">Gross paid</p>
                <p class="mt-1 font-bold text-slate-900">{{ number_format($paymentSummary['gross_paid'], 2) }} {{ $currency }}</p>
            </div>
            <div class="rounded-xl border border-blue-100 bg-blue-50/70 p-3.5">
                <p class="text-xs text-blue-700">Net paid</p>
                <p class="mt-1 font-bold text-blue-900">{{ number_format($netPaid, 2) }} {{ $currency }}</p>
            </div>
        </div>

        @if($group->total_amount)
            <p class="mt-3 text-xs text-slate-500">Using group billing total set on edit. Leave blank there to auto-sum member registration totals.</p>
        @else
            <p class="mt-3 text-xs text-slate-500">Total is summed from linked registration prices. Set a fixed billing total on the group edit page if needed.</p>
        @endif

        <button type="button"
                onclick="document.getElementById('logGroupPaymentModal').classList.remove('hidden')"
                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                data-testid="open-group-log-payment-modal">
            Log a payment
        </button>
    </div>
</section>
