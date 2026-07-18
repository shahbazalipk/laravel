<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTeam extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_teams';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectTeamMember::class, 'team_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'team_id');
    }
}
