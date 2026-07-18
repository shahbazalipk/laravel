<?php

namespace App\Submissions\Services;

use App\Submissions\Models\PortalUser;
use App\Submissions\Models\Submission;
use App\Submissions\Models\SubmissionActivity;
use App\Submissions\Models\SubmissionType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SubmissionService
{
    public function __construct(
        private SubmissionNumberService $numbers,
        private ConditionalRuleEvaluator $conditions,
    ) {}

    /** @param array<string, mixed> $data */
    public function createDraft(SubmissionType $type, PortalUser $applicant, array $data = []): Submission
    {
        $this->assertOpen($type, false);
        $limit = $type->maximum_submissions_per_applicant;
        if ($limit && $type->submissions()->where('portal_user_id', $applicant->getKey())->count() >= $limit) {
            throw ValidationException::withMessages(['submission' => 'You have reached the submission limit.']);
        }

        return DB::transaction(function () use ($type, $applicant, $data): Submission {
            $submission = $type->submissions()->create([
                'portal_user_id' => $applicant->getKey(),
                'current_stage_id' => $type->default_stage_id,
                'title' => $data['title'] ?? 'Untitled submission',
                'status' => 'draft',
                'is_draft' => true,
                'settings' => ['form_version' => $type->published_form_version, ...($data['metadata'] ?? [])],
            ]);
            SubmissionActivity::record($submission, 'submission.created');

            return $submission;
        });
    }

    /** @param array<string, mixed> $answers */
    public function saveAnswers(Submission $submission, array $answers): Submission
    {
        if (! $submission->is_draft && ! $submission->type->allow_editing_after_submission) {
            throw ValidationException::withMessages(['submission' => 'This submission can no longer be edited.']);
        }

        $questions = $submission->type->questions()->with('conditionalRules.sourceQuestion')->get();
        $rules = $questions->flatMap(fn ($question) => $question->conditionalRules->map(fn ($rule) => [
            'source' => $rule->sourceQuestion?->key,
            'operator' => $rule->operator,
            'value' => $rule->compare_value,
            'action' => $rule->action,
            ...($rule->settings ?? []),
            'target' => $question->key,
        ]))->all();
        $state = $this->conditions->evaluate($rules, $answers);

        return DB::transaction(function () use ($submission, $answers, $questions, $state): Submission {
            foreach ($questions as $question) {
                if (! array_key_exists($question->key, $answers)) {
                    if ($question->is_required || ($state[$question->key]['required'] ?? false)) {
                        throw ValidationException::withMessages([$question->key => "{$question->label} is required."]);
                    }

                    continue;
                }
                $value = $answers[$question->key];
                $submission->answers()->updateOrCreate(
                    ['question_id' => $question->getKey()],
                    [
                        'question_key' => $question->key,
                        'question_label' => $question->label,
                        'question_type' => $question->type->value ?? $question->type,
                        'answer_text' => is_scalar($value) ? (string) $value : null,
                        'answer_json' => is_array($value) ? $value : null,
                        'normalized_value' => is_scalar($value) ? mb_strtolower(trim((string) $value)) : null,
                    ],
                );
            }
            SubmissionActivity::record($submission, 'submission.answers_saved');

            return $submission->refresh();
        });
    }

    public function storeFile(Submission $submission, UploadedFile $file, string $category = 'supporting'): void
    {
        $path = $file->store("submission-files/{$submission->public_id}", 'local');
        $submission->files()->create([
            'original_name' => $file->getClientOriginalName(),
            'disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'category' => $category,
        ]);
    }

    public function submit(Submission $submission): Submission
    {
        $this->assertOpen($submission->type, true);
        $missing = $submission->type->questions()->where('is_required', true)
            ->whereDoesntHave('answers', fn ($query) => $query->where('submission_id', $submission->getKey()))
            ->pluck('label');
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages(['answers' => 'Complete required fields: '.$missing->join(', ')]);
        }

        return DB::transaction(function () use ($submission): Submission {
            $submission->forceFill([
                'reference' => $submission->reference_number ?: $this->numbers->next($submission->type),
                'is_draft' => false,
                'status' => 'submitted',
                'current_stage_id' => $submission->type->workflowStages()->where('category', 'submitted')->value('id') ?: $submission->current_stage_id,
                'submitted_at' => now(),
            ])->save();
            SubmissionActivity::record($submission, 'submission.submitted');

            return $submission->refresh();
        });
    }

    private function assertOpen(SubmissionType $type, bool $forSubmit): void
    {
        $now = now($type->timezone ?: config('app.timezone'));
        if ($type->status !== 'active'
            || ($type->opens_at && $now->lt($type->opens_at))
            || ($type->closes_at && $now->gt($type->closes_at))) {
            throw ValidationException::withMessages(['submission' => $forSubmit ? 'Submissions are closed.' : 'This form is not open.']);
        }
    }
}
