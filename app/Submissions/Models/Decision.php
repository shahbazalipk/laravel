<?php

namespace App\Submissions\Models;

use App\Submissions\Enums\DecisionType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Decision extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_decisions';

    protected $casts = [
        'decision' => DecisionType::class,
        'settings' => 'array',
        'decided_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function revisionRequests(): HasMany
    {
        return $this->hasMany(RevisionRequest::class);
    }
}
