<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConditionalRule extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_conditional_rules';

    protected $casts = [
        'compare_value' => 'array',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function submissionType(): BelongsTo
    {
        return $this->belongsTo(SubmissionType::class);
    }

    public function sourceQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'source_question_id');
    }

    public function targetQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'target_question_id');
    }
}
