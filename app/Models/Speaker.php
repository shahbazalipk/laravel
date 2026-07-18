<?php

namespace App\Models;

use App\Submissions\Models\SpeakerSubmissionLink;
use App\Traits\HasAuditLogging;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Speaker extends Model
{
    use HasAuditLogging, HasEventScope, HasHashedRoutes, SoftDeletes;

    public $organizationColumn = 'org_id';

    protected $fillable = [
        'full_name',
        'bio',
        'profile_image',
        'email',
        'phone',
        'company',
        'job_title',
        'public_id',
        'onboarding_status',
        'profile',
    ];

    protected $casts = ['profile' => 'array'];

    protected static function booted(): void
    {
        static::creating(function (self $speaker): void {
            $speaker->public_id ??= (string) Str::uuid();
        });
    }

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
            ->withPivot('role', 'submission_id', 'presentation_order', 'duration_minutes', 'status', 'confirmed_at', 'event_id', 'org_id')
            ->withTimestamps();
    }

    public function submissionLinks(): HasMany
    {
        return $this->hasMany(SpeakerSubmissionLink::class);
    }
}
