<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeakerOnboarding extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'speaker_onboarding';

    protected $casts = [
        'profile_data' => 'array',
        'settings' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function speakerSubmissionLink(): BelongsTo
    {
        return $this->belongsTo(SpeakerSubmissionLink::class);
    }

    public function checklistProgress(): HasMany
    {
        return $this->hasMany(SpeakerChecklistProgress::class);
    }
}
