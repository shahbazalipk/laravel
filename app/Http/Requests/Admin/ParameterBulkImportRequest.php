<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ParameterBulkImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $definition = config("parameter_imports.{$this->route('parameter')}");
        $requiresSponsorshipFields = (bool) ($definition['requires_sponsorship_fields'] ?? false);

        return [
            'names' => ['nullable', 'string', 'max:50000'],
            'import_file' => ['nullable', 'file', 'mimes:txt,csv', 'max:2048'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'type' => [$requiresSponsorshipFields ? 'required' : 'nullable', 'string', 'max:255'],
            'sponsorship_label' => [$requiresSponsorshipFields ? 'required' : 'nullable', 'string', 'max:255'],
            'visible_online' => ['nullable', 'boolean'],
            'visible_onsite' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasNames = trim((string) $this->input('names')) !== '';
                $hasFile = $this->hasFile('import_file');

                if (!$hasNames && !$hasFile) {
                    $validator->errors()->add(
                        'names',
                        'Paste a list of names or upload a TXT/CSV file.'
                    );
                }
            },
        ];
    }
}
