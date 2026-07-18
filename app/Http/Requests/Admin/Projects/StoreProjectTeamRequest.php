<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'regex:/^[A-Za-z][A-Za-z0-9-]{1,15}$/',
                Rule::unique('project_teams')->where(fn ($query) => $query
                    ->where('event_id', config('event.event_id'))
                    ->where('org_id', config('event.org_id'))
                    ->whereNull('deleted_at')),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'department' => ['nullable', 'string', 'max:255'],
            'lead_admin_id' => ['nullable', 'integer'],
            'default_role' => ['required', Rule::in(['member', 'coordinator', 'manager'])],
        ];
    }
}
