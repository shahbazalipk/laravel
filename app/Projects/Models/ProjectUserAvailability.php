<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectUserAvailability extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_user_availability';

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_available' => 'boolean',
            'available_minutes' => 'integer',
        ];
    }
}
