<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submissions\SaveSubmissionRequest;
use App\Submissions\Models\PortalUser;
use App\Submissions\Models\Submission;
use App\Submissions\Models\SubmissionType;
use App\Submissions\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicSubmissionController extends Controller
{
    public function __construct(private SubmissionService $submissions) {}

    public function landing(string $eventSlug, string $typeSlug): View
    {
        $type = SubmissionType::query()->where('slug', $typeSlug)->firstOrFail();

        return view('submissions.public.landing', compact('type', 'eventSlug'));
    }

    public function start(string $eventSlug, SubmissionType $submissionType): RedirectResponse
    {
        $user = PortalUser::query()->findOrFail(session('submission_portal_user_id'));
        $submission = $this->submissions->createDraft($submissionType, $user);

        return redirect()->route('submissions.public.edit', compact('eventSlug', 'submission'));
    }

    public function edit(string $eventSlug, Submission $submission): View
    {
        $this->assertOwner($submission);
        $submission->load(['type.sections.questions.options', 'answers', 'people', 'files']);

        return view('submissions.public.form', compact('submission', 'eventSlug'));
    }

    public function update(SaveSubmissionRequest $request, string $eventSlug, Submission $submission): RedirectResponse
    {
        $this->assertOwner($submission);
        $data = $request->validated();
        if (filled($data['title'] ?? null)) {
            $submission->update(['title' => $data['title']]);
        }
        $this->submissions->saveAnswers($submission, $data['answers'] ?? []);
        foreach ($request->file('files', []) as $file) {
            $this->submissions->storeFile($submission, $file);
        }

        return back()->with('success', 'Draft saved.');
    }

    public function submit(string $eventSlug, Submission $submission): RedirectResponse
    {
        $this->assertOwner($submission);
        $this->submissions->submit($submission);

        return redirect()->route('submissions.portal.dashboard')->with('success', 'Submission completed.');
    }

    public function withdraw(string $eventSlug, Submission $submission): RedirectResponse
    {
        $this->assertOwner($submission);
        $submission->update(['is_withdrawn' => true, 'status' => 'withdrawn', 'withdrawn_at' => now()]);

        return back()->with('success', 'Submission withdrawn.');
    }

    private function assertOwner(Submission $submission): void
    {
        abort_unless((int) $submission->portal_user_id === (int) session('submission_portal_user_id'), 404);
    }
}
