<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
