<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinanceBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['event', 'department', 'category', 'project', 'team', 'vendor', 'campaign'])],
            'category_id' => ['nullable', 'integer'],
            'department' => ['nullable', 'string', 'max:255'],
            'project_public_id' => ['nullable', 'uuid'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'currency' => ['required', 'string', 'size:3'],
            'planned_income' => ['nullable', 'numeric', 'min:0'],
            'planned_expense' => ['required', 'numeric', 'gt:0'],
            'warning_threshold' => ['required', 'integer', 'between:1,99'],
            'critical_threshold' => ['required', 'integer', 'gt:warning_threshold', 'max:100'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
        ];
    }
}
