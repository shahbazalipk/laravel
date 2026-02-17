<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExhibitorTag;
use App\Services\ExhibitorTagService;
use Illuminate\Http\Request;

class ExhibitorTagController extends Controller
{
    public function __construct(
        private ExhibitorTagService $service
    ) {}

    public function index()
    {
        $exhibitorTags = $this->service->getAllExhibitorTags();
        return view('admin.exhibitor-tags.index', compact('exhibitorTags'));
    }

    public function create()
    {
        return view('admin.exhibitor-tags.create');
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

        $this->service->createExhibitorTag($validated);

        return redirect()->route('admin.exhibitor-tags.index')
            ->with('success', 'Exhibitor tag created successfully');
    }

    public function edit(ExhibitorTag $exhibitorTag)
    {
        return view('admin.exhibitor-tags.edit', compact('exhibitorTag'));
    }

    public function update(Request $request, ExhibitorTag $exhibitorTag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->updateExhibitorTag($exhibitorTag, $validated);

        return redirect()->route('admin.exhibitor-tags.index')
            ->with('success', 'Exhibitor tag updated successfully');
    }

    public function destroy(ExhibitorTag $exhibitorTag)
    {
        $this->service->deleteExhibitorTag($exhibitorTag);

        return redirect()->route('admin.exhibitor-tags.index')
            ->with('success', 'Exhibitor tag deleted successfully');
    }

    public function toggleActive(ExhibitorTag $exhibitorTag)
    {
        $this->service->toggleActive($exhibitorTag);

        return redirect()->route('admin.exhibitor-tags.index')
            ->with('success', 'Exhibitor tag status updated successfully');
    }
}
