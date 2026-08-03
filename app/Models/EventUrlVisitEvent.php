<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventUrlVisitEvent extends Model
{
    use HasEventScope;

    protected $fillable = [
        'event_id',
        'org_id',
        'event_url_id',
        'event_url_visit_id',
        'event_type',
        'step',
        'path',
        'meta',
        'occurred_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(EventUrlVisit::class, 'event_url_visit_id');
    }

    public function eventUrl(): BelongsTo
    {
        return $this->belongsTo(EventUrl::class);
    }
}
