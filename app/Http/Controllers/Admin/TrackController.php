<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\Agenda;
use App\Services\TrackService;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    protected TrackService $trackService;

    public function __construct(TrackService $trackService)
    {
        $this->trackService = $trackService;
    }

    public function index()
    {
        $tracks = $this->trackService->getAllTracks();
        return view('admin.tracks.index', compact('tracks'));
    }

    public function create()
    {
        $agendas = Agenda::orderBy('start_date', 'desc')->get();
        return view('admin.tracks.create', compact('agendas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
            'agenda_id' => 'required|exists:agendas,id',
        ]);

        $track = $this->trackService->createTrack($validated);

        return redirect()
            ->route('admin.tracks.index')
            ->with('success', 'Track created successfully.');
    }

    public function show(Track $track)
    {
        $track->load(['agenda', 'sessions']);
        return view('admin.tracks.show', compact('track'));
    }

    public function edit(Track $track)
    {
        $agendas = Agenda::orderBy('start_date', 'desc')->get();
        return view('admin.tracks.edit', compact('track', 'agendas'));
    }

    public function update(Request $request, Track $track)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
            'agenda_id' => 'required|exists:agendas,id',
        ]);

        $this->trackService->updateTrack($track, $validated);

        return redirect()
            ->route('admin.tracks.index')
            ->with('success', 'Track updated successfully.');
    }

    public function destroy(Track $track)
    {
        $this->trackService->deleteTrack($track);

        return redirect()
            ->route('admin.tracks.index')
            ->with('success', 'Track deleted successfully.');
    }
}
