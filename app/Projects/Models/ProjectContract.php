<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectContract extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_contracts';

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'approved_at' => 'datetime',
            'signed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }
}
