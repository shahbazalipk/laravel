<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectDependencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_enforced' => $this->boolean('is_enforced')]);
    }

    public function rules(): array
    {
        return [
            'depends_on_task_id' => ['required', 'integer'],
            'type' => ['required', Rule::in(['blocks', 'is_blocked_by', 'starts_after', 'finishes_before', 'related_to', 'duplicate_of'])],
            'lag_days' => ['nullable', 'integer', 'between:-365,365'],
            'is_enforced' => ['boolean'],
        ];
    }
}
