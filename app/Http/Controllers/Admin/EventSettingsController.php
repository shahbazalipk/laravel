<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EventSettingsService;
use Illuminate\Http\Request;

class EventSettingsController extends Controller
{
    public function __construct(
        private EventSettingsService $service
    ) {}

    public function edit()
    {
        $event = $this->service->getCurrentEvent();
        
        if (!$event) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Event not found');
        }

        return view('admin.event-settings.edit', compact('event'));
    }

    public function update(Request $request)
    {
        $event = $this->service->getCurrentEvent();
        
        if (!$event) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Event not found');
        }

        $validated = $request->validate([
            // Basic Info
            'event_name' => 'nullable|string|max:255',
            'event_type' => 'nullable|string|max:255',
            'event_mode' => 'nullable|string|max:255',
            'stage' => 'nullable|string|max:255',
            
            // Dates
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'online_reg_close' => 'nullable|date',
            
            // Description
            'description' => 'nullable|string',
            
            // Financial
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:10',
            'tax_inclusive' => 'boolean',
            
            // Registration Settings
            'registration_form_active' => 'boolean',
            'email_verification_required' => 'boolean',
            'code_verification_required' => 'boolean',
            'bulk_print_enabled' => 'boolean',
            'reprint_enabled' => 'boolean',
            
            // URLs
            'website_url' => 'nullable|url|max:500',
            'terms_url' => 'nullable|url|max:500',
            'map_url' => 'nullable|url|max:500',
            
            // Media
            'placeholder_type' => 'nullable|string|max:50',
            'placeholder_url' => 'nullable|string|max:1000',
            'logo' => 'nullable|string|max:500',
            'header_image' => 'nullable|string|max:500',
            'portal_background' => 'nullable|string|max:500',
            'main_floor_plan' => 'nullable|string|max:500',
            
            // Messages
            'closed_message' => 'nullable|string',
            'show_info_on_portal_background' => 'boolean',
            'footer_information' => 'nullable|string',
            
            // Manager Info
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'manager_phone' => 'nullable|string|max:50',
            
            // Address
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:100',
            
            // Social Media
            'twitter_mention' => 'nullable|string|max:255',
            'social_media_description' => 'nullable|string',
            'social_media_share_banner' => 'nullable|string|max:500',
            'social_media_share_font_color' => 'nullable|string|max:7',
            
            // Email Settings
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|max:10',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            
            // Captcha
            'recaptcha_site_key' => 'nullable|string|max:255',
            'recaptcha_secret_key' => 'nullable|string|max:255',
            
            // SEO
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);

        $this->service->updateEventSettings($event, $validated);

        return redirect()->route('admin.event-settings.edit')
            ->with('success', 'Event settings updated successfully');
    }
}
