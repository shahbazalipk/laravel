<?php

namespace App\Projects\Models;

use App\Models\OrganizationAdminUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_tasks';

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'estimated_minutes' => 'integer',
            'logged_minutes' => 'integer',
            'progress_percent' => 'integer',
            'labels' => 'array',
            'position' => 'integer',
            'version' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(ProjectBoard::class, 'board_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProjectBoardColumn::class, 'column_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_task_id')->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectTaskComment::class, 'task_id')->orderByDesc('is_pinned')->latest();
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ProjectTaskChecklistItem::class, 'task_id')->orderBy('sort_order');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(ProjectTaskDependency::class, 'task_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(ProjectTaskDependency::class, 'depends_on_task_id');
    }

    public function recurrenceRules(): HasMany
    {
        return $this->hasMany(ProjectRecurrenceRule::class, 'source_task_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganizationAdminUser::class,
            'project_task_assignees',
            'task_id',
            'organization_admin_user_id'
        )->withPivot(['is_primary', 'assigned_at', 'assigned_by']);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'task_id');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ProjectTaskApprovalRequest::class, 'task_id')->latest('requested_at');
    }
}
