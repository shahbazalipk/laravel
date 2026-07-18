<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionSchemaVersion extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = [
        'schema_snapshot' => 'array',
        'published_at' => 'datetime',
    ];

    public function submissionType(): BelongsTo
    {
        return $this->belongsTo(SubmissionType::class);
    }
}
