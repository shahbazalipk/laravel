<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandingPageTemplate extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'event_id',
        'org_id',
        'name',
        'slug',
        'description',
        'preview_image',
        'html_content',
        'css_content',
        'js_content',
        'customizable_sections',
        'default_settings',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'customizable_sections' => 'array',
        'default_settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = \Str::slug($template->name);
                
                // Ensure uniqueness
                $count = 1;
                $originalSlug = $template->slug;
                while (static::where('slug', $template->slug)->exists()) {
                    $template->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($template) {
            if ($template->isDirty('name') && !$template->isDirty('slug')) {
                $template->slug = \Str::slug($template->name);
                
                // Ensure uniqueness
                $count = 1;
                $originalSlug = $template->slug;
                while (static::where('slug', $template->slug)->where('id', '!=', $template->id)->exists()) {
                    $template->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
    
    // Relationships
    public function events()
    {
        return $this->hasMany(Event::class, 'landing_page_template_id');
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Methods
    public function canDelete(): bool
    {
        return $this->events()->count() === 0;
    }
}
