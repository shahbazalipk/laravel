<?php

namespace App\Http\Requests\Admin\Sales;

use App\Sales\Enums\SalesFieldType;
use App\Sales\Enums\StageCategory;
use App\Sales\Models\PipelineType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePipelineTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        /** @var PipelineType|null $type */
        $type = $this->route('pipeline_type');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('sales_pipeline_types', 'slug')
                    ->where(fn ($q) => $q
                        ->where('event_id', config('event.event_id'))
                        ->where('org_id', config('event.org_id')))
                    ->ignore($type?->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'color' => ['nullable', 'string', 'max:32'],
            'is_active' => ['boolean'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.name' => ['required', 'string', 'max:255'],
            'stages.*.slug' => ['nullable', 'string', 'max:255'],
            'stages.*.category' => ['required', Rule::enum(StageCategory::class)],
            'stages.*.probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'stages.*.is_default' => ['nullable', 'boolean'],
            'stages.*.public_id' => ['nullable', 'string'],
            'pipeline_fields' => ['nullable', 'array'],
            'pipeline_fields.*.label' => ['required_with:pipeline_fields', 'string', 'max:255'],
            'pipeline_fields.*.type' => ['required_with:pipeline_fields', Rule::enum(SalesFieldType::class)],
            'deal_fields' => ['nullable', 'array'],
            'deal_fields.*.label' => ['required_with:deal_fields', 'string', 'max:255'],
            'deal_fields.*.type' => ['required_with:deal_fields', Rule::enum(SalesFieldType::class)],
        ];
    }
}
