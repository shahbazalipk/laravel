<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeakerCommercialTerm extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'speaker_commercial_terms';

    protected $casts = ['terms' => 'encrypted:array'];

    public function link(): BelongsTo
    {
        return $this->belongsTo(SpeakerSubmissionLink::class, 'speaker_submission_link_id');
    }
}
