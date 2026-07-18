<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionPerson extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = [
        'is_primary' => 'boolean',
        'profile' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class);
    }

    public function speakerLink(): HasOne
    {
        return $this->hasOne(SpeakerSubmissionLink::class);
    }
}
