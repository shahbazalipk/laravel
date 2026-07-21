<?php

namespace App\Http\Requests\PublicRegistration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SinglePageRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $eventId = config('event.event_id');

        return [
            'email' => ['required', 'email', 'max:255'],
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'industry_id' => [
                'required',
                'integer',
                Rule::exists('industries', 'id')->where(fn ($query) => $query
                    ->where('event_id', $eventId)
                    ->where('is_active', true)),
            ],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'profile_picture_data' => ['nullable', 'string'],
            'terms_accepted' => ['required', 'accepted'],
        ];
    }
}
