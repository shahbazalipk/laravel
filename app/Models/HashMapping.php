<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasEventScope;

class HashMapping extends Model
{
    use HasEventScope;

    protected $fillable = [
        'hash',
        'model_type',
        'model_id',
        'event_id',
        'org_id'
    ];

    public function model()
    {
        return $this->morphTo();
    }
}
