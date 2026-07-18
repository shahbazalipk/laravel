<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reviewer extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_reviewers';

    protected $casts = [
        'settings' => 'array',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function expertise(): HasMany
    {
        return $this->hasMany(ReviewerExpertise::class);
    }

    public function conflicts(): HasMany
    {
        return $this->hasMany(ReviewerConflict::class);
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, ReviewAssignment::class, 'reviewer_id', 'review_assignment_id');
    }

    public function getMaximumCapacityAttribute(): ?int
    {
        return $this->capacity;
    }

    public function getExpertiseLabelsAttribute(): array
    {
        return $this->expertise()->pluck('topic')->all();
    }
}
