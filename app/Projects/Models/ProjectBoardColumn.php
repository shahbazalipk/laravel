<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBoardColumn extends ProjectModel
{
    protected $table = 'project_board_columns';

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'work_in_progress_limit' => 'integer',
            'is_terminal' => 'boolean',
            'transition_rules' => 'array',
        ];
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(ProjectBoard::class, 'board_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'column_id')->orderBy('position');
    }
}
