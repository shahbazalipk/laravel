<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExhibitorType;
use App\Services\ExhibitorTypeService;
use Illuminate\Http\Request;

class ExhibitorTypeController extends Controller
{
    public function __construct(
        private ExhibitorTypeService $service
    ) {}

    public function index()
    {
        $exhibitorTypes = $this->service->getAllExhibitorTypes();
        return view('admin.exhibitor-types.index', compact('exhibitorTypes'));
    }

    public function create()
    {
        return view('admin.exhibitor-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->createExhibitorType($validated);

        return redirect()->route('admin.exhibitor-types.index')
            ->with('success', 'Exhibitor type created successfully');
    }

    public function edit(ExhibitorType $exhibitorType)
    {
        return view('admin.exhibitor-types.edit', compact('exhibitorType'));
    }

    public function update(Request $request, ExhibitorType $exhibitorType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->updateExhibitorType($exhibitorType, $validated);

        return redirect()->route('admin.exhibitor-types.index')
            ->with('success', 'Exhibitor type updated successfully');
    }

    public function destroy(ExhibitorType $exhibitorType)
    {
        $this->service->deleteExhibitorType($exhibitorType);

        return redirect()->route('admin.exhibitor-types.index')
            ->with('success', 'Exhibitor type deleted successfully');
    }

    public function toggleActive(ExhibitorType $exhibitorType)
    {
        $this->service->toggleActive($exhibitorType);

        return redirect()->route('admin.exhibitor-types.index')
            ->with('success', 'Exhibitor type status updated successfully');
    }
}
