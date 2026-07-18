<?php

namespace App\Models;

use App\Traits\HasAuditLogging;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasAuditLogging, HasEventScope, HasHashedRoutes, SoftDeletes;

    protected $table = 'agenda_sessions';

    public $organizationColumn = 'org_id';

    protected $fillable = [
        'title',
        'description',
        'type',
        'start_time',
        'end_time',
        'agenda_id',
        'track_id',
        'location_id',
        'requires_speakers',
        'max_attendees',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'requires_speakers' => 'boolean',
        'max_attendees' => 'integer',
    ];

    /**
     * Get the agenda this session belongs to
     */
    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    /**
     * Get the track this session belongs to
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the location for this session
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get all lectures in this session
     */
    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    /**
     * Get all speakers for this session
     */
    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class, 'session_speaker')
            ->withPivot('role', 'submission_id', 'presentation_order', 'duration_minutes', 'status', 'confirmed_at', 'event_id', 'org_id')
            ->withTimestamps();
    }

    /**
     * Check if this session type allows speakers
     */
    public function allowsSpeakers(): bool
    {
        return $this->type !== 'break';
    }

    /**
     * Check if this session type allows lectures
     */
    public function allowsLectures(): bool
    {
        return $this->type !== 'break';
    }
}
