<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Services\AgendaService;
use Illuminate\Http\Request;

class AgendaManagementController extends Controller
{
    protected AgendaService $agendaService;

    public function __construct(AgendaService $agendaService)
    {
        $this->agendaService = $agendaService;
    }

    public function index()
    {
        $agendas = $this->agendaService->getAllAgendas();
        return view('admin.agenda-management.index', compact('agendas'));
    }

    public function create()
    {
        return view('admin.agenda-management.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,published,archived',
        ]);

        try {
            $agenda = $this->agendaService->createAgenda($validated);

            return redirect()
                ->route('admin.agenda-management.index')
                ->with('success', 'Agenda created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Agenda $agenda)
    {
        $statistics = $this->agendaService->getStatistics($agenda);
        $agenda->load(['tracks', 'sessions.location']);
        return view('admin.agenda-management.show', compact('agenda', 'statistics'));
    }

    public function edit(Agenda $agenda)
    {
        return view('admin.agenda-management.edit', compact('agenda'));
    }

    public function update(Request $request, Agenda $agenda)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,published,archived',
        ]);

        try {
            $this->agendaService->updateAgenda($agenda, $validated);

            return redirect()
                ->route('admin.agenda-management.index')
                ->with('success', 'Agenda updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(Agenda $agenda)
    {
        $this->agendaService->deleteAgenda($agenda);

        return redirect()
            ->route('admin.agenda-management.index')
            ->with('success', 'Agenda deleted successfully.');
    }
}
