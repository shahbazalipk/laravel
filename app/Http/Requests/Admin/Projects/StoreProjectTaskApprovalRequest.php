<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectTaskApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', 'in:any,all,sequential'],
            'approver_admin_ids' => ['required', 'array', 'min:1'],
            'approver_admin_ids.*' => ['integer', 'distinct'],
            'instructions' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
