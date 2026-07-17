<?php

namespace App\Forms\Services;

use App\Forms\Enums\FormQuestionType;
use App\Forms\Enums\FormResponseStatus;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormAnswer;
use App\Forms\Models\CustomFormAnswerFile;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Models\CustomFormResponse;
use App\Models\Registration;
use App\Registration\Models\RegistrationDraft;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Persists custom form submissions, including secure upload handling.
 *
 * Hidden answers (per FormVisibilityEvaluator) are ignored. Question key/label/type
 * are snapshotted onto each answer row. Uploads are stored under
 * custom-form-answers/{response public id}/ on the local disk.
 */
class FormResponseService
{
    public const UPLOAD_DISK = 'local';

    public function __construct(
        private FormVisibilityEvaluator $visibility,
        private DynamicFormValidator $validator
    ) {}

    /**
     * Validate and persist a submitted response for the given respondent.
     *
     * @param  array<string, mixed>  $answers
     * @param  array<string, mixed>|null  $metadata
     *
     * @throws ValidationException
     */
    public function submit(
        CustomForm $form,
        Model $respondent,
        array $answers,
        ?array $metadata = null,
        ?CustomFormResponse $existing = null
    ): CustomFormResponse {
        $this->ensureFormReady($form);

        $validated = $this->validator->validate($form, $answers, $existing);
        $visibleQuestions = $this->visibility->visibleQuestions($form, $answers);

        return DB::transaction(function () use ($form, $respondent, $validated, $visibleQuestions, $metadata, $existing) {
            $response = $existing ?? new CustomFormResponse([
                'event_id' => $form->event_id,
                'org_id' => $form->org_id,
                'custom_form_id' => $form->id,
            ]);

            $response->respondent()->associate($respondent);
            $response->status = FormResponseStatus::Submitted;
            $response->submitted_at = now();
            $response->form_version = (int) ($form->settings['version'] ?? $response->form_version ?? 1);
            $response->metadata = $metadata;
            $response->save();

            // Ensure public_id exists before storing files (boot may have just set it).
            $response->refresh();

            $this->replaceAnswers($response, $visibleQuestions, $validated);

            return $response->load(['answers.files']);
        });
    }

    /**
     * Persist answers without marking the response as submitted.
     *
     * @param  array<string, mixed>  $answers
     * @param  array<string, mixed>|null  $metadata
     */
    public function saveDraft(
        CustomForm $form,
        Model $respondent,
        array $answers,
        ?array $metadata = null,
        ?CustomFormResponse $existing = null
    ): CustomFormResponse {
        $this->ensureFormReady($form);

        $visibleQuestions = $this->visibility->visibleQuestions($form, $answers);

        return DB::transaction(function () use ($form, $respondent, $answers, $visibleQuestions, $metadata, $existing) {
            $response = $existing ?? new CustomFormResponse([
                'event_id' => $form->event_id,
                'org_id' => $form->org_id,
                'custom_form_id' => $form->id,
            ]);

            $response->respondent()->associate($respondent);
            $response->status = FormResponseStatus::Draft;
            $response->submitted_at = null;
            $response->form_version = (int) ($form->settings['version'] ?? $response->form_version ?? 1);
            $response->metadata = $metadata;
            $response->save();
            $response->refresh();

            $this->replaceAnswers($response, $visibleQuestions, $answers);

            return $response->load(['answers.files']);
        });
    }

    /**
     * Re-point responses from a completed registration draft to the final registration.
     */
    public function promoteDraftRespondent(RegistrationDraft $draft, Registration $registration): int
    {
        return CustomFormResponse::query()
            ->where('respondent_type', $draft->getMorphClass())
            ->where('respondent_id', $draft->getKey())
            ->update([
                'respondent_type' => $registration->getMorphClass(),
                'respondent_id' => $registration->getKey(),
                'updated_at' => now(),
            ]);
    }

