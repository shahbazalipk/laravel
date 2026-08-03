<?php

namespace App\Http\Requests\PublicRegistration;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'promo_code' => ['nullable', 'string', 'max:50'],
            'terms_accepted' => ['required', 'accepted'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('promo_code')) {
            $this->merge([
                'promo_code' => strtoupper(trim((string) $this->input('promo_code'))),
            ]);
        } else {
            $this->merge(['promo_code' => null]);
        }
    }
}
