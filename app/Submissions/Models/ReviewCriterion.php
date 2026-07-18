<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewCriterion extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_review_criteria';

    protected $casts = [
        'weight' => 'decimal:3',
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'is_required' => 'boolean',
        'settings' => 'array',
    ];

    public function scorecard(): BelongsTo
    {
        return $this->belongsTo(Scorecard::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ReviewAnswer::class, 'criterion_id');
    }
}
