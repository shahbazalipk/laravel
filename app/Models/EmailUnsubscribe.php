<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;

class EmailUnsubscribe extends Model
{
    use HasEventScope;
    
    public $organizationColumn = 'org_id';
    
    protected $fillable = [
        'event_id',
        'org_id',
        'email',
        'reason',
        'unsubscribed_at',
        'ip_address',
        'user_agent',
    ];
    
    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];
    
    // Scopes
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }
}
