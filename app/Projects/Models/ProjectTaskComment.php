<?php

namespace App\Projects\Models;

use App\Models\OrganizationAdminUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTaskComment extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_task_comments';

    protected function casts(): array
    {
        return [
            'mentioned_admin_ids' => 'array',
            'is_internal' => 'boolean',
            'is_pinned' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(OrganizationAdminUser::class, 'author_admin_id');
    }
}
