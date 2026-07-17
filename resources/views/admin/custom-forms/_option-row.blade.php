<div class="grid grid-cols-[1fr_1fr_auto] gap-2" data-option-row>
    <input name="options[{{ $index }}][label]" value="{{ data_get($option, 'label', '') }}" placeholder="Label"
           class="min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
    <input name="options[{{ $index }}][value]" value="{{ data_get($option, 'value', '') }}" placeholder="stored_value"
           class="min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-sm">
    <button type="button" data-remove-option class="rounded-lg px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50" aria-label="Remove option">×</button>
</div>
