<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_admin_user_id' => ['required', 'integer'],
            'role' => ['required', Rule::in(['lead', 'manager', 'coordinator', 'member', 'viewer'])],
        ];
    }
}
