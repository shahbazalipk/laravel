<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinanceAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('finance_accounts')->where(fn ($query) => $query
                    ->where('event_id', config('event.event_id'))
                    ->where('org_id', config('event.org_id'))
                    ->where('currency', strtoupper($this->string('currency')->toString()))
                    ->whereNull('deleted_at')),
            ],
            'type' => ['required', Rule::in([
                'bank', 'cash', 'petty_cash', 'payment_gateway', 'credit_card', 'other',
            ])],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'opening_balance' => ['nullable', 'numeric'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_title' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
