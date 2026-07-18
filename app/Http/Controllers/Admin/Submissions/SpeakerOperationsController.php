<?php

namespace App\Http\Controllers\Admin\Submissions;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\Speaker;
use App\Submissions\Models\Submission;
use App\Submissions\Models\SubmissionActivity;
use App\Submissions\Services\SpeakerConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SpeakerOperationsController extends Controller
{
    public function convert(Submission $submission, SpeakerConversionService $conversion): RedirectResponse
    {
        $speakers = $conversion->convert($submission);

        return back()->with('success', count($speakers).' speaker profile(s) linked.');
    }

    public function assignSession(Request $request, Speaker $speaker): RedirectResponse
    {
        $data = $request->validate([
            'session_id' => ['required', 'integer'],
            'role' => ['required', 'string', 'max:100'],
            'submission_id' => ['nullable', 'integer'],
            'presentation_order' => ['nullable', 'integer', 'min:1'],
        ]);
        $session = Session::query()->findOrFail($data['session_id']);
        $conflict = $speaker->sessions()
            ->whereKeyNot($session->getKey())
            ->where('start_time', '<', $session->end_time)
            ->where('end_time', '>', $session->start_time)
            ->exists();
        if ($conflict) {
            throw ValidationException::withMessages(['session_id' => 'The speaker already has an overlapping session.']);
        }
        $speaker->sessions()->syncWithoutDetaching([
            $session->getKey() => [
                'role' => $data['role'],
                'submission_id' => $data['submission_id'] ?? null,
                'presentation_order' => $data['presentation_order'] ?? null,
                'status' => 'proposed',
                'event_id' => config('event.event_id'),
                'org_id' => config('event.org_id'),
            ],
        ]);

        return back()->with('success', 'Session assigned.');
    }

    public function commercial(Request $request, Speaker $speaker): RedirectResponse
    {
        $data = $request->validate([
            'engagement_type' => ['nullable', 'string', 'max:100'],
            'speaker_fee' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'details' => ['nullable', 'array'],
        ]);
        $link = $speaker->submissionLinks()->latest()->firstOrFail();
        $link->commercialTerm()->updateOrCreate([], [
            'currency' => $data['currency'] ?? null,
            'fee_amount' => $data['speaker_fee'] ?? null,
            'payment_status' => $data['payment_status'] ?? 'not_applicable',
            'terms' => ['engagement_type' => $data['engagement_type'] ?? null, ...($data['details'] ?? [])],
        ]);
        SubmissionActivity::record($link->submission, 'speaker.commercial_updated', ['speaker_id' => $speaker->getKey()]);

        return back()->with('success', 'Commercial details saved.');
    }

    public function contract(Request $request, Speaker $speaker): RedirectResponse
    {
        $data = $request->validate([
            'expires_at' => ['nullable', 'date', 'after:today'],
            'terms' => ['required', 'string', 'max:50000'],
        ]);
        $link = $speaker->submissionLinks()->latest()->firstOrFail();
        $link->contracts()->create([
            'status' => 'sent',
            'sent_at' => now(),
            'expires_at' => $data['expires_at'] ?? null,
            'terms' => ['body' => $data['terms'], 'version' => $link->contracts()->count() + 1],
        ]);

        return back()->with('success', 'Contract version sent to the speaker portal.');
    }

    public function travel(Request $request, Speaker $speaker): RedirectResponse
    {
        $data = $request->validate([
            'travel_required' => ['nullable', 'boolean'],
            'departure_country' => ['nullable', 'string', 'max:100'],
            'departure_city' => ['nullable', 'string', 'max:100'],
            'passport_number' => ['nullable', 'string', 'max:100'],
            'passport_expiry' => ['nullable', 'date'],
            'visa_required' => ['nullable', 'boolean'],
            'details' => ['nullable', 'array'],
        ]);
        $link = $speaker->submissionLinks()->latest()->firstOrFail();
        $link->travel()->updateOrCreate([], [
            'status' => ($data['travel_required'] ?? false) ? 'required' : 'not_required',
            'itinerary' => [
                'departure_country' => $data['departure_country'] ?? null,
                'departure_city' => $data['departure_city'] ?? null,
            ],
            'preferences' => $data['details'] ?? [],
            'documents' => [
                'passport_number' => $data['passport_number'] ?? null,
                'passport_expiry' => $data['passport_expiry'] ?? null,
                'visa_required' => $data['visa_required'] ?? false,
            ],
        ]);
        SubmissionActivity::record($link->submission, 'speaker.sensitive_travel_updated', ['speaker_id' => $speaker->getKey()]);

        return back()->with('success', 'Travel details saved and access logged.');
    }
}
