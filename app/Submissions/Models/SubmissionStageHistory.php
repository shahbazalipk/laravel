<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionStageHistory extends SubmissionModel
{
    protected $table = 'submission_stage_histories';

    protected $casts = ['metadata' => 'array'];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
