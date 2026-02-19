<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalleryAlbum extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'name',
        'description',
        'cover_image',
        'cover_photo',
        'show_cover_at_top',
        'default_photo_status',
        'upload_size',
        'download_size',
        'preset',
        'enable_download',
        'enable_form',
        'gallery_form_id',
        'form_trigger',
        'form_delay_seconds',
        'form_requirement',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_cover_at_top' => 'boolean',
        'enable_download' => 'boolean',
        'enable_form' => 'boolean',
        'order' => 'integer',
        'form_delay_seconds' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function photos()
    {
        return $this->hasMany(Gallery::class);
    }

    public function galleryForm()
    {
        return $this->belongsTo(GalleryForm::class, 'gallery_form_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at', 'desc');
    }

    public function getPhotoCountAttribute()
    {
        return $this->photos()->count();
    }
}
