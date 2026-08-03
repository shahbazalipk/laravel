<?php

namespace App\Http\Requests\Admin;

use App\Enums\PromoDiscountType;
use App\Models\PromoCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePromoCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var PromoCode|null $promoCode */
        $promoCode = $this->route('promo_code');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('promo_codes', 'code')
                    ->where(fn ($query) => $query
                        ->where('event_id', config('event.event_id'))
                        ->where('org_id', config('event.org_id')))
                    ->ignore($promoCode?->id),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'discount_type' => ['required', Rule::enum(PromoDiscountType::class)],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_total_uses' => ['nullable', 'integer', 'min:1'],
            'max_uses_per_email' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
            'restrict_to_email_list' => ['sometimes', 'boolean'],
            'email_list_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:5120'],
            'clear_email_list' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('discount_type');
            $value = (float) $this->input('discount_value');

            if ($type === PromoDiscountType::Percentage->value && $value > 100) {
                $validator->errors()->add('discount_value', 'Percentage discounts cannot exceed 100%.');
            }

            if ($type === PromoDiscountType::Fixed->value && blank($this->input('currency'))) {
                $validator->errors()->add('currency', 'Currency is required for fixed-amount discounts.');
            }

            /** @var PromoCode|null $promoCode */
            $promoCode = $this->route('promo_code');
            $restrict = $this->boolean('restrict_to_email_list');
            $hasFile = $this->hasFile('email_list_file');
            $clearing = $this->boolean('clear_email_list');
            $existingCount = $promoCode?->emails()->count() ?? 0;

            if ($restrict && ! $hasFile && ($promoCode === null || $existingCount === 0 || $clearing)) {
                $validator->errors()->add(
                    'email_list_file',
                    'Upload a CSV of allowed emails when restricting this promo code to an email list.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Use only letters, numbers, hyphens, and underscores.',
            'expires_at.after_or_equal' => 'Expiry must be on or after the start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code', ''))),
            'is_active' => $this->boolean('is_active'),
            'restrict_to_email_list' => $this->boolean('restrict_to_email_list'),
            'clear_email_list' => $this->boolean('clear_email_list'),
            'currency' => $this->filled('currency')
                ? strtoupper(trim((string) $this->input('currency')))
                : null,
            'max_total_uses' => $this->filled('max_total_uses') ? $this->input('max_total_uses') : null,
            'max_uses_per_email' => $this->filled('max_uses_per_email') ? $this->input('max_uses_per_email') : null,
            'starts_at' => $this->filled('starts_at') ? $this->input('starts_at') : null,
            'expires_at' => $this->filled('expires_at') ? $this->input('expires_at') : null,
        ]);
    }
}
