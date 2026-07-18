<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SubmissionActivity extends SubmissionModel
{
    protected $table = 'submission_activities';

    protected $casts = ['metadata' => 'array'];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @param array<string, mixed> $metadata */
    public static function record(Submission $submission, string $type, array $metadata = [], ?string $description = null): self
    {
        return static::query()->create([
            'submission_id' => $submission->getKey(),
            'subject_type' => $submission->getMorphClass(),
            'subject_id' => $submission->getKey(),
            'actor_type' => session('admin_id') ? 'admin' : 'portal_user',
            'actor_id' => session('admin_id') ?: session('submission_portal_user_id'),
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
