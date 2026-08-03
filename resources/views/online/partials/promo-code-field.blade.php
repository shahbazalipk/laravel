{{--
  Shared promo apply/remove UI for online registration.

  @var string $testidPrefix  e.g. wizard | single-page
  @var string|null $appliedPromoCode
  @var float|int|null $appliedDiscount
  @var string|null $currency
  @var string|null $applyUrl   Server POST (wizard). Null = client Apply button.
  @var string|null $removeUrl  Server DELETE (wizard). Null = client Remove button.
  @var string|null $inputValue
  @var bool $useHiddenWhenApplied  When true, emit hidden promo_code for the parent form.
--}}
@php
    $prefix = $testidPrefix ?? 'promo';
    $applied = $appliedPromoCode ? strtoupper(trim((string) $appliedPromoCode)) : null;
    $discount = (float) ($appliedDiscount ?? 0);
    $currencyLabel = $currency ?? '';
    $value = old('promo_code', $inputValue ?? $applied ?? '');
    $useHiddenWhenApplied = $useHiddenWhenApplied ?? true;
@endphp

<div class="space-y-3"
     data-testid="{{ $prefix }}-promo-card"
     data-promo-root
     @if($applied) data-promo-applied="1" @endif>
    @if($applied)
        <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3"
             data-testid="{{ $prefix }}-promo-applied">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-900">
                        Promo <span class="font-mono">{{ $applied }}</span> applied
                    </p>
                    @if($discount > 0)
                        <p class="mt-0.5 text-xs text-emerald-800">
                            Discount {{ number_format($discount, 2) }} {{ $currencyLabel }}
                        </p>
                    @endif
                </div>
                @if($removeUrl)
                    <form method="POST"
                          action="{{ $removeUrl }}"
                          onsubmit="return confirm('Remove this promo code and restore the original total?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 sm:w-auto"
                                data-testid="{{ $prefix }}-promo-remove">
                            Remove
                        </button>
                    </form>
                @else
                    <button type="button"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 sm:w-auto"
                            data-promo-remove
                            data-testid="{{ $prefix }}-promo-remove">
                        Remove
                    </button>
                @endif
            </div>
        </div>
        @if($useHiddenWhenApplied)
            <input type="hidden"
                   name="promo_code"
                   value="{{ $applied }}"
                   data-testid="{{ $prefix }}-promo-code"
                   data-promo-input>
        @endif
    @else
        <div>
            <label for="{{ $prefix }}_promo_code" class="mb-2 block text-sm font-medium text-slate-700">Promo code</label>
            @if($applyUrl)
                <form method="POST" action="{{ $applyUrl }}" class="space-y-2" data-testid="{{ $prefix }}-promo-apply-form">
                    @csrf
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input id="{{ $prefix }}_promo_code"
                               type="text"
                               name="promo_code"
                               value="{{ $value }}"
                               maxlength="50"
                               autocomplete="off"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 font-mono text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('promo_code') border-red-500 @enderror"
                               placeholder="Optional"
                               data-testid="{{ $prefix }}-promo-code"
                               data-promo-input>
                        <button type="submit"
                                class="inline-flex shrink-0 items-center justify-center rounded-xl border border-indigo-200 bg-white px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50"
                                data-testid="{{ $prefix }}-promo-apply">
                            Apply
                        </button>
                    </div>
                    <p class="text-xs text-slate-500">Verified against your registration email. You can remove it anytime before submitting.</p>
                    @error('promo_code')
                        <p class="text-sm text-red-600" data-testid="{{ $prefix }}-promo-error">{{ $message }}</p>
                    @enderror
                </form>
            @else
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input id="{{ $prefix }}_promo_code"
                           type="text"
                           name="promo_code"
                           value="{{ $value }}"
                           maxlength="50"
                           autocomplete="off"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 font-mono text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('promo_code') border-red-500 @enderror"
                           placeholder="Optional"
                           data-testid="{{ $prefix }}-promo-code"
                           data-promo-input>
                    <button type="button"
                            class="inline-flex shrink-0 items-center justify-center rounded-xl border border-indigo-200 bg-white px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50"
                            data-promo-apply
                            data-testid="{{ $prefix }}-promo-apply">
                        Apply
                    </button>
                </div>
                <p class="mt-1 text-xs text-slate-500">If you have a returning-attendee or partner code, enter it and click Apply to update the total.</p>
                <p class="mt-1 hidden text-sm text-red-600" data-promo-error data-testid="{{ $prefix }}-promo-error"></p>
                @error('promo_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endif
        </div>
    @endif
</div>
