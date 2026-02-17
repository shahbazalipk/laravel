<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessActivity;
use App\Services\BusinessActivityService;
use Illuminate\Http\Request;

class BusinessActivityController extends Controller
{
    public function __construct(
        private BusinessActivityService $service
    ) {}

    public function index()
    {
        $businessActivities = $this->service->getAllBusinessActivities();
        return view('admin.business-activities.index', compact('businessActivities'));
    }

    public function create()
    {
        return view('admin.business-activities.create');
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

        $this->service->createBusinessActivity($validated);

        return redirect()->route('admin.business-activities.index')
            ->with('success', 'Business activity created successfully');
    }

    public function edit(BusinessActivity $businessActivity)
    {
        return view('admin.business-activities.edit', compact('businessActivity'));
    }

    public function update(Request $request, BusinessActivity $businessActivity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->updateBusinessActivity($businessActivity, $validated);

        return redirect()->route('admin.business-activities.index')
            ->with('success', 'Business activity updated successfully');
    }

    public function destroy(BusinessActivity $businessActivity)
    {
        $this->service->deleteBusinessActivity($businessActivity);

        return redirect()->route('admin.business-activities.index')
            ->with('success', 'Business activity deleted successfully');
    }

    public function toggleActive(BusinessActivity $businessActivity)
    {
        $this->service->toggleActive($businessActivity);

        return redirect()->route('admin.business-activities.index')
            ->with('success', 'Business activity status updated successfully');
    }
}
