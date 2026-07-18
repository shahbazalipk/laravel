<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_projects';

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'date',
            'progress_percent' => 'integer',
            'budget_amount' => 'decimal:4',
            'tags' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(ProjectTeam::class, 'team_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(ProjectBoard::class, 'project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'project_id');
    }

    public function risks(): HasMany
    {
        return $this->hasMany(ProjectRisk::class, 'project_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ProjectIssue::class, 'project_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ProjectContract::class, 'project_id');
    }
}
