<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;

class LinkProjectFinanceResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_type' => ['required', 'in:expense,budget'],
            'resource_public_id' => ['required', 'uuid'],
            'task_id' => ['nullable', 'integer'],
        ];
    }
}
