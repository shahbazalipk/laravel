<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionTag extends SubmissionModel
{
    use SoftDeletes;

    public function submissions(): BelongsToMany
    {
        return $this->belongsToMany(Submission::class, 'submission_tag_links', 'tag_id', 'submission_id')
            ->withTimestamps();
    }
}
