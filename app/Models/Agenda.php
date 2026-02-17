<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use App\Traits\HasAuditLogging;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agenda extends Model
{
    use SoftDeletes, HasEventScope, HasHashedRoutes, HasAuditLogging;

    public $organizationColumn = 'org_id';

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get all tracks for this agenda
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    /**
     * Get all sessions for this agenda
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
