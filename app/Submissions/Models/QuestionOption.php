<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionOption extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_question_options';

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
