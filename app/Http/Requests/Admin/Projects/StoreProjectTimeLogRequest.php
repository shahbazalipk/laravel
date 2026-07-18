<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectTimeLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_billable' => $this->boolean('is_billable')]);
    }

    public function rules(): array
    {
        return [
            'logged_on' => ['required', 'date', 'before_or_equal:today'],
            'duration_minutes' => ['required', 'integer', 'between:1,1440'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_billable' => ['boolean'],
        ];
    }
}
