<div id="logGroupPaymentModal"
     class="{{ session('open_group_payment_modal') || $errors->has('amount') ? '' : 'hidden' }} fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm"
     data-testid="log-group-payment-modal">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
            <div class="border-b px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Log Group Payment</h2>
            </div>
            <form action="{{ route('admin.groups.payments.store', $group) }}" method="POST" class="space-y-4 px-5 py-5">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Amount *</label>
                        <input type="number" step="0.01" min="0.01" name="amount" required
                               value="{{ old('amount', $paymentSummary['balance_due'] > 0 ? number_format($paymentSummary['balance_due'], 2, '.', '') : number_format($paymentSummary['group_total'], 2, '.', '')) }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Currency</label>
                        <input type="text" name="currency" value="{{ old('currency', $group->billingCurrency()) }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Method</label>
                        <input type="text" name="method" value="{{ old('method') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Reference</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Outcome *</label>
                    <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="succeeded" selected>Succeeded</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                </div>
                <div class="flex justify-end gap-3 border-t pt-4">
                    <button type="button" onclick="document.getElementById('logGroupPaymentModal').classList.add('hidden')"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Cancel</button>
                    <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="refundGroupPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
            <div class="border-b px-5 py-4">
                <h2 class="text-lg font-semibold">Record Refund</h2>
                <p class="text-sm text-gray-500">Max: <span id="group_refund_max_label">0.00</span></p>
            </div>
            <form id="refundGroupPaymentForm" method="POST" class="space-y-4 px-5 py-5">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium">Amount *</label>
                    <input id="group_refund_amount" type="number" step="0.01" min="0.01" name="amount" required class="w-full rounded-lg border px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3 border-t pt-4">
                    <button type="button" onclick="document.getElementById('refundGroupPaymentModal').classList.add('hidden')" class="rounded-lg border px-4 py-2 text-sm">Cancel</button>
                    <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm text-white">Save Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openGroupRefundModal(paymentPublicId, maxAmount, currency) {
    const form = document.getElementById('refundGroupPaymentForm');
    form.action = @json(route('admin.groups.payments.refund', [$group, '__PAYMENT__'])).replace('__PAYMENT__', paymentPublicId);
    document.getElementById('group_refund_max_label').textContent = Number(maxAmount).toFixed(2) + ' ' + currency;
    document.getElementById('group_refund_amount').max = maxAmount;
    document.getElementById('group_refund_amount').value = Number(maxAmount).toFixed(2);
    document.getElementById('refundGroupPaymentModal').classList.remove('hidden');
}
</script>
