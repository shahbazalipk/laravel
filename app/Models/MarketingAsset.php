<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingAsset extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'title',
        'description',
        'type',
        'category',
        'content',
        'image_path',
        'dimensions',
        'hashtags',
        'caption_text',
        'social_platforms',
        'download_count',
        'share_count',
        'order',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'dimensions' => 'array',
        'hashtags' => 'array',
        'social_platforms' => 'array',
        'download_count' => 'integer',
        'share_count' => 'integer',
        'order' => 'integer',
        'is_featured' => 'boolean',
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at', 'desc');
    }

    public function incrementDownloads()
    {
        $this->increment('download_count');
    }

    public function incrementShares()
    {
        $this->increment('share_count');
    }
}
