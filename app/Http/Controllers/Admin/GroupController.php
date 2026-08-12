<?php

namespace App\Http\Controllers\Admin;

use App\Forms\Enums\FormAudience;
use App\Forms\Services\AudienceFormSubmissionService;
use App\Http\Controllers\Controller;
use App\Models\ExhibitorTag;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Industry;
use App\Models\Registration;
use App\Payments\Services\GroupPaymentTotals;
use App\Payments\Services\RecordGroupPayment;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    public function __construct(
        private GroupService $groupService,
        private AudienceFormSubmissionService $audienceForms
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
        $customForms = $this->audienceForms->activeForms(FormAudience::Group);
        $customFormResponses = collect();

        return view('admin.groups.create', compact(
            'groupTypes',
            'industries',
            'tags',
            'customForms',
            'customFormResponses'
        ));
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
            'total_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
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

        try {
            $group = DB::transaction(function () use ($request, $validated) {
                $this->audienceForms->validate(FormAudience::Group, $request);
                $group = $this->groupService->createGroup($validated);
                $this->audienceForms->submit(
                    FormAudience::Group,
                    $group,
                    $request,
                    'admin_group'
                );

                return $group;
            });
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()->route('admin.groups.show', $group)
            ->with('success', 'Group created successfully.');
    }

    public function show(Group $group, GroupPaymentTotals $paymentTotals)
    {
        $group->load([
            'groupType',
            'industry',
            'tags',
            'paymentEntries',
            'registrations.registrationCategory',
            'registrations.registrationStatus',
            'customFormResponses' => fn ($query) => $query
                ->where('status', 'submitted')
                ->orderBy('submitted_at')
                ->with([
                    'form',
                    'answers.files',
                    'answers.question.options',
                ]),
        ]);

        $paymentSummary = $paymentTotals->calculate($group, $group->paymentEntries);
        $paymentRecorder = app(RecordGroupPayment::class);
        $refundableByPayment = $group->paymentEntries
            ->mapWithKeys(fn ($entry) => [
                $entry->id => $paymentRecorder->refundableAmountForPayment($group, $entry),
            ]);

        $availableRegistrations = Registration::query()
            ->whereNull('group_id')
            ->where('event_id', $group->event_id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(200)
            ->get(['id', 'registration_number', 'first_name', 'last_name', 'email']);

        return view('admin.groups.show', compact(
            'group',
            'paymentSummary',
            'refundableByPayment',
            'availableRegistrations'
        ));
    }

    public function edit(Group $group)
    {
        $groupTypes = GroupType::where('is_active', true)->orderBy('name')->get();
        $industries = Industry::where('is_active', true)->orderBy('name')->get();
        $tags = ExhibitorTag::where('is_active', true)->orderBy('name')->get();
        $customForms = $this->audienceForms->activeForms(FormAudience::Group);
        $customFormResponses = $this->audienceForms->existingResponses($group, $customForms);

        return view('admin.groups.edit', compact(
            'group',
            'groupTypes',
            'industries',
            'tags',
            'customForms',
            'customFormResponses'
        ));
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
            'total_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
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

        try {
            DB::transaction(function () use ($request, $group, $validated) {
                $this->audienceForms->validate(FormAudience::Group, $request, $group);
                $this->groupService->updateGroup($group, $validated);
                $this->audienceForms->submit(
                    FormAudience::Group,
                    $group->fresh(),
                    $request,
                    'admin_group'
                );
            });
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()->route('admin.groups.show', $group)
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
