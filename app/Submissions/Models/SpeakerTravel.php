<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeakerTravel extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'speaker_travel';

    protected $casts = [
        'itinerary' => 'array',
        'preferences' => 'array',
        'documents' => 'encrypted:array',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(SpeakerSubmissionLink::class, 'speaker_submission_link_id');
    }
}
