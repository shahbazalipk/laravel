<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Services\MembershipService;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function __construct(
        private MembershipService $service
    ) {}

    public function index()
    {
        $memberships = $this->service->getAllMemberships();
        return view('admin.memberships.index', compact('memberships'));
    }

    public function create()
    {
        return view('admin.memberships.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'verification_type' => 'required|in:upload_file,third_party_api',
            'api_endpoint' => 'nullable|required_if:verification_type,third_party_api|url',
            'api_key' => 'nullable|string',
            'membership_file' => 'nullable|required_if:verification_type,upload_file|file|mimes:txt,csv|max:2048',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $this->service->createMembership($validated);

        return redirect()->route('admin.memberships.index')
            ->with('success', 'Membership created successfully');
    }

    public function edit(Membership $membership)
    {
        return view('admin.memberships.edit', compact('membership'));
    }

    public function update(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'verification_type' => 'required|in:upload_file,third_party_api',
            'api_endpoint' => 'nullable|required_if:verification_type,third_party_api|url',
            'api_key' => 'nullable|string',
            'membership_file' => 'nullable|file|mimes:txt,csv|max:2048',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $this->service->updateMembership($membership, $validated);

        return redirect()->route('admin.memberships.index')
            ->with('success', 'Membership updated successfully');
    }

    public function destroy(Membership $membership)
    {
        $this->service->deleteMembership($membership);

        return redirect()->route('admin.memberships.index')
            ->with('success', 'Membership deleted successfully');
    }

    public function toggleActive(Membership $membership)
    {
        $this->service->toggleActive($membership);

        return redirect()->route('admin.memberships.index')
            ->with('success', 'Membership status toggled successfully');
    }

    public function show(Membership $membership)
    {
        $search = request('search');
        $codes = $this->service->getMembershipCodes($membership, $search);
        
        return view('admin.memberships.show', compact('membership', 'codes', 'search'));
    }

    public function storeCode(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'allowed_usage' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive,expired',
        ]);

        $this->service->addMembershipCode($membership, $validated);

        return redirect()->route('admin.memberships.show', $membership)
            ->with('success', 'Membership code added successfully');
    }

    public function destroyCode(Membership $membership, $codeId)
    {
        $this->service->deleteMembershipCode($codeId);

        return redirect()->route('admin.memberships.show', $membership)
            ->with('success', 'Membership code deleted successfully');
    }

    public function importCodes(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'import_file' => 'required|file|mimes:txt,csv|max:2048',
            'allowed_usage' => 'required|integer|min:1',
        ]);

        $count = $this->service->importMembershipCodes($membership, $validated);

        return redirect()->route('admin.memberships.show', $membership)
            ->with('success', "Successfully imported {$count} membership codes");
    }
}
