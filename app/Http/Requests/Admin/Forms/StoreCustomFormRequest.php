<?php

namespace App\Http\Requests\Admin\Forms;

use App\Forms\Enums\FormAudience;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('custom_forms', 'slug')->where(fn ($query) => $query
                    ->where('event_id', config('event.event_id'))
                    ->where('org_id', config('event.org_id'))),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'audience' => [
                'required',
                Rule::enum(FormAudience::class),
                Rule::unique('custom_forms', 'audience_unique')->where(fn ($query) => $query
                    ->where('event_id', config('event.event_id'))
                    ->where('org_id', config('event.org_id'))
                    ->whereNull('deleted_at')),
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        $audience = FormAudience::tryFrom((string) $this->input('audience'));
        $label = $audience?->label() ?? 'that';

        return [
            'audience.unique' => "A {$label} form already exists for this event. Open the existing form to edit it, or delete it before creating another.",
        ];
    }
}
