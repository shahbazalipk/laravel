<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventWallPost extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'registration_id',
        'content',
        'post_type',
        'images',
        'link_url',
        'link_title',
        'link_description',
        'link_image',
        'hashtags',
        'mentions',
        'is_pinned',
        'is_approved',
        'is_active',
        'likes_count',
        'comments_count',
    ];

    protected $casts = [
        'images' => 'array',
        'hashtags' => 'array',
        'mentions' => 'array',
        'is_pinned' => 'boolean',
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function comments()
    {
        return $this->hasMany(EventWallComment::class)->where('is_active', true)->whereNull('parent_id');
    }

    public function allComments()
    {
        return $this->hasMany(EventWallComment::class);
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
        return $query->where('is_active', true)->where('is_approved', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc');
    }
}
