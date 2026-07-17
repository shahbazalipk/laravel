<?php

namespace App\Http\Requests\Admin\Sales;

use App\Sales\Enums\PipelineStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePipelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_pipeline_type_id' => ['required', 'exists:sales_pipeline_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', Rule::enum(PipelineStatus::class)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'currency' => ['nullable', 'string', 'max:10'],
            'revenue_target' => ['nullable', 'numeric', 'min:0'],
            'custom_field_answers' => ['nullable', 'array'],
        ];
    }
}
