<?php

namespace App\Projects\Models;

use App\Models\OrganizationAdminUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTaskApprovalStep extends ProjectModel
{
    protected $table = 'project_task_approval_steps';

    protected $guarded = ['public_id'];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProjectTaskApprovalRequest::class, 'approval_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(OrganizationAdminUser::class, 'approver_admin_id');
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
