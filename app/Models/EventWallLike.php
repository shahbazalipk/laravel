<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;

class EventWallLike extends Model
{
    use HasEventScope;

    protected $fillable = [
        'event_id',
        'org_id',
        'registration_id',
        'likeable_type',
        'likeable_id',
        'reaction_type',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function likeable()
    {
        return $this->morphTo();
    }
}
