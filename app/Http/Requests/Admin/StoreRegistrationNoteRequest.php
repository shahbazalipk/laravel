<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v): void {
            if (blank($this->input('body')) && ! $this->hasFile('image')) {
                $v->errors()->add('body', 'A note must have text or an image.');
            }
        });
    }
}
