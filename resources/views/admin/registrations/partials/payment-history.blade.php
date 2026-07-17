@php
    use App\Payments\Enums\PaymentEntryStatus;
    use App\Payments\Enums\PaymentEntryType;
@endphp

<div class="bg-white rounded-lg shadow-sm p-6" data-testid="payment-history-card">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Payment History</h3>
        <p class="text-sm text-gray-500 mt-1">Immutable ledger of payments, refunds, and reversals.</p>
    </div>

    @if($registration->paymentEntries->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center" data-testid="payment-history-empty">
            <p class="text-sm font-medium text-gray-700">No payment entries yet</p>
            <p class="mt-1 text-sm text-gray-500">Log the first payment to start the history.</p>
        </div>
    @else
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" data-testid="payment-history-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">When</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Details</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Recorded By</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($registration->paymentEntries->sortByDesc('occurred_at') as $entry)
                        @include('admin.registrations.partials.payment-history-row', [
                            'entry' => $entry,
                            'registration' => $registration,
                            'refundableByPayment' => $refundableByPayment,
                            'layout' => 'table',
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-3 md:hidden" data-testid="payment-history-cards">
            @foreach($registration->paymentEntries->sortByDesc('occurred_at') as $entry)
                @include('admin.registrations.partials.payment-history-row', [
                    'entry' => $entry,
                    'registration' => $registration,
                    'refundableByPayment' => $refundableByPayment,
                    'layout' => 'card',
                ])
            @endforeach
        </div>
    @endif
</div>
