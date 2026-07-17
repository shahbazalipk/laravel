<?php

namespace App\Sales\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SalesActivity extends Model
{
    use HasEventScope;

    protected $table = 'sales_activities';

    protected $fillable = [
        'event_id',
        'org_id',
        'subject_type',
        'subject_id',
        'type',
        'summary',
        'properties',
        'actor_admin_id',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
