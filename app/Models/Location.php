<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use App\Traits\HasAuditLogging;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use SoftDeletes, HasEventScope, HasHashedRoutes, HasAuditLogging;

    public $organizationColumn = 'org_id';

    protected $fillable = [
        'name',
        'address',
        'room_number',
        'capacity',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    /**
     * Get all sessions at this location
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get all lectures at this location
     */
    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }
}
