<?php

namespace App\Http\Requests\Admin\Submissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubmissionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) session('admin_logged_in');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $id = $this->route('submission_type')?->getKey();

        return [
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('submission_types')->ignore($id)->where(
                fn ($query) => $query->where('event_id', config('event.event_id'))->where('org_id', config('event.org_id')),
            )],
            'slug' => ['nullable', 'alpha_dash', 'max:160'],
            'description' => ['nullable', 'string', 'max:10000'],
            'public_title' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:50000'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed', 'archived'])],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after:opens_at'],
            'timezone' => ['nullable', 'timezone'],
            'maximum_submissions_per_applicant' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'allow_drafts' => ['nullable', 'boolean'],
            'allow_editing_after_submission' => ['nullable', 'boolean'],
            'allow_anonymous_review' => ['nullable', 'boolean'],
            'enable_scoring' => ['nullable', 'boolean'],
            'enable_revisions' => ['nullable', 'boolean'],
            'enable_speaker_onboarding' => ['nullable', 'boolean'],
            'number_prefix' => ['nullable', 'alpha_dash', 'max:12'],
            'number_pattern' => ['nullable', 'string', 'max:100'],
            'success_message' => ['nullable', 'string', 'max:5000'],
            'closed_message' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:50000'],
            'privacy_consent' => ['nullable', 'string', 'max:50000'],
        ];
    }
}
