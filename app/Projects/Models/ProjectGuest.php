<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectGuest extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_guests';

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'invitation_expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(ProjectGuestAccess::class, 'guest_id');
    }
}
