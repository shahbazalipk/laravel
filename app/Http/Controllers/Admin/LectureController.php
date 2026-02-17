<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\Session;
use App\Models\Speaker;
use App\Models\Location;
use App\Services\LectureService;
use Illuminate\Http\Request;

class LectureController extends Controller
{
    protected LectureService $lectureService;

    public function __construct(LectureService $lectureService)
    {
        $this->lectureService = $lectureService;
    }

    public function index()
    {
        $lectures = $this->lectureService->getAllLectures();
        return view('admin.lectures.index', compact('lectures'));
    }

    public function create()
    {
        $sessions = Session::where('type', '!=', 'break')
            ->orderBy('start_time')
            ->get();
        $speakers = Speaker::orderBy('full_name')->get();
        $locations = Location::orderBy('name')->get();
        
        return view('admin.lectures.create', compact('sessions', 'speakers', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_id' => 'required|exists:agenda_sessions,id',
            'speaker_id' => 'required|exists:speakers,id',
            'location_id' => 'required|exists:locations,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            $lecture = $this->lectureService->createLecture($validated);

            return redirect()
                ->route('admin.lectures.index')
                ->with('success', 'Lecture created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Lecture $lecture)
    {
        $lecture->load(['session', 'speaker', 'location']);
        return view('admin.lectures.show', compact('lecture'));
    }

    public function edit(Lecture $lecture)
    {
        $sessions = Session::where('type', '!=', 'break')
            ->orderBy('start_time')
            ->get();
        $speakers = Speaker::orderBy('full_name')->get();
        $locations = Location::orderBy('name')->get();
        
        return view('admin.lectures.edit', compact('lecture', 'sessions', 'speakers', 'locations'));
    }

    public function update(Request $request, Lecture $lecture)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_id' => 'required|exists:agenda_sessions,id',
            'speaker_id' => 'required|exists:speakers,id',
            'location_id' => 'required|exists:locations,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            $this->lectureService->updateLecture($lecture, $validated);

            return redirect()
                ->route('admin.lectures.index')
                ->with('success', 'Lecture updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(Lecture $lecture)
    {
        $this->lectureService->deleteLecture($lecture);

        return redirect()
            ->route('admin.lectures.index')
            ->with('success', 'Lecture deleted successfully.');
    }
}
