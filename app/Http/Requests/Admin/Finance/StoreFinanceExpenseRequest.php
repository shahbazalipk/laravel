<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinanceExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenant = fn ($query) => $query
            ->where('event_id', config('event.event_id'))
            ->where('org_id', config('event.org_id'))
            ->whereNull('deleted_at');

        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => [
                'nullable',
                Rule::exists('finance_categories', 'id')->where(fn ($query) => $tenant($query)
                    ->where('kind', 'expense')
                    ->where('is_active', true)),
            ],
            'vendor_id' => [
                'nullable',
                Rule::exists('finance_vendors', 'id')->where(fn ($query) => $tenant($query)
                    ->where('is_active', true)),
            ],
            'expense_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:expense_date'],
            'expected_amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:10000'],
            'internal_notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
