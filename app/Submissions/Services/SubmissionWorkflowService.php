<?php

namespace App\Submissions\Services;

use App\Submissions\Models\Submission;
use App\Submissions\Models\SubmissionActivity;
use App\Submissions\Models\WorkflowStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SubmissionWorkflowService
{
    public function move(Submission $submission, WorkflowStage $target, ?string $note = null, ?string $overrideReason = null): Submission
    {
        $this->assertSameTenant($submission, $target);
        $transition = $submission->currentStage?->outgoingTransitions()
            ->where('to_stage_id', $target->getKey())
            ->first();

        if (! $transition && blank($overrideReason)) {
            throw ValidationException::withMessages(['stage' => 'This stage transition is not allowed.']);
        }

        $requirements = $transition?->conditions ?? [];
        if (($requirements['note_required'] ?? false) && blank($note)) {
            throw ValidationException::withMessages(['note' => 'A note is required for this transition.']);
        }
        if (($requirements['completed_reviews'] ?? 0) > $submission->reviews()->whereNotNull('submitted_at')->count()) {
            throw ValidationException::withMessages(['stage' => 'The required reviews are not complete.']);
        }
        if (isset($requirements['minimum_score']) && $submission->average_score < $requirements['minimum_score']) {
            throw ValidationException::withMessages(['stage' => 'The minimum review score has not been reached.']);
        }

        return DB::transaction(function () use ($submission, $target, $note, $overrideReason): Submission {
            $from = $submission->current_stage_id;
            $submission->forceFill([
                'current_stage_id' => $target->getKey(),
                'status' => match ($target->category->value) {
                    'review', 'screening' => 'under_review',
                    'revision' => 'revision_requested',
                    'accepted' => 'accepted',
                    default => $target->category->value,
                },
            ])->save();
            $submission->stageHistory()->create([
                'from_stage_id' => $from,
                'to_stage_id' => $target->getKey(),
                'actor_type' => 'admin',
                'actor_id' => session('admin_id'),
                'reason' => $note,
                'metadata' => ['override_reason' => $overrideReason],
            ]);
            SubmissionActivity::record($submission, 'submission.stage_changed', [
                'from_stage_id' => $from,
                'to_stage_id' => $target->getKey(),
                'override_reason' => $overrideReason,
            ]);

            return $submission->refresh();
        });
    }

    private function assertSameTenant(Submission $submission, WorkflowStage $stage): void
    {
        if ((int) $submission->event_id !== (int) $stage->event_id || (int) $submission->org_id !== (int) $stage->org_id) {
            throw ValidationException::withMessages(['stage' => 'The selected stage does not belong to this event.']);
        }
    }
}
