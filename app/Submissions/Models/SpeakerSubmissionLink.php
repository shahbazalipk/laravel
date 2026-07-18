<?php

namespace App\Submissions\Models;

use App\Models\Speaker;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeakerSubmissionLink extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = [
        'linked_at' => 'datetime',
        'settings' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(SubmissionPerson::class, 'submission_person_id');
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function onboarding(): HasOne
    {
        return $this->hasOne(SpeakerOnboarding::class);
    }

    public function commercialTerm(): HasOne
    {
        return $this->hasOne(SpeakerCommercialTerm::class);
    }

    public function travel(): HasOne
    {
        return $this->hasOne(SpeakerTravel::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(SpeakerContract::class);
    }

    public function presentations(): HasMany
    {
        return $this->hasMany(SpeakerPresentation::class);
    }
}
