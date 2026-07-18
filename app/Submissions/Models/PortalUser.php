<?php

namespace App\Submissions\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortalUser extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_portal_users';

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'settings' => 'array',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function people(): HasMany
    {
        return $this->hasMany(SubmissionPerson::class);
    }

    public function loginTokens(): HasMany
    {
        return $this->hasMany(PortalLoginToken::class);
    }

    public function reviewer(): HasOne
    {
        return $this->hasOne(Reviewer::class);
    }

    /** @return list<string> */
    public function getRolesAttribute(): array
    {
        return $this->settings['roles'] ?? [];
    }

    /** @param list<string> $roles */
    public function setRolesAttribute(array $roles): void
    {
        $settings = $this->settings ?? [];
        $settings['roles'] = array_values(array_unique($roles));
        $this->attributes['settings'] = json_encode($settings);
    }
}
