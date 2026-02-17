<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use HasEventScope, HasHashedRoutes, SoftDeletes;
    
    public $organizationColumn = 'org_id';
    
    protected $fillable = [
        'event_id',
        'org_id',
        'name',
        'slug',
        'category',
        'subject',
        'html_content',
        'text_content',
        'description',
        'is_active',
        'usage_count',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];
    
    // Relationships
    public function campaigns()
    {
        return $this->hasMany(EmailCampaign::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
