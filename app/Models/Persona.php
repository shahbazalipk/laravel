<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;

class Persona extends Model
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
        
        static::creating(function ($persona) {
            if (empty($persona->slug)) {
                $persona->slug = \Illuminate\Support\Str::slug($persona->name);
            }
        });
    }
}
