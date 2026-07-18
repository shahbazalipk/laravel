<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinanceAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([
                'bank_fee', 'currency_difference', 'rounding_difference', 'missing_transaction',
                'duplicate_transaction', 'incorrect_amount', 'reversed_transaction',
            ])],
            'direction' => ['required', Rule::in(['incoming', 'outgoing'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:5000'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
