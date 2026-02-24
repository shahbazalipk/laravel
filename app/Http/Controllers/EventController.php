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
        
        // Get all dynamic data
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
        
        // Get active partners
        $partners = \App\Models\Partner::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        // Get registration categories
        $registrationCategories = \App\Models\RegistrationCategory::orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        // Get exhibitors
        $exhibitors = \App\Models\Exhibitor::where('is_active', true)
            ->orderBy('company_name')
            ->get();
        
        // Get sessions
        $sessions = \App\Models\Session::with(['speaker', 'track', 'location'])
            ->orderBy('start_time')
            ->get();
        
        // Check if event has a custom template
        if ($event->landing_page_template_id && $event->landingPageTemplate) {
            return view('event.landing-template', compact(
                'event', 
                'tracks', 
                'agendaItems', 
                'speakers', 
                'sponsors', 
                'partners',
                'registrationCategories',
                'exhibitors',
                'sessions'
            ));
        }
        
        return view('event.landing', compact(
            'event', 
            'tracks', 
            'agendaItems', 
            'speakers', 
            'sponsors',
            'partners',
            'registrationCategories',
            'exhibitors',
            'sessions'
        ));
    }
}
