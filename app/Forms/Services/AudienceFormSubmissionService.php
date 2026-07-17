<?php

namespace App\Forms\Services;

use App\Forms\Enums\FormAudience;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Resolves, validates, and persists the single active custom form for an audience.
 */
class AudienceFormSubmissionService
{
    public function __construct(
        private FormResolver $forms,
        private DynamicFormValidator $validator,
        private FormResponseService $responses,
    ) {}

    /**
     * @return Collection<int, CustomForm>
     */
    public function activeForms(FormAudience $audience): Collection
    {
        return $this->forms->activeForAudience($audience);
    }

    /**
     * @param  Collection<int, CustomForm>|null  $forms
     * @return Collection<int|string, CustomFormResponse>
     */
    public function existingResponses(Model $respondent, ?Collection $forms = null): Collection
    {
        if (! method_exists($respondent, 'customFormResponses')) {
            return new Collection;
        }

        $forms ??= new Collection;
        $query = $respondent->customFormResponses()->with(['answers.files', 'form']);

        if ($forms->isNotEmpty()) {
            $query->whereIn('custom_form_id', $forms->modelKeys());
        }

        return $query->get()->keyBy('custom_form_id');
    }

    /**
     * Validate submitted custom-form answers without persisting.
     *
     * @throws ValidationException
     */
    public function validate(FormAudience $audience, Request $request, ?Model $respondent = null): void
    {
        $forms = $this->activeForms($audience);
        if ($forms->isEmpty()) {
            return;
        }

        $existing = $respondent
            ? $this->existingResponses($respondent, $forms)
            : new Collection;
        $submitted = $this->submittedAnswers($request);
        $errors = [];

        foreach ($forms as $form) {
            $answers = $submitted[$form->public_id] ?? [];
            $existingResponse = $existing->get($form->id);

            try {
                $this->validator->validate(
                    $form,
                    is_array($answers) ? $answers : [],
                    $existingResponse instanceof CustomFormResponse ? $existingResponse : null
                );
            } catch (ValidationException $exception) {
                foreach ($exception->errors() as $key => $messages) {
                    $errors["custom_forms.{$form->public_id}.{$key}"] = $messages;
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate and persist answers for the audience form(s).
     *
     * @throws ValidationException
     */
    public function submit(
        FormAudience $audience,
        Model $respondent,
        Request $request,
        string $source
    ): void {
        $forms = $this->activeForms($audience);
        if ($forms->isEmpty()) {
            return;
        }

        $this->validate($audience, $request, $respondent);

        $existing = $this->existingResponses($respondent, $forms);
        $submitted = $this->submittedAnswers($request);

        foreach ($forms as $form) {
            $answers = $submitted[$form->public_id] ?? [];
            $existingResponse = $existing->get($form->id);

            $this->responses->submit(
                $form,
                $respondent,
                is_array($answers) ? $answers : [],
                ['source' => $source],
                $existingResponse instanceof CustomFormResponse ? $existingResponse : null
            );
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function submittedAnswers(Request $request): array
    {
        $input = $request->input('custom_forms', []);
        $files = $request->file('custom_forms', []) ?: [];

        if (! is_array($input)) {
            $input = [];
        }
        if (! is_array($files)) {
            $files = [];
        }

        /** @var array<string, array<string, mixed>> $merged */
        $merged = array_replace_recursive($input, $files);

        return $merged;
    }
}
