<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EventSettingsService;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $templates = \App\Models\LandingPageTemplate::where('is_active', true)->get();
        $timezones = $this->groupedTimezones($event->timezone);

        return view('admin.event-settings.edit', compact('event', 'templates', 'timezones'));
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
            
            // Landing Page Template
            'landing_page_template_id' => 'nullable|exists:landing_page_templates,id',
            
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
            
            // Media Files
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'header_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'portal_background_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'main_floor_plan_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:10240',
            'social_media_share_banner_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Media URLs (legacy)
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
            'timezone' => ['nullable', 'string', 'max:100', Rule::in(DateTimeZone::listIdentifiers())],
            
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
            
            // LLM Integration
            'llm_enabled' => 'nullable|boolean',
            'llm_provider' => 'nullable|string|in:openai,anthropic,google,azure',
            'llm_model' => 'nullable|string|max:255',
            'llm_api_key' => 'nullable|string',
            'llm_settings' => 'nullable|array',
            'llm_settings.temperature' => 'nullable|numeric|min:0|max:2',
            'llm_settings.max_tokens' => 'nullable|integer|min:100|max:8000',
        ]);

        // Handle checkbox - if not present, set to false
        $validated['llm_enabled'] = $request->has('llm_enabled') ? true : false;

        // Handle file uploads
        if ($request->hasFile('logo_file')) {
            if ($event->logo) {
                \Storage::disk('public')->delete($event->logo);
            }
            $path = $request->file('logo_file')->store('event/logos', 'public');
            $validated['logo'] = $path;
        }

        if ($request->hasFile('header_image_file')) {
            if ($event->header_image) {
                \Storage::disk('public')->delete($event->header_image);
            }
            $path = $request->file('header_image_file')->store('event/headers', 'public');
            $validated['header_image'] = $path;
        }

        if ($request->hasFile('portal_background_file')) {
            if ($event->portal_background) {
                \Storage::disk('public')->delete($event->portal_background);
            }
            $path = $request->file('portal_background_file')->store('event/backgrounds', 'public');
            $validated['portal_background'] = $path;
        }

        if ($request->hasFile('main_floor_plan_file')) {
            if ($event->main_floor_plan) {
                \Storage::disk('public')->delete($event->main_floor_plan);
            }
            $path = $request->file('main_floor_plan_file')->store('event/floorplans', 'public');
            $validated['main_floor_plan'] = $path;
        }

        if ($request->hasFile('social_media_share_banner_file')) {
            if ($event->social_media_share_banner) {
                \Storage::disk('public')->delete($event->social_media_share_banner);
            }
            $path = $request->file('social_media_share_banner_file')->store('event/social', 'public');
            $validated['social_media_share_banner'] = $path;
        }

        // Encrypt API key if provided
        if (!empty($validated['llm_api_key']) && $validated['llm_api_key'] !== '••••••••••••••••') {
            $validated['llm_api_key'] = encrypt($validated['llm_api_key']);
        } else {
            unset($validated['llm_api_key']); // Don't update if placeholder
        }

        \Log::info('Updating event settings', [
            'event_id' => $event->id,
            'llm_enabled' => $validated['llm_enabled'] ?? null,
            'llm_provider' => $validated['llm_provider'] ?? null,
            'llm_model' => $validated['llm_model'] ?? null,
            'has_llm_api_key' => isset($validated['llm_api_key']),
            'llm_settings' => $validated['llm_settings'] ?? null,
        ]);

        $this->service->updateEventSettings($event, $validated);

        return redirect()->route('admin.event-settings.edit')
            ->with('success', 'Event settings updated successfully');
    }

    /**
     * @return array<string, list<string>>
     */
    private function groupedTimezones(?string $currentTimezone = null): array
    {
        $grouped = [];

        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            $region = str_contains($timezone, '/')
                ? str_replace('_', ' ', explode('/', $timezone, 2)[0])
                : 'Other';

            $grouped[$region][] = $timezone;
        }

        ksort($grouped);

        if ($currentTimezone && ! in_array($currentTimezone, DateTimeZone::listIdentifiers(), true)) {
            $grouped = ['Current' => [$currentTimezone]] + $grouped;
        }

        return $grouped;
    }
}
