<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeakerChecklist extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = ['items' => 'array', 'is_active' => 'boolean', 'settings' => 'array'];

    public function progress(): HasMany
    {
        return $this->hasMany(SpeakerChecklistProgress::class);
    }
}
