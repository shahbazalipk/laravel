<?php

namespace App\Http\Requests\PublicRegistration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveInformationStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = config('event.event_id');

        return [
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
            'profile_picture_data' => ['nullable', 'string', 'max:1500000'],
        ];
    }
}
