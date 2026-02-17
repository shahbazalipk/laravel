<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Services\PartnerService;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function __construct(
        private PartnerService $service
    ) {}

    public function index()
    {
        $partners = $this->service->getAllPartners();
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sponsorship_label' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo_thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|dimensions:min_width=400,min_height=400',
            'logo_defined_size' => 'nullable|image|mimes:jpg,jpeg,png|dimensions:min_width=400,min_height=400',
            'website_url' => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ], [
            'sponsorship_label.required' => 'The partnership label field is required.',
            'sponsorship_label.string' => 'The partnership label must be a string.',
            'sponsorship_label.max' => 'The partnership label may not be greater than :max characters.',
        ]);

        // Handle checkboxes (they won't be in request if unchecked)
        $validated['visible_on_ebadge'] = $request->has('visible_on_ebadge');
        $validated['visible_on_exhibitor_portal'] = $request->has('visible_on_exhibitor_portal');
        $validated['visible_on_group_portal'] = $request->has('visible_on_group_portal');
        $validated['visible_online'] = $request->has('visible_online');
        $validated['visible_onsite'] = $request->has('visible_onsite');
        $validated['is_active'] = $request->has('is_active');

        $this->service->createPartner($validated);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner created successfully');
    }

    public function edit(Partner $partner)
    {
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sponsorship_label' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo_thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|dimensions:min_width=400,min_height=400',
            'logo_defined_size' => 'nullable|image|mimes:jpg,jpeg,png|dimensions:min_width=400,min_height=400',
            'website_url' => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ], [
            'sponsorship_label.required' => 'The partnership label field is required.',
            'sponsorship_label.string' => 'The partnership label must be a string.',
            'sponsorship_label.max' => 'The partnership label may not be greater than :max characters.',
        ]);

        // Handle checkboxes (they won't be in request if unchecked)
        $validated['visible_on_ebadge'] = $request->has('visible_on_ebadge');
        $validated['visible_on_exhibitor_portal'] = $request->has('visible_on_exhibitor_portal');
        $validated['visible_on_group_portal'] = $request->has('visible_on_group_portal');
        $validated['visible_online'] = $request->has('visible_online');
        $validated['visible_onsite'] = $request->has('visible_onsite');
        $validated['is_active'] = $request->has('is_active');

        $this->service->updatePartner($partner, $validated);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner updated successfully');
    }

    public function destroy(Partner $partner)
    {
        $this->service->deletePartner($partner);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner deleted successfully');
    }

    public function toggleActive(Partner $partner)
    {
        $this->service->toggleActive($partner);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner status updated successfully');
    }
}
