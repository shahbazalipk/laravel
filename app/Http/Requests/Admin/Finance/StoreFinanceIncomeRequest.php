<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinanceIncomeRequest extends FormRequest
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
                    ->where('kind', 'income')
                    ->where('is_active', true)),
            ],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'payer_email' => ['nullable', 'email', 'max:255'],
            'expected_amount' => ['required', 'numeric', 'gt:0'],
            'received_amount' => ['nullable', 'numeric', 'min:0', 'lte:expected_amount'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:10000'],
            'internal_notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
