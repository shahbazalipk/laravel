<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinanceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kind' => ['required', Rule::in(['income', 'expense'])],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'alpha_dash',
                'max:64',
                Rule::unique('finance_categories')->where(fn ($query) => $query
                    ->where('event_id', config('event.event_id'))
                    ->where('org_id', config('event.org_id'))
                    ->where('kind', $this->string('kind')->toString())
                    ->whereNull('deleted_at')),
            ],
            'parent_id' => [
                'nullable',
                Rule::exists('finance_categories', 'id')->where(fn ($query) => $query
                    ->where('event_id', config('event.event_id'))
                    ->where('org_id', config('event.org_id'))
                    ->where('kind', $this->string('kind')->toString())
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'color' => ['nullable', 'string', 'max:32'],
        ];
    }
}
