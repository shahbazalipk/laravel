<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectSavedFilter extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_saved_filters';

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'visible_columns' => 'array',
            'is_default' => 'boolean',
        ];
    }
}
