<?php

namespace App\Http\Requests\PublicRegistration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCategoryStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = config('event.event_id');

        return [
            'registration_category_id' => [
                'required',
                'integer',
                Rule::exists('registration_categories', 'id')->where(fn ($query) => $query
                    ->where('event_id', $eventId)
                    ->where('is_active', true)
                    ->where('visible', true)),
            ],
            'category_password' => ['nullable', 'string', 'max:255'],
            'membership_id' => ['nullable', 'string', 'max:255'],
            'professional_student_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
