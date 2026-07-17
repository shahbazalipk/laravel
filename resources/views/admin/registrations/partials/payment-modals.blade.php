<div id="logPaymentModal"
     class="{{ session('open_payment_modal') || $errors->has('amount') || $errors->has('status') ? '' : 'hidden' }} fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm sm:p-6"
     data-testid="log-payment-modal">
    <div class="flex min-h-full items-start justify-center pt-4 sm:items-center sm:pt-0">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Log Payment</h2>
                    <p class="mt-1 text-sm text-gray-500">Record a successful or failed payment against this registration.</p>
                </div>
                <button type="button"
                        onclick="document.getElementById('logPaymentModal').classList.add('hidden')"
                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-50 hover:text-gray-600"
                        aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.registrations.payments.store', $registration) }}"
                  method="POST"
                  class="space-y-4 px-5 py-5 sm:px-6"
                  data-testid="log-payment-form">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="payment_amount" class="mb-2 block text-sm font-medium text-gray-700">Amount *</label>
                        <input id="payment_amount"
                               type="number"
                               step="0.01"
                               min="0.01"
                               name="amount"
                               value="{{ old('amount', $paymentSummary['balance_due'] > 0 ? number_format($paymentSummary['balance_due'], 2, '.', '') : number_format($registration->total_amount, 2, '.', '')) }}"
                               required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                               data-testid="payment-amount">
                    </div>
                    <div>
                        <label for="payment_currency" class="mb-2 block text-sm font-medium text-gray-700">Currency</label>
                        <input id="payment_currency"
                               type="text"
                               name="currency"
                               value="{{ old('currency', $registration->currency ?: 'AED') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="payment_method" class="mb-2 block text-sm font-medium text-gray-700">Method</label>
                        <input id="payment_method"
                               type="text"
                               name="method"
                               value="{{ old('method') }}"
                               placeholder="Credit Card, Bank Transfer..."
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                               data-testid="payment-method">
                    </div>
                    <div>
                        <label for="payment_reference" class="mb-2 block text-sm font-medium text-gray-700">Reference</label>
                        <input id="payment_reference"
                               type="text"
                               name="reference"
                               value="{{ old('reference') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                               data-testid="payment-reference">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="payment_occurred_at" class="mb-2 block text-sm font-medium text-gray-700">Occurred At</label>
                        <input id="payment_occurred_at"
                               type="datetime-local"
                               name="occurred_at"
                               value="{{ old('occurred_at', now()->format('Y-m-d\\TH:i')) }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div>
                        <label for="payment_status" class="mb-2 block text-sm font-medium text-gray-700">Outcome *</label>
                        <select id="payment_status"
                                name="status"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                data-testid="payment-status">
                            <option value="succeeded" {{ old('status', 'succeeded') === 'succeeded' ? 'selected' : '' }}>Succeeded</option>
                            <option value="failed" {{ old('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="payment_notes" class="mb-2 block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="payment_notes"
                              name="notes"
                              rows="3"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                              data-testid="payment-notes">{{ old('notes') }}</textarea>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
                    <button type="button"
                            onclick="document.getElementById('logPaymentModal').classList.add('hidden')"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700"
                            data-testid="submit-log-payment">
                        Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="refundPaymentModal"
     class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm sm:p-6"
     data-testid="refund-payment-modal">
    <div class="flex min-h-full items-start justify-center pt-4 sm:items-center sm:pt-0">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Record Refund</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Max refundable: <span id="refund_max_label" class="font-medium text-gray-800">0.00</span>
                    </p>
                </div>
                <button type="button"
                        onclick="document.getElementById('refundPaymentModal').classList.add('hidden')"
                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-50 hover:text-gray-600"
                        aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="refundPaymentForm" method="POST" class="space-y-4 px-5 py-5" data-testid="refund-payment-form">
                @csrf
                <div>
                    <label for="refund_amount" class="mb-2 block text-sm font-medium text-gray-700">Amount *</label>
                    <input id="refund_amount"
                           type="number"
                           step="0.01"
                           min="0.01"
                           name="amount"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                           data-testid="refund-amount">
                </div>
                <div>
                    <label for="refund_method" class="mb-2 block text-sm font-medium text-gray-700">Method</label>
                    <input id="refund_method" type="text" name="method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="refund_reference" class="mb-2 block text-sm font-medium text-gray-700">Reference</label>
                    <input id="refund_reference" type="text" name="reference" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="refund_notes" class="mb-2 block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="refund_notes" name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
                    <button type="button"
                            onclick="document.getElementById('refundPaymentModal').classList.add('hidden')"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-rose-700"
                            data-testid="submit-refund-payment">
                        Save Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRefundModal(publicId, maxAmount, currency) {
        const form = document.getElementById('refundPaymentForm');
        const amountInput = document.getElementById('refund_amount');
        const maxLabel = document.getElementById('refund_max_label');

        form.action = @json(url('/admin/registrations/'.$registration->hash.'/payments')).replace(/\/$/, '') + '/' + publicId + '/refund';
        amountInput.max = maxAmount;
        amountInput.value = Number(maxAmount).toFixed(2);
        maxLabel.textContent = Number(maxAmount).toFixed(2) + ' ' + currency;
        document.getElementById('refundPaymentModal').classList.remove('hidden');
    }
</script>
