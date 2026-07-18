<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionAnswer extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = ['value' => 'array', 'answer_json' => 'array'];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class, 'answer_id');
    }
}
