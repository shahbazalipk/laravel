<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Forms\Models\CustomFormResponse;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;

class Group extends Model
{
    use SoftDeletes, HasEventScope, HasHashedRoutes;

    protected $table = 'event_groups';

    protected $fillable = [
        'event_id',
        'org_id',
        'group_name',
        'group_type_id',
        'organization_name',
        'industry_id',
        'description',
        'website_url',
        'allowed_attendees',
        'invoice_number',
        'total_amount',
        'currency',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_date',
        'primary_contact_name',
        'primary_contact_email',
        'primary_contact_phone',
        'secondary_contact_name',
        'secondary_contact_email',
        'secondary_contact_phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'special_requirements',
        'is_active',
        'is_vip',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_vip' => 'boolean',
        'sort_order' => 'integer',
        'allowed_attendees' => 'integer',
        'total_amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // Relationships
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'group_id')->orderBy('last_name')->orderBy('first_name');
    }

    public function paymentEntries(): HasMany
    {
        return $this->hasMany(\App\Payments\Models\GroupPaymentEntry::class, 'event_group_id');
    }

    public function billingTotal(): float
    {
        if ($this->total_amount !== null && (float) $this->total_amount > 0) {
            return round((float) $this->total_amount, 2);
        }

        return round((float) $this->registrations()->sum('total_amount'), 2);
    }

    public function billingCurrency(): string
    {
        if ($this->currency) {
            return strtoupper($this->currency);
        }

        $memberCurrency = $this->registrations()->value('currency');

        return $memberCurrency ? strtoupper($memberCurrency) : 'AED';
    }

    public function groupType()
    {
        return $this->belongsTo(GroupType::class);
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function tags()
    {
        return $this->belongsToMany(ExhibitorTag::class, 'event_group_tag', 'event_group_id', 'exhibitor_tag_id')
                    ->withTimestamps();
    }

    public function customFormResponses(): MorphMany
    {
        return $this->morphMany(CustomFormResponse::class, 'respondent');
    }
}
