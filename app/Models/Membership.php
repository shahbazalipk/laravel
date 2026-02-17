<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;

class Membership extends Model
{
    use HasEventScope, HasHashedRoutes;

    protected $fillable = [
        'name',
        'slug',
        'verification_type',
        'api_endpoint',
        'api_key',
        'api_headers',
        'file_path',
        'color',
        'description',
        'is_active',
        'sort_order',
        'event_id',
        'org_id'
    ];

    protected $casts = [
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

    public function codes()
    {
        return $this->hasMany(MembershipCode::class);
    }
}
