<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExhibitorProduct extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'exhibitor_id',
        'name',
        'description',
        'category',
        'image',
        'images',
        'price',
        'price_text',
        'features',
        'specifications',
        'brochure_url',
        'video_url',
        'demo_url',
        'is_featured',
        'is_new',
        'order',
        'views_count',
        'inquiries_count',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'order' => 'integer',
        'views_count' => 'integer',
        'inquiries_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function exhibitor()
    {
        return $this->belongsTo(Exhibitor::class);
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

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementInquiries()
    {
        $this->increment('inquiries_count');
    }
}
