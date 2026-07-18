<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => [
                'required',
                'string',
                'regex:/^[A-Za-z][A-Za-z0-9]{1,9}$/',
                Rule::unique('project_projects')->where(fn ($query) => $query
                    ->where('event_id', config('event.event_id'))
                    ->where('org_id', config('event.org_id'))
                    ->whereNull('deleted_at')),
            ],
            'description' => ['nullable', 'string', 'max:10000'],
            'type' => ['required', Rule::in([
                'event_operations', 'marketing', 'sponsorship', 'content', 'logistics', 'technology', 'other',
            ])],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['required', Rule::in(['planned', 'active', 'on_hold', 'completed', 'cancelled'])],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'required_with:budget_amount', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'tags' => ['nullable', 'string', 'max:2000'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'visibility' => ['required', Rule::in(['members', 'organization'])],
        ];
    }
}
