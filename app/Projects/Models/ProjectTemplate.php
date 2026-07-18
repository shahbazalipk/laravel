<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTemplate extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_templates';

    protected function casts(): array
    {
        return [
            'definition' => 'array',
            'task_count' => 'integer',
            'is_active' => 'boolean',
            'is_shared' => 'boolean',
        ];
    }
}
