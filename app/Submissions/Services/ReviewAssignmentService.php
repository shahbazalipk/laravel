<?php

namespace App\Submissions\Services;

use App\Submissions\Models\Reviewer;
use App\Submissions\Models\Submission;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class ReviewAssignmentService
{
    public function assign(Submission $submission, Reviewer $reviewer, ?string $dueDate = null): void
    {
        if ($submission->is_draft) {
            throw ValidationException::withMessages(['submission' => 'Drafts cannot be assigned for review.']);
        }
        if ((int) $submission->event_id !== (int) $reviewer->event_id || (int) $submission->org_id !== (int) $reviewer->org_id) {
            throw ValidationException::withMessages(['reviewer' => 'Reviewer does not belong to this event.']);
        }
        if ($reviewer->conflicts()->where('submission_id', $submission->getKey())->exists()) {
            throw ValidationException::withMessages(['reviewer' => 'This reviewer has a declared conflict.']);
        }
        if ($reviewer->maximum_capacity && $reviewer->assignments()->whereIn('status', ['assigned', 'accepted', 'in_progress'])->count() >= $reviewer->maximum_capacity) {
            throw ValidationException::withMessages(['reviewer' => 'Reviewer capacity has been reached.']);
        }

        $submission->reviewAssignments()->updateOrCreate(
            ['reviewer_id' => $reviewer->getKey()],
            ['status' => 'assigned', 'due_at' => $dueDate, 'settings' => ['assigned_by' => session('admin_id'), 'assigned_at' => now()->toIso8601String()]],
        );
    }

    /** @return Collection<int, Reviewer> */
    public function recommend(Submission $submission, int $limit = 5): Collection
    {
        return Reviewer::query()
            ->where('status', 'active')
            ->withCount(['assignments as open_assignments_count' => fn ($query) => $query->whereIn('status', ['assigned', 'accepted', 'in_progress'])])
            ->whereDoesntHave('conflicts', fn ($query) => $query->where('submission_id', $submission->getKey()))
            ->get()
            ->sortBy(fn (Reviewer $reviewer) => $reviewer->open_assignments_count)
            ->take($limit)
            ->values();
    }
}
