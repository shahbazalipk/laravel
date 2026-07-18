<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinanceReconciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['required', 'integer'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'statement_opening_balance' => ['required', 'numeric'],
            'statement_closing_balance' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
