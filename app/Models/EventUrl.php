<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventUrl extends Model
{
    protected $fillable = [
        'event_id',
        'organization_id',
        'name',
        'slug',
        'type',
        'is_active',
        'enabled_categories',
        'allow_reprint',
        'allow_print_from_photo',
        'enable_barcode_scanner',
        'enable_manual_input',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_reprint' => 'boolean',
        'allow_print_from_photo' => 'boolean',
        'enable_barcode_scanner' => 'boolean',
        'enable_manual_input' => 'boolean',
        'enabled_categories' => 'array',
    ];

    /**
     * Get the event that owns the URL
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the full URL path
     */
    public function getFullUrlAttribute(): string
    {
        return match($this->type) {
            'badge' => "/badge/{$this->slug}",
            'onsite' => "/onsite/{$this->slug}",
            'exhibitors' => "/exhibitors/{$this->slug}",
            'groups' => "/groups/{$this->slug}",
            default => "/online/{$this->slug}",
        };
    }

    /**
     * Get enabled categories
     */
    public function categories()
    {
        if (empty($this->enabled_categories)) {
            return collect();
        }
        
        return RegistrationCategory::whereIn('id', $this->enabled_categories)->get();
    }

    /**
     * Scope for active URLs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
