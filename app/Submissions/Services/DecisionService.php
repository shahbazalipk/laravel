<?php

namespace App\Submissions\Services;

use App\Submissions\Models\Submission;
use App\Submissions\Models\SubmissionActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DecisionService
{
    /** @param array<string, mixed> $data */
    public function decide(Submission $submission, array $data): Submission
    {
        $rules = $submission->type->decision_rules ?? [];
        $completed = $submission->reviews()->whereNotNull('submitted_at')->count();
        if ($completed < ($rules['minimum_reviews'] ?? 0) && blank($data['override_reason'] ?? null)) {
            throw ValidationException::withMessages(['decision' => 'The minimum completed review count has not been reached.']);
        }
        if ($submission->average_score < ($rules['minimum_score'] ?? 0) && blank($data['override_reason'] ?? null)) {
            throw ValidationException::withMessages(['decision' => 'The minimum score has not been reached.']);
        }

        return DB::transaction(function () use ($submission, $data): Submission {
            $submission->decisions()->create([
                'decision' => $data['decision'],
                'status' => 'final',
                'reason' => $data['reason'] ?? null,
                'decided_by' => session('admin_id'),
                'decided_at' => now(),
                'settings' => [
                    'internal_notes' => $data['internal_notes'] ?? null,
                    'public_message' => $data['public_message'] ?? null,
                    'response_deadline' => $data['response_deadline'] ?? null,
                    'override_reason' => $data['override_reason'] ?? null,
                ],
            ]);
            $submission->forceFill([
                'final_decision' => $data['decision'],
                'decision_at' => now(),
                'is_selected' => in_array($data['decision'], ['selected', 'conditionally_selected'], true),
            ])->save();
            SubmissionActivity::record($submission, 'submission.decision_made', ['decision' => $data['decision']]);

            return $submission->refresh();
        });
    }
}
