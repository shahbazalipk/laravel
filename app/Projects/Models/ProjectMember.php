<?php

namespace App\Projects\Models;

use App\Models\OrganizationAdminUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends ProjectModel
{
    protected $table = 'project_members';

    protected $guarded = ['public_id'];

    protected function casts(): array
    {
        return [
            'can_view_financials' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
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
