<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GallerySetting;
use App\Models\GalleryForm;
use Illuminate\Http\Request;

class GallerySettingsController extends Controller
{
    private function getOrCreateSettings()
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        return GallerySetting::firstOrCreate(
            ['event_id' => $eventId, 'org_id' => $orgId],
            [
                'enable_store' => false,
                'primary_color' => '#4F46E5',
                'secondary_color' => '#10B981',
                'default_form_trigger' => 'on_download',
                'default_form_delay_seconds' => 10,
                'default_form_requirement' => 'mandatory',
            ]
        );
    }

    public function index()
    {
        $settings = $this->getOrCreateSettings();
        return view('admin.gallery-settings.index', compact('settings'));
    }

    public function store()
    {
        $settings = $this->getOrCreateSettings();
        return view('admin.gallery-settings.store', compact('settings'));
    }

    public function updateStore(Request $request)
    {
        $settings = $this->getOrCreateSettings();
        
        $validated = $request->validate([
            'enable_store' => 'boolean',
            'store_url' => 'nullable|url',
        ]);

        $validated['enable_store'] = $request->has('enable_store');
        $settings->update($validated);

        return redirect()->route('admin.gallery-settings.store')
            ->with('success', 'Store settings updated successfully');
    }

    public function photoSizes()
    {
        $settings = $this->getOrCreateSettings();
        return view('admin.gallery-settings.photo-sizes', compact('settings'));
    }

    public function updatePhotoSizes(Request $request)
    {
        $settings = $this->getOrCreateSettings();
        
        $validated = $request->validate([
            'photo_sizes' => 'required|array',
        ]);

        $settings->update($validated);

        return redirect()->route('admin.gallery-settings.photo-sizes')
            ->with('success', 'Photo sizes updated successfully');
    }

    public function presets()
    {
        $settings = $this->getOrCreateSettings();
        return view('admin.gallery-settings.presets', compact('settings'));
    }

    public function updatePresets(Request $request)
    {
        $settings = $this->getOrCreateSettings();
        
        $validated = $request->validate([
            'presets' => 'required|array',
        ]);

        $settings->update($validated);

        return redirect()->route('admin.gallery-settings.presets')
            ->with('success', 'Presets updated successfully');
    }

    public function branding()
    {
        $settings = $this->getOrCreateSettings();
        return view('admin.gallery-settings.branding', compact('settings'));
    }

    public function updateBranding(Request $request)
    {
        $settings = $this->getOrCreateSettings();
        
        $validated = $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'custom_css' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            if ($settings->logo) {
                \Storage::disk('public')->delete($settings->logo);
            }
            $path = $request->file('logo')->store('gallery/branding', 'public');
            $validated['logo'] = $path;
        }

        $settings->update($validated);

        return redirect()->route('admin.gallery-settings.branding')
            ->with('success', 'Branding updated successfully');
    }

    public function forms()
    {
        $settings = $this->getOrCreateSettings();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $forms = GalleryForm::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->get();

        return view('admin.gallery-settings.forms', compact('settings', 'forms'));
    }

    public function updateForms(Request $request)
    {
        $settings = $this->getOrCreateSettings();
        
        $validated = $request->validate([
            'default_form_id' => 'nullable|exists:gallery_forms,id',
            'default_form_trigger' => 'required|in:on_open,delayed,on_download',
            'default_form_delay_seconds' => 'nullable|integer|min:0',
            'default_form_requirement' => 'required|in:mandatory,optional',
        ]);

        $settings->update($validated);

        return redirect()->route('admin.gallery-settings.forms')
            ->with('success', 'Form settings updated successfully');
    }
}
