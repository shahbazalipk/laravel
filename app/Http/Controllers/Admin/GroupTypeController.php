<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupType;
use App\Services\GroupTypeService;
use Illuminate\Http\Request;

class GroupTypeController extends Controller
{
    public function __construct(
        private GroupTypeService $groupTypeService
    ) {}

    public function index()
    {
        $groupTypes = $this->groupTypeService->getAllGroupTypes();
        return view('admin.group-types.index', compact('groupTypes'));
    }

    public function create()
    {
        return view('admin.group-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->groupTypeService->createGroupType($validated);

        return redirect()->route('admin.group-types.index')
            ->with('success', 'Group type created successfully.');
    }

    public function edit(GroupType $groupType)
    {
        return view('admin.group-types.edit', compact('groupType'));
    }

    public function update(Request $request, GroupType $groupType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->groupTypeService->updateGroupType($groupType, $validated);

        return redirect()->route('admin.group-types.index')
            ->with('success', 'Group type updated successfully.');
    }

    public function destroy(GroupType $groupType)
    {
        $this->groupTypeService->deleteGroupType($groupType);

        return redirect()->route('admin.group-types.index')
            ->with('success', 'Group type deleted successfully.');
    }

    public function toggleActive(GroupType $groupType)
    {
        $this->groupTypeService->toggleActive($groupType);

        return redirect()->route('admin.group-types.index')
            ->with('success', 'Group type status updated successfully.');
    }
}
