<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationAdminUser extends Model
{
    protected $table = 'organization_admin_users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'status',
        'is_primary_admin',
        'last_login_at',
        'email_notifications',
        'browser_notifications',
    ];

    protected $casts = [
        'is_primary_admin' => 'boolean',
        'email_notifications' => 'boolean',
        'browser_notifications' => 'boolean',
        'last_login_at' => 'datetime',
    ];
    
    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
