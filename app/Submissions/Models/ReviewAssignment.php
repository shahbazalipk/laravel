<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewAssignment extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_review_assignments';

    protected $casts = [
        'settings' => 'array',
        'due_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Reviewer::class);
    }

    public function scorecard(): BelongsTo
    {
        return $this->belongsTo(Scorecard::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
