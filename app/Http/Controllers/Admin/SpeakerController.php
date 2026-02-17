<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Speaker;
use App\Services\SpeakerService;
use Illuminate\Http\Request;

class SpeakerController extends Controller
{
    protected SpeakerService $speakerService;

    public function __construct(SpeakerService $speakerService)
    {
        $this->speakerService = $speakerService;
    }

    public function index()
    {
        $speakers = $this->speakerService->getAllSpeakers();
        return view('admin.speakers.index', compact('speakers'));
    }

    public function create()
    {
        return view('admin.speakers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
        ]);

        $speaker = $this->speakerService->createSpeaker($validated);

        return redirect()
            ->route('admin.speakers.index')
            ->with('success', 'Speaker created successfully.');
    }

    public function show(Speaker $speaker)
    {
        $schedule = $this->speakerService->getSchedule($speaker);
        return view('admin.speakers.show', compact('speaker', 'schedule'));
    }

    public function edit(Speaker $speaker)
    {
        return view('admin.speakers.edit', compact('speaker'));
    }

    public function update(Request $request, Speaker $speaker)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
        ]);

        $this->speakerService->updateSpeaker($speaker, $validated);

        return redirect()
            ->route('admin.speakers.index')
            ->with('success', 'Speaker updated successfully.');
    }

    public function destroy(Speaker $speaker)
    {
        $this->speakerService->deleteSpeaker($speaker);

        return redirect()
            ->route('admin.speakers.index')
            ->with('success', 'Speaker deleted successfully.');
    }
}
