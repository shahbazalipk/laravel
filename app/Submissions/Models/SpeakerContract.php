<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeakerContract extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = [
        'terms' => 'encrypted:array',
        'sent_at' => 'datetime',
        'signed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(SpeakerSubmissionLink::class, 'speaker_submission_link_id');
    }
}
