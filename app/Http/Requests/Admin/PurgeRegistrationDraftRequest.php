<?php

namespace App\Http\Requests\Admin;

use App\Registration\Models\RegistrationDraft;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurgeRegistrationDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var RegistrationDraft $draft */
        $draft = $this->route('draft');

        return [
            'confirmation' => [
                'required',
                'string',
                Rule::in([$draft->displayReference()]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmation.required' => 'Enter the draft reference to confirm permanent deletion.',
            'confirmation.in' => 'The draft reference does not match.',
        ];
    }
}
