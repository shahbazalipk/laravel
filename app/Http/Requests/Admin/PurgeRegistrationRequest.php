<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurgeRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirmation' => [
                'required',
                'string',
                Rule::in([(string) $this->route('registration')->registration_number]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmation.required' => 'Enter the registration number to confirm permanent deletion.',
            'confirmation.in' => 'The registration number does not match.',
        ];
    }
}
