<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionFile extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = ['settings' => 'array'];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(SubmissionAnswer::class, 'answer_id');
    }
}
