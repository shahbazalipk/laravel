<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoothType;
use App\Services\BoothTypeService;
use Illuminate\Http\Request;

class BoothTypeController extends Controller
{
    public function __construct(
        private BoothTypeService $service
    ) {}

    public function index()
    {
        $boothTypes = $this->service->getAllBoothTypes();
        return view('admin.booth-types.index', compact('boothTypes'));
    }

    public function create()
    {
        return view('admin.booth-types.create');
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

        $this->service->createBoothType($validated);

        return redirect()->route('admin.booth-types.index')
            ->with('success', 'Booth type created successfully');
    }

    public function edit(BoothType $boothType)
    {
        return view('admin.booth-types.edit', compact('boothType'));
    }

    public function update(Request $request, BoothType $boothType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->updateBoothType($boothType, $validated);

        return redirect()->route('admin.booth-types.index')
            ->with('success', 'Booth type updated successfully');
    }

    public function destroy(BoothType $boothType)
    {
        $this->service->deleteBoothType($boothType);

        return redirect()->route('admin.booth-types.index')
            ->with('success', 'Booth type deleted successfully');
    }

    public function toggleActive(BoothType $boothType)
    {
        $this->service->toggleActive($boothType);

        return redirect()->route('admin.booth-types.index')
            ->with('success', 'Booth type status updated successfully');
    }
}
