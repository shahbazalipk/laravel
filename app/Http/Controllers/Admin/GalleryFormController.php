<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryForm;
use Illuminate\Http\Request;

class GalleryFormController extends Controller
{
    public function create()
    {
        return view('admin.gallery-forms.create');
    }

    public function store(Request $request)
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['event_id'] = $eventId;
        $validated['org_id'] = $orgId;
        $validated['is_default'] = $request->has('is_default');
        $validated['is_active'] = $request->has('is_active');

        // If this form is set as default, unset other defaults
        if ($validated['is_default']) {
            GalleryForm::where('event_id', $eventId)
                ->where('org_id', $orgId)
                ->update(['is_default' => false]);
        }

        GalleryForm::create($validated);

        return redirect()->route('admin.gallery-settings.forms')
            ->with('success', 'Form created successfully');
    }

    public function edit(GalleryForm $galleryForm)
    {
        return view('admin.gallery-forms.edit', compact('galleryForm'));
    }

    public function update(Request $request, GalleryForm $galleryForm)
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_default'] = $request->has('is_default');
        $validated['is_active'] = $request->has('is_active');

        // If this form is set as default, unset other defaults
        if ($validated['is_default']) {
            GalleryForm::where('event_id', $eventId)
                ->where('org_id', $orgId)
                ->where('id', '!=', $galleryForm->id)
                ->update(['is_default' => false]);
        }

        $galleryForm->update($validated);

        return redirect()->route('admin.gallery-settings.forms')
            ->with('success', 'Form updated successfully');
    }

    public function destroy(GalleryForm $galleryForm)
    {
        $galleryForm->delete();

        return redirect()->route('admin.gallery-settings.forms')
            ->with('success', 'Form deleted successfully');
    }

    public function setDefault(GalleryForm $galleryForm)
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        GalleryForm::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->update(['is_default' => false]);

        $galleryForm->update(['is_default' => true]);

        return redirect()->route('admin.gallery-settings.forms')
            ->with('success', 'Default form updated successfully');
    }
}
