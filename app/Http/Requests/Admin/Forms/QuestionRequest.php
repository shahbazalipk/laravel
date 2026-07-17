<?php

namespace App\Http\Requests\Admin\Forms;

use App\Forms\Enums\FormConditionAction;
use App\Forms\Enums\FormConditionOperator;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $options = array_values(array_filter(
            $this->input('options', []),
            fn ($option) => filled($option['label'] ?? null) || filled($option['value'] ?? null)
        ));

        $this->merge([
            'is_required' => $this->boolean('is_required'),
            'options' => $options,
        ]);
    }

    public function rules(): array
    {
        /** @var CustomForm $form */
        $form = $this->route('custom_form');
        /** @var CustomFormQuestion|null $question */
        $question = $this->route('question');

        return [
            'label' => ['required', 'string', 'max:255'],
            'key' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('custom_form_questions', 'key')
                    ->where('custom_form_id', $form->id)
                    ->when($question, fn ($rule) => $rule->ignore($question)),
            ],
            'type' => ['required', Rule::enum(FormQuestionType::class)],
            'is_required' => ['required', 'boolean'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:2000'],
            'validation' => ['nullable', 'array'],
            'validation.min' => ['nullable', 'integer', 'min:0'],
            'validation.max' => ['nullable', 'integer', 'min:0', 'gte:validation.min'],
            'validation.mimes' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9, ]+$/i'],
            'validation.max_kb' => ['nullable', 'integer', 'min:1', 'max:102400'],
            'options' => ['nullable', 'array', 'max:100'],
            'options.*.label' => ['required_with:options.*.value', 'string', 'max:255'],
            'options.*.value' => ['required_with:options.*.label', 'string', 'max:255', 'distinct'],
            'condition' => ['nullable', 'array'],
            'condition.source_question' => ['nullable', 'uuid'],
            'condition.operator' => ['nullable', Rule::enum(FormConditionOperator::class)],
            'condition.compare_value' => ['nullable', 'string', 'max:1000'],
            'condition.action' => ['nullable', Rule::enum(FormConditionAction::class)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var CustomForm $form */
                $form = $this->route('custom_form');
                /** @var CustomFormQuestion|null $question */
                $question = $this->route('question');
                $type = FormQuestionType::tryFrom((string) $this->input('type'));
                $options = $this->input('options', []);

                if ($type?->hasOptions() && count($options) < 1) {
                    $validator->errors()->add('options', 'This question type requires at least one option.');
                }

                if ($type === FormQuestionType::Upload && blank($this->input('validation.max_kb'))) {
                    $validator->errors()->add('validation.max_kb', 'Maximum file size is required for uploads.');
                }

                $sourcePublicId = $this->input('condition.source_question');
                if (! $sourcePublicId) {
                    return;
                }

                $source = $form->questions()->where('public_id', $sourcePublicId)->first();
                if (! $source || ($question && $source->is($question))) {
                    $validator->errors()->add('condition.source_question', 'Select another question from this form.');
                } elseif ($question && $source->sort_order >= $question->sort_order) {
                    $validator->errors()->add('condition.source_question', 'The source must appear before this question.');
                }

                if (! $this->filled('condition.operator') || ! $this->filled('condition.action')) {
                    $validator->errors()->add('condition.operator', 'Choose an operator and action for the condition.');
                }

                if (in_array($this->input('condition.operator'), ['equals', 'not_equals', 'contains'], true)
                    && ! $this->filled('condition.compare_value')) {
                    $validator->errors()->add('condition.compare_value', 'A comparison value is required.');
                }
            },
        ];
    }
}
