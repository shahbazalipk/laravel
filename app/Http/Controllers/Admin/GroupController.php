<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Industry;
use App\Models\ExhibitorTag;
use App\Services\GroupService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct(
        private GroupService $groupService
    ) {}

    public function index()
    {
        $groups = $this->groupService->getAllGroups();
        return view('admin.groups.index', compact('groups'));
    }

    public function create()
    {
        $groupTypes = GroupType::where('is_active', true)->orderBy('name')->get();
        $industries = Industry::where('is_active', true)->orderBy('name')->get();
        $tags = ExhibitorTag::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.groups.create', compact('groupTypes', 'industries', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'group_type_id' => 'required|exists:group_types,id',
            'organization_name' => 'nullable|string|max:255',
            'industry_id' => 'nullable|exists:industries,id',
            'description' => 'nullable|string',
            'website_url' => 'nullable|url|max:255',
            'allowed_attendees' => 'required|integer|min:1',
            'invoice_number' => 'nullable|string|max:255',
            'primary_contact_name' => 'required|string|max:255',
            'primary_contact_email' => 'required|email|max:255',
            'primary_contact_phone' => 'required|string|max:50',
            'secondary_contact_name' => 'nullable|string|max:255',
            'secondary_contact_email' => 'nullable|email|max:255',
            'secondary_contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'special_requirements' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:exhibitor_tags,id',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_vip'] = $request->has('is_vip');

        $this->groupService->createGroup($validated);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group created successfully.');
    }

    public function show(Group $group)
    {
        $group->load(['groupType', 'industry', 'tags']);
        return view('admin.groups.show', compact('group'));
    }

    public function edit(Group $group)
    {
        $groupTypes = GroupType::where('is_active', true)->orderBy('name')->get();
        $industries = Industry::where('is_active', true)->orderBy('name')->get();
        $tags = ExhibitorTag::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.groups.edit', compact('group', 'groupTypes', 'industries', 'tags'));
    }

    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'group_type_id' => 'required|exists:group_types,id',
            'organization_name' => 'nullable|string|max:255',
            'industry_id' => 'nullable|exists:industries,id',
            'description' => 'nullable|string',
            'website_url' => 'nullable|url|max:255',
            'allowed_attendees' => 'required|integer|min:1',
            'invoice_number' => 'nullable|string|max:255',
            'primary_contact_name' => 'required|string|max:255',
            'primary_contact_email' => 'required|email|max:255',
            'primary_contact_phone' => 'required|string|max:50',
            'secondary_contact_name' => 'nullable|string|max:255',
            'secondary_contact_email' => 'nullable|email|max:255',
            'secondary_contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'special_requirements' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:exhibitor_tags,id',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_vip'] = $request->has('is_vip');

        $this->groupService->updateGroup($group, $validated);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group updated successfully.');
    }

    public function destroy(Group $group)
    {
        $this->groupService->deleteGroup($group);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group deleted successfully.');
    }

    public function toggleActive(Group $group)
    {
        $this->groupService->toggleActive($group);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group status updated successfully.');
    }

    public function toggleVip(Group $group)
    {
        $this->groupService->toggleVip($group);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group VIP status updated successfully.');
    }
}
