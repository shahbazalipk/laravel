<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectSavedFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_default' => $this->boolean('is_default')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'view' => ['required', 'in:kanban,list,calendar,timeline,my_work'],
            'criteria' => ['nullable', 'array'],
            'visible_columns' => ['nullable', 'array'],
            'visibility' => ['required', 'in:private,shared'],
            'is_default' => ['boolean'],
        ];
    }
}
