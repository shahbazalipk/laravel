<?php

namespace App\Submissions\Models;

use App\Submissions\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = [
        'status' => SubmissionStatus::class,
        'settings' => 'array',
        'submitted_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'decision_at' => 'datetime',
        'is_draft' => 'boolean',
        'is_selected' => 'boolean',
        'settings' => 'array',
    ];

    public function submissionType(): BelongsTo
    {
        return $this->belongsTo(SubmissionType::class);
    }

    public function type(): BelongsTo
    {
        return $this->submissionType();
    }

    public function schemaVersion(): BelongsTo
    {
        return $this->belongsTo(SubmissionSchemaVersion::class);
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->portalUser();
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'current_stage_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class);
    }

    public function people(): HasMany
    {
        return $this->hasMany(SubmissionPerson::class)->orderBy('sort_order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function reviewAssignments(): HasMany
    {
        return $this->assignments();
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, ReviewAssignment::class, 'submission_id', 'review_assignment_id');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class);
    }

    public function revisionRequests(): HasMany
    {
        return $this->hasMany(RevisionRequest::class);
    }

    public function revisions(): HasMany
    {
        return $this->revisionRequests();
    }

    public function decision(): HasOne
    {
        return $this->hasOne(Decision::class)->latestOfMany();
    }

    public function speakerLinks(): HasMany
    {
        return $this->hasMany(SpeakerSubmissionLink::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(SubmissionActivity::class)->latest();
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function stageHistory(): HasMany
    {
        return $this->hasMany(SubmissionStageHistory::class);
    }

    public function getReferenceNumberAttribute(): ?string
    {
        return $this->attributes['reference'] ?? null;
    }

    public function setReferenceNumberAttribute(?string $value): void
    {
        $this->attributes['reference'] = $value;
    }

    public function getAverageScoreAttribute(): float
    {
        return (float) ($this->reviews()->whereNotNull('submitted_at')->avg('total_score') ?? 0);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(SubmissionTag::class, 'submission_tag_links', 'submission_id', 'tag_id')
            ->withTimestamps();
    }
}
