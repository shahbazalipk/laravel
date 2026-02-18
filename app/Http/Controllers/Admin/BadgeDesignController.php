<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadgeDesign;
use Illuminate\Http\Request;

class BadgeDesignController extends Controller
{
    public function index()
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $designs = BadgeDesign::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.badge-designs.index', compact('designs'));
    }

    public function create()
    {
        return view('admin.badge-designs.create');
    }

    public function store(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'size' => 'required|string',
            'orientation' => 'required|string',
            'header_bg_color' => 'required|string',
            'header_text_color' => 'required|string',
            'footer_bg_color' => 'required|string',
            'border_color' => 'required|string',
            'border_width' => 'required|string',
            'profile_picture_shape' => 'required|string',
            'name_font_size' => 'required|string',
            'category_style' => 'required|string',
            'qr_code_size' => 'required|string',
            'qr_code_position' => 'required|string',
        ]);

        $validated['event_id'] = $eventId;
        $validated['org_id'] = $organizationId;
        
        // Handle checkboxes
        $checkboxes = [
            'is_default', 'is_active', 'show_header', 'show_event_logo', 'show_event_name',
            'show_event_dates', 'show_profile_picture', 'show_name', 'show_job_title',
            'show_company', 'show_category', 'show_qr_code', 'show_footer',
            'show_registration_number', 'show_location', 'show_website', 'show_border'
        ];
        
        foreach ($checkboxes as $checkbox) {
            $validated[$checkbox] = $request->has($checkbox);
        }

        // If this is set as default, unset other defaults
        if ($validated['is_default']) {
            BadgeDesign::where('event_id', $eventId)
                ->where('org_id', $organizationId)
                ->update(['is_default' => false]);
        }

        BadgeDesign::create($validated);

        return redirect()->route('admin.badge-designs.index')
            ->with('success', 'Badge design created successfully');
    }

    public function edit(BadgeDesign $badgeDesign)
    {
        return view('admin.badge-designs.edit', compact('badgeDesign'));
    }

    public function update(Request $request, BadgeDesign $badgeDesign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'size' => 'required|string',
            'orientation' => 'required|string',
            'header_bg_color' => 'required|string',
            'header_text_color' => 'required|string',
            'footer_bg_color' => 'required|string',
            'border_color' => 'required|string',
            'border_width' => 'required|string',
            'profile_picture_shape' => 'required|string',
            'name_font_size' => 'required|string',
            'category_style' => 'required|string',
            'qr_code_size' => 'required|string',
            'qr_code_position' => 'required|string',
        ]);

        // Handle checkboxes
        $checkboxes = [
            'is_default', 'is_active', 'show_header', 'show_event_logo', 'show_event_name',
            'show_event_dates', 'show_profile_picture', 'show_name', 'show_job_title',
            'show_company', 'show_category', 'show_qr_code', 'show_footer',
            'show_registration_number', 'show_location', 'show_website', 'show_border'
        ];
        
        foreach ($checkboxes as $checkbox) {
            $validated[$checkbox] = $request->has($checkbox);
        }

        // If this is set as default, unset other defaults
        if ($validated['is_default']) {
            BadgeDesign::where('event_id', $badgeDesign->event_id)
                ->where('org_id', $badgeDesign->org_id)
                ->where('id', '!=', $badgeDesign->id)
                ->update(['is_default' => false]);
        }

        $badgeDesign->update($validated);

        return redirect()->route('admin.badge-designs.index')
            ->with('success', 'Badge design updated successfully');
    }

    public function destroy(BadgeDesign $badgeDesign)
    {
        if ($badgeDesign->is_default) {
            return back()->with('error', 'Cannot delete the default badge design');
        }

        $badgeDesign->delete();

        return redirect()->route('admin.badge-designs.index')
            ->with('success', 'Badge design deleted successfully');
    }
}
