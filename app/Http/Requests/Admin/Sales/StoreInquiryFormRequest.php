<?php

namespace App\Http\Requests\Admin\Sales;

use App\Sales\Enums\SalesFieldType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['auto_create_deal' => $this->boolean('auto_create_deal')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('sales_inquiry_forms', 'slug')->where(fn ($q) => $q
                    ->where('event_id', config('event.event_id'))
                    ->where('org_id', config('event.org_id'))),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'heading' => ['nullable', 'string', 'max:255'],
            'intro_text' => ['nullable', 'string', 'max:10000'],
            'submit_button_label' => ['nullable', 'string', 'max:64'],
            'success_message' => ['nullable', 'string', 'max:5000'],
            'sales_pipeline_type_id' => ['nullable', 'exists:sales_pipeline_types,id'],
            'sales_pipeline_id' => ['nullable', 'exists:sales_pipelines,id'],
            'default_stage_id' => ['nullable', 'exists:sales_pipeline_stages,id'],
            'auto_create_deal' => ['boolean'],
            'allowed_domains' => ['nullable', 'string', 'max:2000'],
            'fields' => ['nullable', 'array'],
            'fields.*.label' => ['required_with:fields', 'string', 'max:255'],
            'fields.*.type' => ['required_with:fields', Rule::enum(SalesFieldType::class)],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.map_to_deal_field' => ['nullable', 'string', 'max:64'],
            'fields.*.map_to_contact_field' => ['nullable', 'string', 'max:64'],
        ];
    }
}
