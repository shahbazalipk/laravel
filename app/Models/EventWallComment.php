<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventWallComment extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'event_wall_post_id',
        'registration_id',
        'parent_id',
        'content',
        'image',
        'is_active',
        'likes_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'likes_count' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function post()
    {
        return $this->belongsTo(EventWallPost::class, 'event_wall_post_id');
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function parent()
    {
        return $this->belongsTo(EventWallComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(EventWallComment::class, 'parent_id')->where('is_active', true);
    }

    public function likes()
    {
        return $this->morphMany(EventWallLike::class, 'likeable');
    }

    public function isLikedBy($registrationId)
    {
        return $this->likes()->where('registration_id', $registrationId)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
