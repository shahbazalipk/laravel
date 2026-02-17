<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;

class GroupType extends Model
{
    use SoftDeletes, HasEventScope, HasHashedRoutes;

    protected $fillable = [
        'event_id',
        'org_id',
        'name',
        'slug',
        'description',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
