<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_admin_user_id' => ['required', 'integer'],
            'role' => ['required', Rule::in(['manager', 'coordinator', 'member', 'viewer'])],
            'can_view_financials' => ['nullable', 'boolean'],
        ];
    }
}
