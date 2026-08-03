<?php

namespace App\Models;

use App\Enums\MembershipIdentifierType;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    use HasEventScope, HasHashedRoutes;

    protected $fillable = [
        'name',
        'slug',
        'verification_type',
        'identifier_type',
        'api_endpoint',
        'api_method',
        'api_key',
        'api_headers',
        'api_sample_request',
        'api_sample_response',
        'file_path',
        'color',
        'description',
        'is_active',
        'sort_order',
        'event_id',
        'org_id',
    ];

    protected $casts = [
        'identifier_type' => MembershipIdentifierType::class,
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($membership) {
            if (empty($membership->slug)) {
                $membership->slug = \Illuminate\Support\Str::slug($membership->name);
            }

            if (empty($membership->identifier_type)) {
                $membership->identifier_type = MembershipIdentifierType::MembershipId;
            }
        });
    }

    public function isUploadFile(): bool
    {
        return $this->verification_type === 'upload_file';
    }

    public function isThirdPartyApi(): bool
    {
        return $this->verification_type === 'third_party_api';
    }

    public function getApiHeadersArray(): array
    {
        if (empty($this->api_headers)) {
            return [];
        }

        return json_decode($this->api_headers, true) ?? [];
    }

    public function identifierType(): MembershipIdentifierType
    {
        return $this->identifier_type instanceof MembershipIdentifierType
            ? $this->identifier_type
            : MembershipIdentifierType::tryFrom((string) $this->identifier_type) ?? MembershipIdentifierType::MembershipId;
    }

    public function codes(): HasMany
    {
        return $this->hasMany(MembershipCode::class);
    }
}
