<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSection extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_form_sections';

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function submissionType(): BelongsTo
    {
        return $this->belongsTo(SubmissionType::class);
    }

    public function schemaVersion(): BelongsTo
    {
        return $this->belongsTo(SubmissionSchemaVersion::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }
}
