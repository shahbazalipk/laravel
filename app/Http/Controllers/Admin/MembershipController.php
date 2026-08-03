<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipIdentifierType;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        return view('admin.memberships.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateMembership($request, creating: true);
        $this->service->createMembership($validated);

        return redirect()->route('admin.memberships.index')
            ->with('success', 'Membership list created successfully.');
    }

    public function edit(Membership $membership)
    {
        return view('admin.memberships.edit', array_merge($this->formData(), compact('membership')));
    }

    public function update(Request $request, Membership $membership)
    {
        $validated = $this->validateMembership($request, creating: false);
        $this->service->updateMembership($membership, $validated);

        return redirect()->route('admin.memberships.index')
            ->with('success', 'Membership list updated successfully.');
    }

    public function destroy(Membership $membership)
    {
        $this->service->deleteMembership($membership);

        return redirect()->route('admin.memberships.index')
            ->with('success', 'Membership list deleted successfully.');
    }

    public function toggleActive(Membership $membership)
    {
        $this->service->toggleActive($membership);

        return redirect()->route('admin.memberships.index')
            ->with('success', 'Membership list status updated.');
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
            ->with('success', $membership->identifierType()->label().' added successfully.');
    }

    public function destroyCode(Membership $membership, $codeId)
    {
        $this->service->deleteMembershipCode($codeId);

        return redirect()->route('admin.memberships.show', $membership)
            ->with('success', $membership->identifierType()->label().' deleted successfully.');
    }

    public function importCodes(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'import_file' => 'required|file|mimes:txt,csv,text/plain|max:5120',
            'allowed_usage' => 'required|integer|min:1',
        ]);

        $count = $this->service->importMembershipCodes($membership, $validated);

        return redirect()->route('admin.memberships.show', $membership)
            ->with('success', "Successfully imported {$count} ".$membership->identifierType()->listLabel().'.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMembership(Request $request, bool $creating): array
    {
        $fileRule = $creating
            ? 'nullable|required_if:verification_type,upload_file|file|mimes:txt,csv,text/plain|max:5120'
            : 'nullable|file|mimes:txt,csv,text/plain|max:5120';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'identifier_type' => ['required', Rule::enum(MembershipIdentifierType::class)],
            'verification_type' => 'required|in:upload_file,third_party_api',
            'api_endpoint' => 'nullable|required_if:verification_type,third_party_api|url|max:2048',
            'api_method' => 'nullable|required_if:verification_type,third_party_api|in:GET,POST',
            'api_key' => 'nullable|string|max:255',
            'api_sample_request' => 'nullable|string',
            'api_sample_response' => 'nullable|string',
            'membership_file' => $fileRule,
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * @return array{identifierTypes: array<int, MembershipIdentifierType>, defaultSampleRequest: string, defaultSampleResponse: string}
     */
    private function formData(): array
    {
        return [
            'identifierTypes' => MembershipIdentifierType::cases(),
            'defaultSampleRequest' => $this->service->defaultSampleRequest(MembershipIdentifierType::MembershipId),
            'defaultSampleResponse' => $this->service->defaultSampleResponse(),
        ];
    }
}
