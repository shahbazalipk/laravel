<?php

namespace App\Http\Requests\Submissions;

use Illuminate\Foundation\Http\FormRequest;

class SaveSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('submission_portal_user_id');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:500'],
            'answers' => ['nullable', 'array'],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png'],
        ];
    }
}
