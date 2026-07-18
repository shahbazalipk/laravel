<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevisionRequest extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_revision_requests';

    protected $casts = [
        'required_question_ids' => 'array',
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'answer_snapshot' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(Decision::class);
    }
}
