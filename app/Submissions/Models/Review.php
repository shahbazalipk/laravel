<?php

namespace App\Submissions\Models;

use App\Submissions\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_reviews';

    protected $casts = [
        'status' => ReviewStatus::class,
        'total_score' => 'decimal:3',
        'settings' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ReviewAssignment::class, 'review_assignment_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ReviewAnswer::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReviewComment::class)->whereNull('parent_id');
    }

    public function getNormalizedScoreAttribute(): float
    {
        return (float) $this->total_score;
    }

    public function getReviewerAttribute(): ?Reviewer
    {
        return $this->assignment?->reviewer;
    }
}
