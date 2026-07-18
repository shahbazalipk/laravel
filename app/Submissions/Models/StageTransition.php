<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StageTransition extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_stage_transitions';

    protected $casts = [
        'is_automatic' => 'boolean',
        'is_active' => 'boolean',
        'conditions' => 'array',
        'settings' => 'array',
    ];

    public function submissionType(): BelongsTo
    {
        return $this->belongsTo(SubmissionType::class);
    }

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'from_stage_id');
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'to_stage_id');
    }
}
