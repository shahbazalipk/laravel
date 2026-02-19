<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalleryForm extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'name',
        'description',
        'fields',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
