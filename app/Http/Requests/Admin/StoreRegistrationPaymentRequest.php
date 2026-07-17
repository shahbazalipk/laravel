<?php

namespace App\Http\Requests\Admin;

use App\Payments\Enums\PaymentEntryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'currency' => ['nullable', 'string', 'max:10'],
            'method' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in([
                PaymentEntryStatus::Succeeded->value,
                PaymentEntryStatus::Failed->value,
            ])],
        ];
    }
}
