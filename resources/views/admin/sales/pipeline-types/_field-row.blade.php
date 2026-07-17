<div class="field-row grid gap-3 rounded-xl border border-gray-200 p-3 md:grid-cols-12">
    @if(!empty($field['public_id']))
        <input type="hidden" name="{{ $prefix }}[{{ $index }}][public_id]" value="{{ $field['public_id'] }}">
    @endif
    <div class="md:col-span-5">
        <input name="{{ $prefix }}[{{ $index }}][label]" type="text" required placeholder="Label"
               value="{{ $field['label'] ?? '' }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div class="md:col-span-4">
        <select name="{{ $prefix }}[{{ $index }}][type]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            @foreach($fieldTypes as $ft)
                <option value="{{ $ft->value }}" @selected(($field['type'] ?? 'text') === $ft->value)>{{ $ft->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-3 flex justify-end">
        <button type="button" class="remove-field text-sm font-semibold text-red-600 hover:text-red-800">Remove</button>
    </div>
</div>
