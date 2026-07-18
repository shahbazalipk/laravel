<?php

namespace App\Projects\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRisk extends ProjectModel
{
    use SoftDeletes;

    protected $table = 'project_risks';

    protected function casts(): array
    {
        return [
            'probability' => 'integer',
            'impact' => 'integer',
            'score' => 'integer',
            'due_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
