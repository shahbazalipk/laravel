<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;

class GalleryFormSubmission extends Model
{
    use HasEventScope;

    protected $fillable = [
        'event_id',
        'org_id',
        'gallery_form_id',
        'gallery_id',
        'registration_id',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function galleryForm()
    {
        return $this->belongsTo(GalleryForm::class);
    }

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
