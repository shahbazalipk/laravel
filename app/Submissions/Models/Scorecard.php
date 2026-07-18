<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scorecard extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_scorecards';

    protected $casts = [
        'passing_score' => 'decimal:2',
        'settings' => 'array',
    ];

    public function submissionType(): BelongsTo
    {
        return $this->belongsTo(SubmissionType::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(ReviewCriterion::class)->orderBy('sort_order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
    }
}
