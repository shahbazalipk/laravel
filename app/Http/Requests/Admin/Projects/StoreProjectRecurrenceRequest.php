<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRecurrenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'weekdays'])],
            'interval' => ['required', 'integer', 'between:1,365'],
            'weekdays' => ['nullable', 'array', 'required_if:frequency,weekdays'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'max_occurrences' => ['nullable', 'integer', 'between:1,1000'],
        ];
    }
}
