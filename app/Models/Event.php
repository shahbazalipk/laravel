<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'organization_id',
        'event_id',
        'title',
        'subdomain',
        'logo',
        'start_date',
        'end_date',
        'timezone',
        'type',
        'format',
        'country',
        'location',
        'languages',
        'currency',
        'visibility',
        'status',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'social_image',
        // Event Settings
        'event_name',
        'event_type',
        'event_mode',
        'stage',
        'online_reg_close',
        'vat_percentage',
        'tax_inclusive',
        'registration_form_active',
        'email_verification_required',
        'code_verification_required',
        'bulk_print_enabled',
        'reprint_enabled',
        'website_url',
        'terms_url',
        'map_url',
        'placeholder_type',
        'placeholder_url',
        'header_image',
        'portal_background',
        'main_floor_plan',
        'closed_message',
        'show_info_on_portal_background',
        'footer_information',
        'manager_name',
        'manager_email',
        'manager_phone',
        'address_line1',
        'address_line2',
        'state',
        'city',
        'twitter_mention',
        'social_media_description',
        'social_media_share_banner',
        'social_media_share_font_color',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        // Landing Page Template
        'landing_page_template_id',
        'template_settings',
        // LLM Integration
        'llm_provider',
        'llm_model',
        'llm_api_key',
        'llm_settings',
        'llm_enabled',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'online_reg_close' => 'datetime',
        'languages' => 'array',
        'template_settings' => 'array',
        'llm_settings' => 'array',
        'llm_enabled' => 'boolean',
    ];
    
    public function landingPageTemplate()
    {
        return $this->belongsTo(LandingPageTemplate::class);
    }
    
    public static function getCurrentEvent()
    {
        // For single-event focus, get the event by configured ID
        // If not found, return the first event for this organization
        $event = self::where('id', config('event.event_id'))
            ->where('organization_id', config('event.org_id'))
            ->first();
            
        if (!$event) {
            // Fallback: get first event for this organization
            $event = self::where('organization_id', config('event.org_id'))->first();
        }
        
        if (!$event) {
            // Last fallback: get any event (for development)
            $event = self::first();
        }
        
        return $event;
    }
}