    /**
     * @param  iterable<int, CustomFormQuestion>  $visibleQuestions
     * @param  array<string, mixed>  $answers
     */
    protected function replaceAnswers(CustomFormResponse $response, iterable $visibleQuestions, array $answers): void
    {
        $visibleById = [];
        foreach ($visibleQuestions as $question) {
            $visibleById[(int) $question->id] = $question;
        }

        $existingAnswers = $response->answers()->with('files')->get()->keyBy('custom_form_question_id');

        // Remove answers for questions that are no longer visible.
        foreach ($existingAnswers as $questionId => $existingAnswer) {
            if (! isset($visibleById[(int) $questionId])) {
                $this->deleteAnswerFiles($existingAnswer);
                $existingAnswer->delete();
            }
        }

        foreach ($visibleById as $question) {
            $raw = $answers[$question->key] ?? null;
            $existing = $existingAnswers->get($question->id);

            if ($question->type === FormQuestionType::Upload) {
                $this->persistUploadAnswer($response, $question, $raw, $existing);

                continue;
            }

            $value = $this->normalizeStoredValue($question, $raw);

            if ($existing instanceof CustomFormAnswer) {
                $existing->fill([
                    'question_key' => $question->key,
                    'question_label' => $question->label,
                    'question_type' => $question->type,
                    'value' => $value,
                ])->save();

                continue;
            }

            $response->answers()->create([
                'event_id' => $response->event_id,
                'org_id' => $response->org_id,
                'custom_form_question_id' => $question->id,
                'question_key' => $question->key,
                'question_label' => $question->label,
                'question_type' => $question->type,
                'value' => $value,
            ]);
        }
    }

    protected function persistUploadAnswer(
        CustomFormResponse $response,
        CustomFormQuestion $question,
        mixed $raw,
        ?CustomFormAnswer $existing
    ): void {
        $answer = $existing ?? $response->answers()->make([
            'event_id' => $response->event_id,
            'org_id' => $response->org_id,
            'custom_form_question_id' => $question->id,
        ]);

        $answer->fill([
            'question_key' => $question->key,
            'question_label' => $question->label,
            'question_type' => $question->type,
        ]);

        if (! $raw instanceof UploadedFile) {
            // Keep existing file when no new upload is provided.
            if (! $answer->exists) {
                $answer->value = null;
                $answer->save();
            } else {
                $answer->save();
            }

            return;
        }

        if ($answer->exists) {
            $this->deleteAnswerFiles($answer);
        } else {
            $answer->save();
        }

        $directory = 'custom-form-answers/'.$response->public_id;
        $path = $raw->store($directory, self::UPLOAD_DISK);

        if ($path === false) {
            throw new InvalidArgumentException('Unable to store uploaded file for '.$question->label.'.');
        }

        $answer->value = [
            'original_name' => $raw->getClientOriginalName(),
            'mime' => $raw->getClientMimeType(),
            'size' => $raw->getSize(),
        ];
        $answer->save();

        CustomFormAnswerFile::query()->create([
            'event_id' => $response->event_id,
            'org_id' => $response->org_id,
            'custom_form_answer_id' => $answer->id,
            'disk' => self::UPLOAD_DISK,
            'path' => $path,
            'original_name' => $raw->getClientOriginalName(),
            'mime' => $raw->getClientMimeType(),
            'size' => (int) $raw->getSize(),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function normalizeStoredValue(CustomFormQuestion $question, mixed $raw): ?array
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if ($question->type === FormQuestionType::Checkbox) {
            $values = is_array($raw) ? array_values($raw) : [$raw];

            return ['values' => array_map(fn ($item) => (string) $item, $values)];
        }

        if (is_array($raw)) {
            return ['value' => $raw];
        }

        return ['value' => (string) $raw];
    }

    protected function deleteAnswerFiles(CustomFormAnswer $answer): void
    {
        $files = $answer->relationLoaded('files')
            ? $answer->files
            : $answer->files()->get();

        foreach ($files as $file) {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
        }
    }

    protected function ensureFormReady(CustomForm $form): void
    {
        if (! $form->relationLoaded('questions')) {
            $form->load([
                'questions' => fn ($query) => $query->active()->orderBy('sort_order'),
                'questions.options' => fn ($query) => $query->active()->orderBy('sort_order'),
                'conditions' => fn ($query) => $query->active()->orderBy('sort_order'),
            ]);
        }
    }
}
