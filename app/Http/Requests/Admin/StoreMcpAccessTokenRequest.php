<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMcpAccessTokenRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in(['registrations.read'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $abilities = $this->input('abilities', ['registrations.read']);

        if (! is_array($abilities)) {
            $abilities = [$abilities];
        }

        if ($abilities === []) {
            $abilities = ['registrations.read'];
        }

        $this->merge([
            'abilities' => array_values(array_unique($abilities)),
        ]);
    }
}
