<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Submissions\Models\PortalUser;
use App\Submissions\Models\ReviewAssignment;
use App\Submissions\Services\ReviewScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewerPortalController extends Controller
{
    public function dashboard(): View
    {
        $reviewer = $this->reviewer();
        $assignments = $reviewer->assignments()->with(['submission.type', 'submission.currentStage'])->latest()->paginate(20);

        return view('submissions.reviewer.dashboard', compact('reviewer', 'assignments'));
    }

    public function show(ReviewAssignment $assignment): View
    {
        $this->assertOwner($assignment);
        $assignment->load(['submission.type.questions', 'submission.type.scorecards.criteria', 'submission.answers.question', 'review.answers']);
        $visibleAnswers = $assignment->submission->answers->filter(function ($answer) use ($assignment): bool {
            if (! $assignment->submission->type->allow_anonymous_review) {
                return (bool) ($answer->question->allow_reviewer_access ?? true);
            }

            return (bool) ($answer->question->allow_reviewer_access ?? true)
                && ! (bool) ($answer->question->hidden_during_anonymous_review ?? false);
        });

        return view('submissions.reviewer.review', compact('assignment', 'visibleAnswers'));
    }

    public function conflict(Request $request, ReviewAssignment $assignment): RedirectResponse
    {
        $this->assertOwner($assignment);
        $data = $request->validate(['reason' => ['required', 'string', 'max:5000']]);
        DB::transaction(function () use ($assignment, $data): void {
            $assignment->reviewer->conflicts()->create([
                'submission_id' => $assignment->submission_id,
                'type' => 'declared',
                'reason' => $data['reason'],
            ]);
            $assignment->update(['status' => 'conflict', 'settings' => ['conflict_reason' => $data['reason']]]);
        });

        return redirect()->route('reviewer.dashboard')->with('success', 'Conflict declared. The organizer will reassign this review.');
    }

    public function save(Request $request, ReviewAssignment $assignment, ReviewScoringService $scoring): RedirectResponse
    {
        $this->assertOwner($assignment);
        abort_if($assignment->status === 'conflict', 403);
        abort_if($assignment->review?->submitted_at, 403, 'Final reviews are locked.');
        $data = $request->validate([
            'scores' => ['required', 'array'],
            'recommendation' => ['nullable', 'string', 'max:100'],
            'comments' => ['nullable', 'string', 'max:10000'],
            'submit' => ['nullable', 'boolean'],
        ]);
        $scorecard = $assignment->submission->type->scorecards()->where('status', 'active')->with('criteria')->firstOrFail();
        $criteria = $scorecard->criteria->map(fn ($criterion) => [
            'id' => $criterion->getKey(),
            'min' => $criterion->min_score,
            'max' => $criterion->max_score,
            'weight' => $criterion->weight,
        ])->all();
        $totals = $scoring->calculate($criteria, $data['scores']);

        DB::transaction(function () use ($assignment, $scorecard, $totals, $data): void {
            $review = $assignment->review()->updateOrCreate([], [
                'status' => ($data['submit'] ?? false) ? 'submitted' : 'draft',
                'total_score' => $totals['normalized'],
                'recommendation' => $data['recommendation'] ?? null,
                'summary' => $data['comments'] ?? null,
                'settings' => [
                    'scorecard_id' => $scorecard->getKey(),
                    'scorecard_version' => $scorecard->settings['version'] ?? 1,
                    'raw_score' => $totals['raw'],
                    'weighted_score' => $totals['weighted'],
                ],
                'submitted_at' => ($data['submit'] ?? false) ? now() : null,
            ]);
            foreach ($totals['answers'] as $answer) {
                $review->answers()->updateOrCreate(['criterion_id' => $answer['criterion_id']], $answer);
            }
            $assignment->update(['status' => ($data['submit'] ?? false) ? 'completed' : 'in_progress']);
        });

        return back()->with('success', ($data['submit'] ?? false) ? 'Review submitted.' : 'Review draft saved.');
    }

    private function reviewer()
    {
        $user = PortalUser::query()->findOrFail(session('submission_portal_user_id'));

        return $user->reviewer()->firstOrFail();
    }

    private function assertOwner(ReviewAssignment $assignment): void
    {
        abort_unless((int) $assignment->reviewer_id === (int) $this->reviewer()->getKey(), 404);
    }
}
