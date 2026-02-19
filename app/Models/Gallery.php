<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'gallery_album_id',
        'title',
        'description',
        'image_path',
        'edit_settings',
        'apply_watermark',
        'watermark_position',
        'watermark_opacity',
        'frame_style',
        'order',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'apply_watermark' => 'boolean',
        'order' => 'integer',
        'watermark_opacity' => 'integer',
        'edit_settings' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function album()
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at', 'desc');
    }
}
