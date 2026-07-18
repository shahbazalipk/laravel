<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Submissions\Models\SpeakerContract;
use App\Submissions\Models\SpeakerSubmissionLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpeakerPortalController extends Controller
{
    public function show(SpeakerSubmissionLink $link): View
    {
        $this->assertOwner($link);
        $link->load(['speaker', 'submission.type', 'onboarding.checklistProgress.checklist', 'contracts', 'presentations']);

        return view('submissions.speaker.onboarding', compact('link'));
    }

    public function update(Request $request, SpeakerSubmissionLink $link): RedirectResponse
    {
        $this->assertOwner($link);
        $data = $request->validate([
            'bio' => ['nullable', 'string', 'max:10000'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'dietary_requirements' => ['nullable', 'string', 'max:2000'],
            'accessibility_requirements' => ['nullable', 'string', 'max:2000'],
            'recording_consent' => ['nullable', 'boolean'],
        ]);
        $link->speaker->update([
            'bio' => $data['bio'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'company' => $data['company'] ?? null,
            'profile' => [
                ...($link->speaker->profile ?? []),
                'dietary_requirements' => $data['dietary_requirements'] ?? null,
                'accessibility_requirements' => $data['accessibility_requirements'] ?? null,
                'recording_consent' => (bool) ($data['recording_consent'] ?? false),
            ],
            'onboarding_status' => 'submitted',
        ]);
        $link->onboarding()->updateOrCreate([], [
            'status' => 'submitted',
            'completion_percent' => 75,
            'profile_data' => $data,
            'started_at' => now(),
        ]);

        return back()->with('success', 'Onboarding details submitted.');
    }

    public function acceptContract(Request $request, SpeakerContract $contract): RedirectResponse
    {
        $this->assertOwner($contract->link);
        $request->validate(['accept' => ['accepted']]);
        $contract->update(['status' => 'accepted', 'signed_at' => now()]);

        return back()->with('success', 'Contract accepted.');
    }

    public function presentation(Request $request, SpeakerSubmissionLink $link): RedirectResponse
    {
        $this->assertOwner($link);
        $data = $request->validate(['presentation' => ['required', 'file', 'max:51200', 'mimes:ppt,pptx,pdf,mp4'], 'title' => ['nullable', 'string', 'max:255']]);
        $file = $data['presentation'];
        $path = $file->store("speaker-presentations/{$link->public_id}", 'local');
        $link->presentations()->create([
            'status' => 'uploaded',
            'title' => $data['title'] ?? $file->getClientOriginalName(),
            'disk' => 'local',
            'path' => $path,
            'version' => $link->presentations()->count() + 1,
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Presentation uploaded.');
    }

    private function assertOwner(SpeakerSubmissionLink $link): void
    {
        abort_unless((int) $link->submission->portal_user_id === (int) session('submission_portal_user_id'), 404);
    }
}
