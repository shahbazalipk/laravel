<?php

namespace App\Submissions\Models;

use App\Submissions\Enums\WorkflowStageCategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowStage extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_workflow_stages';

    protected $casts = [
        'category' => WorkflowStageCategory::class,
        'is_initial' => 'boolean',
        'is_terminal' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function submissionType(): BelongsTo
    {
        return $this->belongsTo(SubmissionType::class);
    }

    public function outgoingTransitions(): HasMany
    {
        return $this->hasMany(StageTransition::class, 'from_stage_id');
    }

    public function incomingTransitions(): HasMany
    {
        return $this->hasMany(StageTransition::class, 'to_stage_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'current_stage_id');
    }
}
