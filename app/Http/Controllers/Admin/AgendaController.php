<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AgendaService;
use App\Services\CategoryService;
use App\Models\AgendaItem;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function __construct(
        private AgendaService $agendaService,
        private CategoryService $categoryService
    ) {}
    
    public function index()
    {
        $agendaItems = $this->agendaService->getAllAgendaItems();
        return view('admin.agenda.index', compact('agendaItems'));
    }
    
    public function create()
    {
        $categories = $this->categoryService->getAllCategories();
        return view('admin.agenda.create', compact('categories'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'track_id' => 'nullable|exists:tracks,id',
            'session_type' => 'required|in:keynote,workshop,panel,presentation,breakout,networking,other',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'nullable|integer',
            'capacity' => 'nullable|integer',
            'level' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_break' => 'boolean',
        ]);
        
        $this->agendaService->createAgendaItem($validated);
        
        return redirect()->route('admin.agenda.index')
            ->with('success', 'Session created successfully');
    }
    
    public function edit(AgendaItem $agendaItem)
    {
        $categories = $this->categoryService->getAllCategories();
        return view('admin.agenda.edit', compact('agendaItem', 'categories'));
    }
    
    public function update(Request $request, AgendaItem $agendaItem)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'track_id' => 'nullable|exists:tracks,id',
            'session_type' => 'required|in:keynote,workshop,panel,presentation,breakout,networking,other',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'nullable|integer',
            'capacity' => 'nullable|integer',
            'level' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_break' => 'boolean',
            'is_active' => 'boolean',
        ]);
        
        $this->agendaService->updateAgendaItem($agendaItem, $validated);
        
        return redirect()->route('admin.agenda.index')
            ->with('success', 'Session updated successfully');
    }
    
    public function destroy(AgendaItem $agendaItem)
    {
        $this->agendaService->deleteAgendaItem($agendaItem);
        
        return redirect()->route('admin.agenda.index')
            ->with('success', 'Session deleted successfully');
    }
}
