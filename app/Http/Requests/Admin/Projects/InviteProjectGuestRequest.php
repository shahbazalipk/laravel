<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;

class InviteProjectGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'type' => ['required', 'in:vendor,agency,contractor,volunteer,client,reviewer'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'task_id' => ['nullable', 'integer'],
            'role' => ['required', 'in:viewer,contributor,reviewer'],
            'invitation_days' => ['nullable', 'integer', 'between:1,30'],
            'access_expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }
}
