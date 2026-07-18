<?php

namespace App\Projects\Models;

use App\Models\OrganizationAdminUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTeamMember extends ProjectModel
{
    public $incrementing = true;

    protected $table = 'project_team_members';

    protected $guarded = ['public_id'];

    protected function casts(): array
    {
        return [
            'weekly_capacity_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(ProjectTeam::class, 'team_id');
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(OrganizationAdminUser::class, 'organization_admin_user_id');
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
