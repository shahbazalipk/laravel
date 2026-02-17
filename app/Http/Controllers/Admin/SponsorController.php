<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Services\SponsorService;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    public function __construct(
        private SponsorService $service
    ) {}

    public function index()
    {
        $sponsors = $this->service->getAllSponsors();
        return view('admin.sponsors.index', compact('sponsors'));
    }

    public function create()
    {
        return view('admin.sponsors.create');
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
        ]);

        // Handle checkboxes (they won't be in request if unchecked)
        $validated['visible_on_ebadge'] = $request->has('visible_on_ebadge');
        $validated['visible_on_exhibitor_portal'] = $request->has('visible_on_exhibitor_portal');
        $validated['visible_on_group_portal'] = $request->has('visible_on_group_portal');
        $validated['visible_online'] = $request->has('visible_online');
        $validated['visible_onsite'] = $request->has('visible_onsite');
        $validated['is_active'] = $request->has('is_active');

        $this->service->createSponsor($validated);

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor created successfully');
    }

    public function edit(Sponsor $sponsor)
    {
        return view('admin.sponsors.edit', compact('sponsor'));
    }

    public function update(Request $request, Sponsor $sponsor)
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
        ]);

        // Handle checkboxes (they won't be in request if unchecked)
        $validated['visible_on_ebadge'] = $request->has('visible_on_ebadge');
        $validated['visible_on_exhibitor_portal'] = $request->has('visible_on_exhibitor_portal');
        $validated['visible_on_group_portal'] = $request->has('visible_on_group_portal');
        $validated['visible_online'] = $request->has('visible_online');
        $validated['visible_onsite'] = $request->has('visible_onsite');
        $validated['is_active'] = $request->has('is_active');

        $this->service->updateSponsor($sponsor, $validated);

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor updated successfully');
    }

    public function destroy(Sponsor $sponsor)
    {
        $this->service->deleteSponsor($sponsor);

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor deleted successfully');
    }

    public function toggleActive(Sponsor $sponsor)
    {
        $this->service->toggleActive($sponsor);

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor status updated successfully');
    }
}
