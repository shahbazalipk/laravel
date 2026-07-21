<?php

namespace App\Models;

use App\Registration\Enums\RegistrationFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EventUrl extends Model
{
    protected $fillable = [
        'event_id',
        'organization_id',
        'name',
        'slug',
        'type',
        'registration_format',
        'is_active',
        'enabled_categories',
        'allow_reprint',
        'allow_print_from_photo',
        'enable_barcode_scanner',
        'enable_manual_input',
        'description',
        'custom_html',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_reprint' => 'boolean',
        'allow_print_from_photo' => 'boolean',
        'enable_barcode_scanner' => 'boolean',
        'enable_manual_input' => 'boolean',
        'enabled_categories' => 'array',
        'registration_format' => RegistrationFormat::class,
    ];

    public function usesSinglePageRegistration(): bool
    {
        return $this->type === 'online'
            && ($this->registration_format ?? RegistrationFormat::MultiStep) === RegistrationFormat::SinglePage;
    }

    /**
     * Get the event that owns the URL
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function sponsors(): BelongsToMany
    {
        return $this->belongsToMany(Sponsor::class, 'event_url_sponsor');
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'event_url_partner');
    }

    /**
     * Get the full URL path
     */
    public function getFullUrlAttribute(): string
    {
        return match ($this->type) {
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
