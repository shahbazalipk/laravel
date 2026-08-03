<?php

namespace App\Models;

use App\Forms\Models\CustomFormResponse;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasEventScope, HasHashedRoutes, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'registration_category_id',
        'registration_status_id',
        'registration_type',
        'exhibitor_id',
        'group_id',
        'salutation',
        'first_name',
        'last_name',
        'email',
        'profile_picture',
        'phone',
        'mobile_phone',
        'job_title',
        'department',
        'professional_student_id',
        'professional_id_document_path',
        'membership_id',
        'membership_validated',
        'company_name',
        'industry_id',
        'business_activity_id',
        'company_size',
        'company_website',
        'company_address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_registration_number',
        'dietary_requirements',
        'special_needs',
        'tshirt_size',
        'how_did_you_hear',
        'areas_of_interest',
        'marketing_consent',
        'terms_accepted',
        'terms_accepted_at',
        'base_price',
        'tax_amount',
        'total_amount',
        'currency',
        'promo_code_id',
        'promo_code',
        'discount_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_date',
        'badge_number',
        'qr_code',
        'badge_printed',
        'badge_printed_at',
        'checked_in',
        'checked_in_at',
        'checked_in_by',
        'email_verified',
        'email_verified_at',
        'email_verification_token',
        'verification_code',
        'code_verified',
        'code_verified_at',
        'registration_number',
        'registration_source',
        'ip_address',
        'user_agent',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'membership_validated' => 'boolean',
        'marketing_consent' => 'boolean',
        'terms_accepted' => 'boolean',
        'terms_accepted_at' => 'datetime',
        'base_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'badge_printed' => 'boolean',
        'badge_printed_at' => 'datetime',
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
        'email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'code_verified' => 'boolean',
        'code_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'areas_of_interest' => 'array',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function registrationCategory()
    {
        return $this->belongsTo(RegistrationCategory::class);
    }

    public function registrationStatus()
    {
        return $this->belongsTo(RegistrationStatus::class);
    }

    public function exhibitor()
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function businessActivity()
    {
        return $this->belongsTo(BusinessActivity::class);
    }

    public function paymentEntries()
    {
        return $this->hasMany(\App\Payments\Models\RegistrationPaymentEntry::class);
    }

    public function customFormResponses(): MorphMany
    {
        return $this->morphMany(CustomFormResponse::class, 'respondent');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePaid($query)
    {
        return $query->whereIn('payment_status', ['paid', 'overpaid']);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('checked_in', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('registration_category_id', $categoryId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('registration_type', $type);
    }

    public function scopeByStatus($query, $statusId)
    {
        return $query->where('registration_status_id', $statusId);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->total_amount, 2).' '.($this->currency ?? 'AED');
    }

    public function getIsVerifiedAttribute()
    {
        return $this->email_verified && (! $this->verification_code || $this->code_verified);
    }

    public function getCanCheckInAttribute()
    {
        return in_array($this->payment_status, ['paid', 'overpaid'], true)
            && $this->is_verified
            && ! $this->checked_in;
    }
}
