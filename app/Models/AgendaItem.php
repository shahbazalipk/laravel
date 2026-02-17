<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;

class AgendaItem extends Model
{
    use HasEventScope;
    
    protected $table = 'event_sessions';
    
    /**
     * The organization column name for this model
     */
    public $organizationColumn = 'org_id';
    
    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'location_id',
        'track_id',
        'event_id',
        'org_id',
        'session_type',
        'is_break',
        'duration',
        'capacity',
        'level',
        'is_featured',
        'is_active'
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_break' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'track_id');
    }
    
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
