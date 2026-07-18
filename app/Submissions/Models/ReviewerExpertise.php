<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewerExpertise extends SubmissionModel
{
    protected $table = 'submission_reviewer_expertise';

    protected $casts = ['settings' => 'array'];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Reviewer::class);
    }
}
