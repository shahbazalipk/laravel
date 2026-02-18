<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeDesign extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'name',
        'description',
        'is_default',
        'is_active',
        'size',
        'orientation',
        'show_header',
        'header_bg_color',
        'header_text_color',
        'show_event_logo',
        'show_event_name',
        'show_event_dates',
        'show_profile_picture',
        'profile_picture_shape',
        'show_name',
        'name_font_size',
        'show_job_title',
        'show_company',
        'show_category',
        'category_style',
        'show_qr_code',
        'qr_code_size',
        'qr_code_position',
        'show_footer',
        'footer_bg_color',
        'show_registration_number',
        'show_location',
        'show_website',
        'show_border',
        'border_color',
        'border_width',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'show_header' => 'boolean',
        'show_event_logo' => 'boolean',
        'show_event_name' => 'boolean',
        'show_event_dates' => 'boolean',
        'show_profile_picture' => 'boolean',
        'show_name' => 'boolean',
        'show_job_title' => 'boolean',
        'show_company' => 'boolean',
        'show_category' => 'boolean',
        'show_qr_code' => 'boolean',
        'show_footer' => 'boolean',
        'show_registration_number' => 'boolean',
        'show_location' => 'boolean',
        'show_website' => 'boolean',
        'show_border' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
