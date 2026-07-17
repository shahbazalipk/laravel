<?php

namespace App\Http\Requests\Admin;

use App\Models\RegistrationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRegistrationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_status_id' => [
                'required',
                'integer',
                Rule::exists('registration_statuses', 'id')->where(function ($query) {
                    $query->where('event_id', config('event.event_id'))
                        ->where('org_id', config('event.org_id'))
                        ->where('is_active', true);
                }),
            ],
        ];
    }

    public function status(): RegistrationStatus
    {
        return RegistrationStatus::query()
            ->whereKey($this->integer('registration_status_id'))
            ->where('is_active', true)
            ->firstOrFail();
    }
}
