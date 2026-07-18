<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'owner_admin_id' => ['nullable', 'integer'],
            'task_id' => ['nullable', 'integer'],
            'resolution' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:open,in_progress,resolved,closed'],
        ];
    }
}
