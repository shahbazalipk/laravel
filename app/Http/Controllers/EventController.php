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
        $categories = $this->categoryService->getAllCategories();
        $agendaItems = $this->agendaService->getAllAgendaItems();
        
        return view('event.landing', compact('event', 'categories', 'agendaItems'));
    }
}
