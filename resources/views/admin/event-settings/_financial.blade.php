<h2 class="text-lg font-semibold text-gray-800 mb-4">Financial Settings</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
            Currency
        </label>
        <input type="text" 
               name="currency" 
               id="currency" 
               value="{{ old('currency', $event->currency) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('currency') border-red-500 @enderror"
               placeholder="e.g., AED, USD">
        @error('currency')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="vat_percentage" class="block text-sm font-medium text-gray-700 mb-2">
            VAT Percentage (%)
        </label>
        <input type="number" 
               name="vat_percentage" 
               id="vat_percentage" 
               step="0.01"
               value="{{ old('vat_percentage', $event->vat_percentage) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('vat_percentage') border-red-500 @enderror"
               placeholder="0.00">
        @error('vat_percentage')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="flex items-center">
            <input type="checkbox" 
                   name="tax_inclusive" 
                   value="1"
                   {{ old('tax_inclusive', $event->tax_inclusive) ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Tax Inclusive (prices include tax)</span>
        </label>
    </div>
</div>
