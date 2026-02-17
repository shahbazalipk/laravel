<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exhibitor extends Model
{
    use HasEventScope, HasHashedRoutes, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        // Required
        'company_name',
        'exhibitor_type_id',
        'industry_id',
        'contact_person_name',
        'contact_email',
        'contact_phone',
        // Company Info
        'description',
        'logo',
        'website_url',
        'year_established',
        'company_size',
        'registration_number',
        // Booth Info
        'booth_type_id',
        'booth_number',
        'booth_size',
        // Additional Contact
        'secondary_contact_name',
        'secondary_contact_email',
        'secondary_contact_phone',
        // Address
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        // Social Media
        'linkedin_url',
        'twitter_url',
        'facebook_url',
        'instagram_url',
        // Event Specific
        'participation_status',
        'registration_date',
        'payment_status',
        'special_requirements',
        // Visibility
        'visible_on_website',
        'visible_on_app',
        'visible_in_directory',
        'is_featured',
        // Media
        'banner_image',
        'catalog_file',
        'video_url',
        // System
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'visible_on_website' => 'boolean',
        'visible_on_app' => 'boolean',
        'visible_in_directory' => 'boolean',
        'is_featured' => 'boolean',
        'registration_date' => 'date',
        'booth_size' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function exhibitorType(): BelongsTo
    {
        return $this->belongsTo(ExhibitorType::class);
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function boothType(): BelongsTo
    {
        return $this->belongsTo(BoothType::class);
    }

    public function businessActivities(): BelongsToMany
    {
        return $this->belongsToMany(BusinessActivity::class, 'exhibitor_business_activity');
    }

    public function productTypes(): BelongsToMany
    {
        return $this->belongsToMany(ProductType::class, 'exhibitor_product_type');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ExhibitorTag::class, 'exhibitor_tag');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('participation_status', $status);
    }

    public function scopeByIndustry($query, int $industryId)
    {
        return $query->where('industry_id', $industryId);
    }

    public function scopeByExhibitorType($query, int $exhibitorTypeId)
    {
        return $query->where('exhibitor_type_id', $exhibitorTypeId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('company_name');
    }

    // Accessors
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo && \Storage::disk('public')->exists($this->logo)) {
            return asset('storage/' . $this->logo);
        }
        return null;
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        if ($this->banner_image && \Storage::disk('public')->exists($this->banner_image)) {
            return asset('storage/' . $this->banner_image);
        }
        return null;
    }

    public function getCatalogFileUrlAttribute(): ?string
    {
        if ($this->catalog_file && \Storage::disk('public')->exists($this->catalog_file)) {
            return asset('storage/' . $this->catalog_file);
        }
        return null;
    }
}
