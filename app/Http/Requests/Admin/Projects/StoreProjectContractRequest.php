<?php

namespace App\Http\Requests\Admin\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:vendor,venue,speaker,sponsor,agency,service,other'],
            'counterparty_name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
            'task_id' => ['nullable', 'integer'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'terms' => ['nullable', 'string', 'max:10000'],
            'vendor_public_id' => ['nullable', 'uuid'],
            'bill_public_id' => ['nullable', 'uuid'],
        ];
    }
}
