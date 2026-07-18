<?php

namespace App\Http\Controllers\Admin\Submissions;

use App\Http\Controllers\Controller;
use App\Submissions\Models\Submission;
use App\Submissions\Services\DecisionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DecisionController extends Controller
{
    public function store(Request $request, Submission $submission, DecisionService $decisions): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:selected,conditionally_selected,waitlisted,revision_required,rejected,disqualified,withdrawn'],
            'reason' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:10000'],
            'public_message' => ['nullable', 'string', 'max:10000'],
            'response_deadline' => ['nullable', 'date', 'after:today'],
            'override_reason' => ['nullable', 'string', 'max:5000'],
        ]);
        $decisions->decide($submission, $data);

        return back()->with('success', 'Decision recorded.');
    }

    public function revision(Request $request, Submission $submission): RedirectResponse
    {
        $data = $request->validate([
            'instructions' => ['required', 'string', 'max:10000'],
            'deadline' => ['required', 'date', 'after:today'],
            'question_ids' => ['nullable', 'array'],
            'question_ids.*' => ['integer'],
        ]);
        $submission->revisions()->create([
            'revision_number' => $submission->revisions()->count() + 1,
            'instructions' => $data['instructions'],
            'due_at' => $data['deadline'],
            'required_question_ids' => $data['question_ids'] ?? [],
            'status' => 'requested',
            'answer_snapshot' => $submission->answers()->get()->toArray(),
        ]);

        return back()->with('success', 'Revision requested.');
    }
}
