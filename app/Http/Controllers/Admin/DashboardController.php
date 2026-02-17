<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EventService;
use App\Services\TrackService;
use App\Services\AgendaService;
use App\Services\SessionService;
use App\Models\Track;
use App\Models\Session;

class DashboardController extends Controller
{
    public function __construct(
        private EventService $eventService,
        private TrackService $trackService,
        private AgendaService $agendaService,
        private SessionService $sessionService
    ) {}
    
    public function index()
    {
        $event = $this->eventService->getCurrentEvent();
        $tracks = $this->trackService->getAllTracks();
        $agendas = $this->agendaService->getAllAgendas();
        $sessions = $this->sessionService->getAllSessions();
        
        // Get recent sessions for display
        $agendaItems = $sessions->sortByDesc('created_at')->take(5);
        
        $stats = [
            'total_tracks' => $tracks->count(),
            'total_agendas' => $agendas->count(),
            'total_sessions' => $sessions->count(),
            'upcoming_sessions' => $sessions->where('start_time', '>', now())->count(),
            'featured_sessions' => 0, // Placeholder - can be implemented with a featured flag
        ];
        
        return view('admin.dashboard', compact('event', 'stats', 'tracks', 'agendas', 'sessions', 'agendaItems'));
    }
}
