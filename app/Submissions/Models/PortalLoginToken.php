<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalLoginToken extends SubmissionModel
{
    protected $table = 'submission_portal_tokens';

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class, 'portal_user_id');
    }
}
