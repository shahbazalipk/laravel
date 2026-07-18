<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeakerChecklistProgress extends SubmissionModel
{
    protected $table = 'speaker_checklist_progress';

    protected $casts = ['response' => 'array', 'completed_at' => 'datetime'];

    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(SpeakerOnboarding::class, 'speaker_onboarding_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(SpeakerChecklist::class, 'speaker_checklist_id');
    }
}
