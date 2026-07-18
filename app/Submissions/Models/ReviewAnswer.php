<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewAnswer extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_review_answers';

    protected $casts = [
        'score' => 'decimal:2',
        'value' => 'array',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(ReviewCriterion::class);
    }
}
