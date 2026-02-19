<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;

class GallerySetting extends Model
{
    use HasEventScope;

    protected $fillable = [
        'event_id',
        'org_id',
        'enable_store',
        'store_url',
        'photo_sizes',
        'presets',
        'logo',
        'primary_color',
        'secondary_color',
        'custom_css',
        'default_form_id',
        'default_form_trigger',
        'default_form_delay_seconds',
        'default_form_requirement',
    ];

    protected $casts = [
        'enable_store' => 'boolean',
        'photo_sizes' => 'array',
        'presets' => 'array',
        'default_form_delay_seconds' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function defaultForm()
    {
        return $this->belongsTo(GalleryForm::class, 'default_form_id');
    }
}
