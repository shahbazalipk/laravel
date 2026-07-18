<?php

namespace App\Projects\Models;

use App\Models\OrganizationAdminUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTimeLog extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_time_logs';

    protected function casts(): array
    {
        return [
            'logged_on' => 'date',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_minutes' => 'integer',
            'is_billable' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(OrganizationAdminUser::class, 'organization_admin_user_id');
    }
}
