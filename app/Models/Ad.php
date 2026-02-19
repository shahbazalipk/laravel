<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use HasEventScope, SoftDeletes;
    
    public $organizationColumn = 'org_id';
    
    protected $fillable = [
        'event_id',
        'org_id',
        'title',
        'type',
        'placement',
        'image',
        'content',
        'link_url',
        'link_text',
        'open_new_tab',
        'display_order',
        'start_date',
        'end_date',
        'max_impressions',
        'impressions_count',
        'clicks_count',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'open_new_tab' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
        'max_impressions' => 'integer',
        'display_order' => 'integer',
    ];
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_impressions')
                  ->orWhereRaw('impressions_count < max_impressions');
            });
    }
    
    public function scopeByPlacement($query, string $placement)
    {
        return $query->where('placement', $placement);
    }
    
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
    
    // Methods
    public function incrementImpressions()
    {
        $this->increment('impressions_count');
    }
    
    public function incrementClicks()
    {
        $this->increment('clicks_count');
    }
    
    public function isExpired(): bool
    {
        if ($this->end_date && $this->end_date->isPast()) {
            return true;
        }
        
        if ($this->max_impressions && $this->impressions_count >= $this->max_impressions) {
            return true;
        }
        
        return false;
    }
    
    public function getCtrAttribute(): float
    {
        if ($this->impressions_count === 0) {
            return 0;
        }
        
        return round(($this->clicks_count / $this->impressions_count) * 100, 2);
    }
}
