<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use App\Services\CategoryService;
use App\Services\AgendaService;

class EventController extends Controller
{
    public function __construct(
        private EventService $eventService,
        private CategoryService $categoryService,
        private AgendaService $agendaService
    ) {}
    
    public function landing()
    {
        $event = $this->eventService->getCurrentEvent();
        $tracks = \App\Models\Track::orderBy('sort_order')->orderBy('name')->get();
        $agendaItems = $this->agendaService->getAllAgendaItems();
        
        // Get speakers with their sessions
        $speakers = \App\Models\Speaker::with('sessions')
            ->orderBy('full_name')
            ->get();
        
        // Get active sponsors that are visible online
        $sponsors = \App\Models\Sponsor::where('is_active', true)
            ->where('visible_online', true)
            ->orderBy('sort_order')
            ->get();
        
        return view('event.landing', compact('event', 'tracks', 'agendaItems', 'speakers', 'sponsors'));
    }
}
