<?php

namespace App\Http\Requests\Admin\Forms;

use App\Forms\Models\CustomForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /** @var CustomForm $form */
        $form = $this->route('custom_form');

        // Audience is locked after create to prevent colliding with another form.
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'audience' => $form->audience->value,
        ]);
    }

    public function rules(): array
    {
        /** @var CustomForm $form */
        $form = $this->route('custom_form');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('custom_forms', 'slug')
                    ->ignore($form)
                    ->where(fn ($query) => $query
                        ->where('event_id', config('event.event_id'))
                        ->where('org_id', config('event.org_id'))),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'audience' => ['required', 'in:'.$form->audience->value],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
