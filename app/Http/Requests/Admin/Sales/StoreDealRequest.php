<?php

namespace App\Http\Requests\Admin\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_pipeline_id' => ['required', 'exists:sales_pipelines,id'],
            'sales_pipeline_stage_id' => ['nullable', 'exists:sales_pipeline_stages,id'],
            'title' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'lead_source' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string', 'max:10000'],
            'custom_field_answers' => ['nullable', 'array'],
            'contact.name' => ['nullable', 'string', 'max:255'],
            'contact.email' => ['nullable', 'email', 'max:255'],
            'contact.phone' => ['nullable', 'string', 'max:64'],
            'contact.company_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
