<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsoToken extends Model
{
    protected $table = 'sso_tokens';
    
    protected $fillable = [
        'token',
        'user_id',
        'organization_id',
        'event_id',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Check if token is valid (not expired and not used)
     */
    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Get the user associated with this token
     */
    public function user()
    {
        return $this->belongsTo(OrganizationAdminUser::class, 'user_id');
    }
}
