<?php

namespace App\Http\Requests\PublicRegistration;

use App\Registration\Rules\UniqueEventRegistrationEmail;
use Illuminate\Foundation\Http\FormRequest;

class StartEmailStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', new UniqueEventRegistrationEmail],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }
}
