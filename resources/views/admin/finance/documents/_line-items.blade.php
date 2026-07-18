<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
        <div><h2 class="font-bold text-slate-900">Line items</h2><p class="mt-1 text-sm text-slate-500">Add the products, services or charges on this document.</p></div>
        <button type="button" data-add-line class="rounded-xl bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-700 hover:bg-indigo-100">Add line</button>
    </div>
    <div data-lines class="mt-5 space-y-3">
        @foreach(old('items', [['description' => '', 'quantity' => '1', 'unit_price' => '', 'tax_rate' => '0', 'discount_amount' => '0']]) as $index => $item)
            <div data-line class="grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_7rem_9rem_7rem_9rem_auto]">
                <div><label class="mb-1 block text-xs font-semibold text-slate-600">Description</label><input name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="mb-1 block text-xs font-semibold text-slate-600">Quantity</label><input type="number" step="0.0001" min="0.0001" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="mb-1 block text-xs font-semibold text-slate-600">Unit price</label><input type="number" step="0.0001" min="0" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="mb-1 block text-xs font-semibold text-slate-600">Tax %</label><input type="number" step="0.0001" min="0" max="100" name="items[{{ $index }}][tax_rate]" value="{{ $item['tax_rate'] ?? 0 }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="mb-1 block text-xs font-semibold text-slate-600">Discount</label><input type="number" step="0.0001" min="0" name="items[{{ $index }}][discount_amount]" value="{{ $item['discount_amount'] ?? 0 }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <button type="button" data-remove-line class="self-end rounded-lg px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" aria-label="Remove line">×</button>
            </div>
        @endforeach
    </div>
</section>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-add-line]').forEach((button) => {
        button.addEventListener('click', () => {
            const container = button.closest('section').querySelector('[data-lines]');
            const source = container.querySelector('[data-line]');
            const clone = source.cloneNode(true);
            const index = container.querySelectorAll('[data-line]').length;
            clone.querySelectorAll('input').forEach((input) => {
                input.name = input.name.replace(/items\[\d+]/, `items[${index}]`);
                input.value = input.name.includes('[quantity]') ? '1' : (input.name.includes('[tax_rate]') || input.name.includes('[discount_amount]') ? '0' : '');
            });
            container.appendChild(clone);
        });
    });
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-line]');
        if (button && button.closest('[data-lines]').querySelectorAll('[data-line]').length > 1) {
            button.closest('[data-line]').remove();
        }
    });
});
</script>
@endpush
@endonce
