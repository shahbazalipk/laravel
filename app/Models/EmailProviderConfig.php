<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;

class EmailProviderConfig extends Model
{
    use HasEventScope;
    
    public $organizationColumn = 'org_id';
    
    protected $fillable = [
        'event_id',
        'org_id',
        'provider_name',
        'credentials',
        'settings',
        'is_active',
        'is_default',
        'last_tested_at',
        'test_status',
    ];
    
    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'last_tested_at' => 'datetime',
    ];
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
