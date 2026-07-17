<?php

namespace App\Forms\Services;

use App\Forms\Enums\FormQuestionType;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Models\CustomFormResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as LaravelValidator;

/**
 * Builds and executes Laravel validation rules for a custom form.
 *
 * Only visible questions are validated. Upload defaults are conservative:
 * max 5MB and pdf/jpg/jpeg/png unless overridden in question validation settings.
 */
class DynamicFormValidator
{
    public const DEFAULT_UPLOAD_MAX_KB = 5120;

    /** @var list<string> */
    public const DEFAULT_UPLOAD_MIMES = ['pdf', 'jpg', 'jpeg', 'png'];

    public function __construct(
        private FormVisibilityEvaluator $visibility
    ) {}

    /**
     * @param  array<string, mixed>  $answers
     * @return array<string, list<mixed>>
     */
    public function buildRules(
        CustomForm $form,
        array $answers,
        ?CustomFormResponse $existing = null
    ): array {
        $rules = [];

        foreach ($this->visibility->visibleQuestions($form, $answers) as $question) {
            $rules[$question->key] = $this->rulesForQuestion(
                $question,
                $this->hasExistingUpload($existing, $question)
            );
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return array<string, string>
     */
    public function buildMessages(CustomForm $form, array $answers): array
    {
        $messages = [];

        foreach ($this->visibility->visibleQuestions($form, $answers) as $question) {
            $messages[$question->key.'.required'] = "{$question->label} is required.";
            $messages[$question->key.'.in'] = "{$question->label} has an invalid selection.";
            $messages[$question->key.'.mimes'] = "{$question->label} must be a file of type: ".$this->allowedMimesLabel($question).'.';
            $messages[$question->key.'.max'] = $question->type === FormQuestionType::Upload
                ? "{$question->label} may not be larger than ".$this->maxUploadKb($question).' kilobytes.'
                : "{$question->label} is too long.";
        }

        return $messages;
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(
        CustomForm $form,
        array $answers,
        ?CustomFormResponse $existing = null
    ): array {
        return $this->make($form, $answers, $existing)->validate();
    }

    /**
     * @param  array<string, mixed>  $answers
     */
    public function make(
        CustomForm $form,
        array $answers,
        ?CustomFormResponse $existing = null
    ): LaravelValidator {
        return Validator::make(
            $answers,
            $this->buildRules($form, $answers, $existing),
            $this->buildMessages($form, $answers)
        );
    }

    /**
     * @return list<mixed>
     */
    public function rulesForQuestion(
        CustomFormQuestion $question,
        bool $hasExistingUpload = false
    ): array {
        $rules = [];
        $rules[] = $question->is_required && ! $hasExistingUpload ? 'required' : 'nullable';

        $validation = $question->validation ?? [];

        return match ($question->type) {
            FormQuestionType::Text => $this->appendTextRules($rules, $validation, maxDefault: 255),
            FormQuestionType::Textarea => $this->appendTextRules($rules, $validation, maxDefault: 5000),
            FormQuestionType::Radio, FormQuestionType::Select => $this->appendSingleOptionRules($rules, $question),
            FormQuestionType::Checkbox => $this->appendMultiOptionRules($rules, $question),
            FormQuestionType::Upload => $this->appendUploadRules($rules, $question),
        };
    }

    /**
     * @param  list<mixed>  $rules
     * @param  array<string, mixed>  $validation
     * @return list<mixed>
     */
    protected function appendTextRules(array $rules, array $validation, int $maxDefault): array
    {
        $rules[] = 'string';

        $min = isset($validation['min']) ? (int) $validation['min'] : null;
        $max = isset($validation['max'])
            ? (int) $validation['max']
            : (isset($validation['max_length']) ? (int) $validation['max_length'] : $maxDefault);

        if ($min !== null) {
            $rules[] = 'min:'.$min;
        }

        $rules[] = 'max:'.$max;

        if (! empty($validation['regex']) && is_string($validation['regex'])) {
            $rules[] = 'regex:'.$validation['regex'];
        }

        return $rules;
    }

    /**
     * @param  list<mixed>  $rules
     * @return list<mixed>
     */
    protected function appendSingleOptionRules(array $rules, CustomFormQuestion $question): array
    {
        $optionValues = $question->optionValues();

        if ($optionValues !== []) {
            $rules[] = 'in:'.implode(',', $optionValues);
        }

        return $rules;
    }

    /**
     * @param  list<mixed>  $rules
     * @return list<mixed>
     */
    protected function appendMultiOptionRules(array $rules, CustomFormQuestion $question): array
    {
        $rules[] = 'array';

        if ($question->is_required) {
            $rules[] = 'min:1';
        }

        $optionValues = $question->optionValues();
        if ($optionValues === []) {
            return $rules;
        }

        $rules[] = function (string $attribute, mixed $value, \Closure $fail) use ($optionValues, $question): void {
            if (! is_array($value)) {
                return;
            }

            foreach ($value as $item) {
                if (! in_array((string) $item, $optionValues, true)) {
                    $fail("{$question->label} has an invalid selection.");

                    return;
                }
            }
        };

        return $rules;
    }

    /**
     * @param  list<mixed>  $rules
     * @return list<mixed>
     */
    protected function appendUploadRules(array $rules, CustomFormQuestion $question): array
    {
        $rules[] = 'file';
        $rules[] = 'mimes:'.implode(',', $this->allowedMimes($question));
        $rules[] = 'max:'.$this->maxUploadKb($question);

        return $rules;
    }

    /**
     * @return list<string>
     */
    public function allowedMimes(CustomFormQuestion $question): array
    {
        $validation = $question->validation ?? [];
        $settings = $question->settings ?? [];

        $configured = $validation['mimes'] ?? $settings['mimes'] ?? $settings['accept'] ?? null;

        if (is_string($configured)) {
            $configured = preg_split('/[,\s]+/', $configured, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (! is_array($configured) || $configured === []) {
            return self::DEFAULT_UPLOAD_MIMES;
        }

        return array_values(array_unique(array_map(
            fn ($mime) => strtolower(ltrim((string) $mime, '.')),
            $configured
        )));
    }

    public function maxUploadKb(CustomFormQuestion $question): int
    {
        $validation = $question->validation ?? [];
        $settings = $question->settings ?? [];

        $max = $validation['max']
            ?? $validation['max_kb']
            ?? $settings['max_kb']
            ?? self::DEFAULT_UPLOAD_MAX_KB;

        return max(1, (int) $max);
    }

    protected function allowedMimesLabel(CustomFormQuestion $question): string
    {
        return implode(', ', $this->allowedMimes($question));
    }

    protected function hasExistingUpload(
        ?CustomFormResponse $existing,
        CustomFormQuestion $question
    ): bool {
        if (
            ! $existing
            || $question->type !== FormQuestionType::Upload
        ) {
            return false;
        }

        $existing->loadMissing('answers.files');
        $answer = $existing->answers->firstWhere('custom_form_question_id', $question->id);

        return $answer !== null && $answer->files->isNotEmpty();
    }
}
