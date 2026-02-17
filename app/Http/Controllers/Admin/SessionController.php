<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\Agenda;
use App\Models\Track;
use App\Models\Location;
use App\Models\Speaker;
use App\Services\SessionService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    protected SessionService $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function index()
    {
        $sessions = $this->sessionService->getAllSessions();
        return view('admin.sessions.index', compact('sessions'));
    }

    public function create()
    {
        $agendas = Agenda::orderBy('start_date', 'desc')->get();
        $tracks = Track::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $speakers = Speaker::orderBy('full_name')->get();
        
        return view('admin.sessions.create', compact('agendas', 'tracks', 'locations', 'speakers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:talk,panel,workshop,break,networking,keynote',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'agenda_id' => 'required|exists:agendas,id',
            'track_id' => 'nullable|exists:tracks,id',
            'location_id' => 'required|exists:locations,id',
            'max_attendees' => 'nullable|integer|min:1',
            'speaker_ids' => 'nullable|array',
            'speaker_ids.*' => 'exists:speakers,id',
        ]);

        try {
            $speakerIds = $validated['speaker_ids'] ?? [];
            unset($validated['speaker_ids']);

            $session = $this->sessionService->createSession($validated);

            // Attach speakers if provided and session allows them
            if (!empty($speakerIds) && $session->allowsSpeakers()) {
                foreach ($speakerIds as $speakerId) {
                    $speaker = Speaker::find($speakerId);
                    $this->sessionService->attachSpeaker($session, $speaker);
                }
            }

            return redirect()
                ->route('admin.sessions.index')
                ->with('success', 'Session created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Session $session)
    {
        $session->load(['agenda', 'track', 'location', 'speakers', 'lectures']);
        return view('admin.sessions.show', compact('session'));
    }

    public function edit(Session $session)
    {
        $agendas = Agenda::orderBy('start_date', 'desc')->get();
        $tracks = Track::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $speakers = Speaker::orderBy('full_name')->get();
        
        return view('admin.sessions.edit', compact('session', 'agendas', 'tracks', 'locations', 'speakers'));
    }

    public function update(Request $request, Session $session)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:talk,panel,workshop,break,networking,keynote',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'agenda_id' => 'required|exists:agendas,id',
            'track_id' => 'nullable|exists:tracks,id',
            'location_id' => 'required|exists:locations,id',
            'max_attendees' => 'nullable|integer|min:1',
            'speaker_ids' => 'nullable|array',
            'speaker_ids.*' => 'exists:speakers,id',
        ]);

        try {
            $speakerIds = $validated['speaker_ids'] ?? [];
            unset($validated['speaker_ids']);

            $this->sessionService->updateSession($session, $validated);

            // Sync speakers
            if ($session->allowsSpeakers()) {
                $session->speakers()->sync($speakerIds);
            } else {
                $session->speakers()->detach();
            }

            return redirect()
                ->route('admin.sessions.index')
                ->with('success', 'Session updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(Session $session)
    {
        $this->sessionService->deleteSession($session);

        return redirect()
            ->route('admin.sessions.index')
            ->with('success', 'Session deleted successfully.');
    }
}
