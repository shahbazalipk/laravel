<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use App\Traits\HasAuditLogging;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    use SoftDeletes, HasEventScope, HasHashedRoutes, HasAuditLogging;

    public $organizationColumn = 'org_id';

    protected $fillable = [
        'name',
        'description',
        'color',
        'sort_order',
        'agenda_id',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the agenda this track belongs to
     */
    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    /**
     * Get all sessions for this track
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
