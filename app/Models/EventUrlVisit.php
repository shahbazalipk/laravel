<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventUrlVisit extends Model
{
    use HasEventScope;

    protected $fillable = [
        'event_id',
        'org_id',
        'event_url_id',
        'visitor_uuid',
        'session_key',
        'ip_address',
        'user_agent',
        'browser',
        'browser_version',
        'platform',
        'device_type',
        'referrer',
        'landing_path',
        'landing_query',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'country_code',
        'language',
        'screen_size',
        'pageview_count',
        'duration_seconds',
        'farthest_step',
        'last_step',
        'registered',
        'registration_id',
        'registration_draft_id',
        'is_bounce',
        'started_at',
        'last_seen_at',
        'completed_at',
    ];

    protected $casts = [
        'registered' => 'boolean',
        'is_bounce' => 'boolean',
        'pageview_count' => 'integer',
        'duration_seconds' => 'integer',
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function eventUrl(): BelongsTo
    {
        return $this->belongsTo(EventUrl::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(EventUrlVisitEvent::class);
    }

    public function durationLabel(): string
    {
        $seconds = max(0, (int) $this->duration_seconds);
        if ($seconds < 60) {
            return $seconds.'s';
        }

        $minutes = intdiv($seconds, 60);
        $remain = $seconds % 60;

        return $minutes.'m '.str_pad((string) $remain, 2, '0', STR_PAD_LEFT).'s';
    }
}
