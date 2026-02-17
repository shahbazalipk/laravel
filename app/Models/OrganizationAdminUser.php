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
        'event_id',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];
}
