<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Services\PersonaService;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    public function __construct(
        private PersonaService $service
    ) {}

    public function index()
    {
        $personas = $this->service->getAllPersonas();
        return view('admin.personas.index', compact('personas'));
    }

    public function create()
    {
        return view('admin.personas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $this->service->createPersona($validated);

        return redirect()->route('admin.personas.index')
            ->with('success', 'Persona created successfully');
    }

    public function edit(Persona $persona)
    {
        return view('admin.personas.edit', compact('persona'));
    }

    public function update(Request $request, Persona $persona)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $this->service->updatePersona($persona, $validated);

        return redirect()->route('admin.personas.index')
            ->with('success', 'Persona updated successfully');
    }

    public function destroy(Persona $persona)
    {
        $this->service->deletePersona($persona);

        return redirect()->route('admin.personas.index')
            ->with('success', 'Persona deleted successfully');
    }

    public function toggleActive(Persona $persona)
    {
        $this->service->toggleActive($persona);

        return redirect()->route('admin.personas.index')
            ->with('success', 'Persona status toggled successfully');
    }
}
