<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTaskDependency extends ProjectModel
{
    protected $table = 'project_task_dependencies';

    protected function casts(): array
    {
        return [
            'lag_days' => 'integer',
            'is_enforced' => 'boolean',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'depends_on_task_id');
    }
}
