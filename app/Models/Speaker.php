<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use App\Traits\HasAuditLogging;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Speaker extends Model
{
    use SoftDeletes, HasEventScope, HasHashedRoutes, HasAuditLogging;

    public $organizationColumn = 'org_id';

    protected $fillable = [
        'full_name',
        'bio',
        'profile_image',
        'email',
        'phone',
        'company',
        'job_title',
    ];

    /**
     * Get all lectures by this speaker
     */
    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    /**
     * Get all sessions this speaker is assigned to
     */
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, 'session_speaker')
            ->withPivot('role')
            ->withTimestamps();
    }
}
