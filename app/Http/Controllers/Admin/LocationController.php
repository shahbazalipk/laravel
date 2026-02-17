<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index()
    {
        $locations = $this->locationService->getAllLocations();
        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'room_number' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $location = $this->locationService->createLocation($validated);

        return redirect()
            ->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }

    public function show(Location $location)
    {
        $location->load(['sessions', 'lectures']);
        return view('admin.locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'room_number' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $this->locationService->updateLocation($location, $validated);

        return redirect()
            ->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $this->locationService->deleteLocation($location);

        return redirect()
            ->route('admin.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
