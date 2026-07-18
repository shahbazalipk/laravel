<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRecurrenceRule extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_recurrence_rules';

    protected function casts(): array
    {
        return [
            'interval' => 'integer',
            'weekdays' => 'array',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'max_occurrences' => 'integer',
            'generated_occurrences' => 'integer',
            'next_occurrence_on' => 'date',
            'last_generated_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function sourceTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'source_task_id');
    }
}
