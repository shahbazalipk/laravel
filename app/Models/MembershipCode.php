<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;

class MembershipCode extends Model
{
    use HasEventScope, HasHashedRoutes;

    protected $fillable = [
        'membership_id',
        'code',
        'allowed_usage',
        'used',
        'status',
        'event_id',
        'org_id'
    ];

    protected $casts = [
        'allowed_usage' => 'integer',
        'used' => 'integer',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->used < $this->allowed_usage;
    }

    public function incrementUsage(): void
    {
        $this->increment('used');
        
        if ($this->used >= $this->allowed_usage) {
            $this->update(['status' => 'inactive']);
        }
    }

    public function getRemainingUsage(): int
    {
        return max(0, $this->allowed_usage - $this->used);
    }
}
