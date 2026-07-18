<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeakerPresentation extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = ['submitted_at' => 'datetime', 'approved_at' => 'datetime', 'settings' => 'array'];

    public function link(): BelongsTo
    {
        return $this->belongsTo(SpeakerSubmissionLink::class, 'speaker_submission_link_id');
    }
}
