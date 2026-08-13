<?php

namespace App\Http\Requests\Admin;

use App\Registration\Enums\RegistrationSavedViewVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationSavedViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_default' => $this->boolean('is_default'),
            'share_user_ids' => array_values(array_filter((array) $this->input('share_user_ids', []))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'visibility' => ['required', Rule::enum(RegistrationSavedViewVisibility::class)],
            'is_default' => ['sometimes', 'boolean'],
            'columns' => ['required', 'array', 'min:1'],
            'columns.*' => ['required', 'string', 'max:80'],
            'share_user_ids' => ['nullable', 'array'],
            'share_user_ids.*' => ['integer'],
        ];
    }
}
