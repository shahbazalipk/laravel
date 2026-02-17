<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Services\IndustryService;
use Illuminate\Http\Request;

class IndustryController extends Controller
{
    public function __construct(
        private IndustryService $service
    ) {}

    public function index()
    {
        $industries = $this->service->getAllIndustries();
        return view('admin.industries.index', compact('industries'));
    }

    public function create()
    {
        return view('admin.industries.create');
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

        $this->service->createIndustry($validated);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry created successfully');
    }

    public function edit(Industry $industry)
    {
        return view('admin.industries.edit', compact('industry'));
    }

    public function update(Request $request, Industry $industry)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->updateIndustry($industry, $validated);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry updated successfully');
    }

    public function destroy(Industry $industry)
    {
        $this->service->deleteIndustry($industry);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry deleted successfully');
    }

    public function toggleActive(Industry $industry)
    {
        $this->service->toggleActive($industry);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry status updated successfully');
    }
}
