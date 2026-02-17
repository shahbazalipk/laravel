<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;

class RegistrationStatus extends Model
{
    use HasEventScope, HasHashedRoutes;

    protected $fillable = [
        'name',
        'slug',
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
        
        static::creating(function ($status) {
            if (empty($status->slug)) {
                $status->slug = \Illuminate\Support\Str::slug($status->name);
            }
        });
    }
}
