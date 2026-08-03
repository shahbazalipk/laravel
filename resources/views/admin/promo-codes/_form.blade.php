@php
    $isEdit = isset($promoCode);
    $selectedType = old('discount_type', $isEdit ? $promoCode->discount_type->value : 'percentage');
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="code" class="mb-2 block text-sm font-medium text-gray-700">
            Promo code <span class="text-red-500">*</span>
        </label>
        <input type="text"
               name="code"
               id="code"
               value="{{ old('code', $isEdit ? $promoCode->code : '') }}"
               required
               maxlength="50"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 font-mono uppercase focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('code') border-red-500 @enderror"
               placeholder="e.g. EARLYBIRD25"
               data-testid="promo-code-input">
        <p class="mt-1 text-xs text-gray-500">Letters, numbers, hyphens, and underscores only.</p>
        @error('code')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="mb-2 block text-sm font-medium text-gray-700">Display name</label>
        <input type="text"
               name="name"
               id="name"
               value="{{ old('name', $isEdit ? $promoCode->name : '') }}"
               maxlength="255"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
               placeholder="e.g. Early bird discount"
               data-testid="promo-name-input">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="discount_type" class="mb-2 block text-sm font-medium text-gray-700">
            Discount type <span class="text-red-500">*</span>
        </label>
        <select name="discount_type"
                id="discount_type"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('discount_type') border-red-500 @enderror"
                data-testid="promo-discount-type">
            @foreach($discountTypes as $type)
                <option value="{{ $type->value }}" {{ $selectedType === $type->value ? 'selected' : '' }}>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
        @error('discount_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="discount_value" class="mb-2 block text-sm font-medium text-gray-700">
            <span id="discount-value-label">Discount value</span> <span class="text-red-500">*</span>
        </label>
        <div class="flex gap-2">
            <input type="number"
                   name="discount_value"
                   id="discount_value"
                   value="{{ old('discount_value', $isEdit ? $promoCode->discount_value : '') }}"
                   required
                   min="0.01"
                   step="0.01"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('discount_value') border-red-500 @enderror"
                   data-testid="promo-discount-value">
            <input type="text"
                   name="currency"
                   id="currency"
                   value="{{ old('currency', $isEdit ? ($promoCode->currency ?: $defaultCurrency) : $defaultCurrency) }}"
                   maxlength="3"
                   class="w-24 rounded-lg border border-gray-300 px-3 py-2 font-mono uppercase focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('currency') border-red-500 @enderror"
                   data-testid="promo-currency"
                   aria-label="Currency">
        </div>
        <p id="discount-value-hint" class="mt-1 text-xs text-gray-500"></p>
        @error('discount_value')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('currency')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="starts_at" class="mb-2 block text-sm font-medium text-gray-700">Starts at</label>
        <input type="datetime-local"
               name="starts_at"
               id="starts_at"
               value="{{ old('starts_at', $isEdit && $promoCode->starts_at ? $promoCode->starts_at->format('Y-m-d\TH:i') : '') }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('starts_at') border-red-500 @enderror"
               data-testid="promo-starts-at">
        @error('starts_at')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="expires_at" class="mb-2 block text-sm font-medium text-gray-700">Expires at</label>
        <input type="datetime-local"
               name="expires_at"
               id="expires_at"
               value="{{ old('expires_at', $isEdit && $promoCode->expires_at ? $promoCode->expires_at->format('Y-m-d\TH:i') : '') }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('expires_at') border-red-500 @enderror"
               data-testid="promo-expires-at">
        <p class="mt-1 text-xs text-gray-500">Leave blank for no expiry.</p>
        @error('expires_at')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="max_total_uses" class="mb-2 block text-sm font-medium text-gray-700">Max total uses</label>
        <input type="number"
               name="max_total_uses"
               id="max_total_uses"
               value="{{ old('max_total_uses', $isEdit ? $promoCode->max_total_uses : '') }}"
               min="1"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('max_total_uses') border-red-500 @enderror"
               placeholder="Unlimited"
               data-testid="promo-max-total-uses">
        <p class="mt-1 text-xs text-gray-500">How many times this code can be redeemed in total.</p>
        @error('max_total_uses')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="max_uses_per_email" class="mb-2 block text-sm font-medium text-gray-700">Max uses per email</label>
        <input type="number"
               name="max_uses_per_email"
               id="max_uses_per_email"
               value="{{ old('max_uses_per_email', $isEdit ? $promoCode->max_uses_per_email : '1') }}"
               min="1"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('max_uses_per_email') border-red-500 @enderror"
               placeholder="Unlimited"
               data-testid="promo-max-uses-per-email">
        <p class="mt-1 text-xs text-gray-500">Limit redemptions by the same attendee email. Leave blank for unlimited.</p>
        @error('max_uses_per_email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6">
    <label for="description" class="mb-2 block text-sm font-medium text-gray-700">Description</label>
    <textarea name="description"
              id="description"
              rows="3"
              class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
              placeholder="Optional internal notes"
              data-testid="promo-description">{{ old('description', $isEdit ? $promoCode->description : '') }}</textarea>
    @error('description')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4" data-testid="promo-email-allowlist-section">
    <label class="flex items-start gap-3">
        <input type="checkbox"
               name="restrict_to_email_list"
               id="restrict_to_email_list"
               value="1"
               {{ old('restrict_to_email_list', $isEdit ? $promoCode->restrict_to_email_list : false) ? 'checked' : '' }}
               class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
               data-testid="promo-restrict-email-list">
        <span>
            <span class="block text-sm font-medium text-gray-800">Restrict to email allowlist</span>
            <span class="mt-0.5 block text-xs text-gray-500">Only emails on the uploaded list can redeem this shared code.</span>
        </span>
    </label>

    <div id="promo-email-list-fields" class="mt-4 space-y-3 {{ old('restrict_to_email_list', $isEdit ? $promoCode->restrict_to_email_list : false) ? '' : 'hidden' }}">
        @if($isEdit)
            <p class="text-sm text-slate-700" data-testid="promo-email-list-count">
                Current allowlist:
                <span class="font-semibold">{{ $promoCode->emails_count ?? $promoCode->emails()->count() }}</span> email(s)
            </p>
        @endif

        <div>
            <label for="email_list_file" class="mb-2 block text-sm font-medium text-gray-700">
                Email list CSV {{ $isEdit ? '(replaces existing list)' : '' }}
            </label>
            <input type="file"
                   name="email_list_file"
                   id="email_list_file"
                   accept=".csv,text/csv,text/plain"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('email_list_file') border-red-500 @enderror"
                   data-testid="promo-email-list-file">
            <p class="mt-1 text-xs text-gray-500">
                One email per row. Download
                <a href="{{ asset('samples/promo-emails-sample.csv') }}" class="text-indigo-600 hover:underline" data-testid="promo-email-sample-download">sample CSV</a>.
            </p>
            @error('email_list_file')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if($isEdit && ($promoCode->emails_count ?? $promoCode->emails()->count()) > 0)
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox"
                       name="clear_email_list"
                       value="1"
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       data-testid="promo-clear-email-list">
                Clear allowlist on save (upload a new CSV if you still want restriction enabled)
            </label>
        @endif
    </div>
</div>

<div class="mt-6">
    <label class="flex items-center">
        <input type="checkbox"
               name="is_active"
               value="1"
               {{ old('is_active', $isEdit ? $promoCode->is_active : true) ? 'checked' : '' }}
               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
               data-testid="promo-is-active">
        <span class="ml-2 text-sm text-gray-700">Active (available for use when within date and usage limits)</span>
    </label>
</div>

@if($isEdit)
    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        Used <span class="font-semibold text-slate-900">{{ $promoCode->used_count }}</span> time{{ $promoCode->used_count === 1 ? '' : 's' }}
        @if($promoCode->max_total_uses !== null)
            · {{ $promoCode->remainingUses() }} remaining
        @endif
    </div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const typeSelect = document.getElementById('discount_type');
        const valueLabel = document.getElementById('discount-value-label');
        const hint = document.getElementById('discount-value-hint');
        const currency = document.getElementById('currency');
        const valueInput = document.getElementById('discount_value');
        const restrictToggle = document.getElementById('restrict_to_email_list');
        const emailFields = document.getElementById('promo-email-list-fields');

        const syncType = () => {
            const isPercentage = typeSelect.value === 'percentage';
            valueLabel.textContent = isPercentage ? 'Percentage' : 'Fixed amount';
            hint.textContent = isPercentage
                ? 'Enter a value between 0.01 and 100.'
                : 'Enter the exact discount amount in the selected currency.';
            currency.classList.toggle('hidden', isPercentage);
            currency.required = !isPercentage;
            valueInput.max = isPercentage ? '100' : '';
        };

        const syncRestrict = () => {
            emailFields?.classList.toggle('hidden', !restrictToggle?.checked);
        };

        typeSelect.addEventListener('change', syncType);
        restrictToggle?.addEventListener('change', syncRestrict);
        syncType();
        syncRestrict();
    });
</script>
@endpush
