<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTaskApprovalRequest extends ProjectModel
{
    protected $table = 'project_task_approval_requests';

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'current_sequence' => 'integer',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProjectTaskApprovalStep::class, 'approval_request_id')->orderBy('sequence');
    }
}
