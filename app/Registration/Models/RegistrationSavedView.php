<?php

namespace App\Registration\Models;

use App\Registration\Enums\RegistrationSavedViewVisibility;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RegistrationSavedView extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'organization_admin_user_id',
        'name',
        'visibility',
        'is_default',
        'columns',
    ];

    protected $casts = [
        'visibility' => RegistrationSavedViewVisibility::class,
        'is_default' => 'boolean',
        'columns' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $view): void {
            if (empty($view->public_id)) {
                $view->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function shares(): HasMany
    {
        return $this->hasMany(RegistrationSavedViewShare::class);
    }

    public function isOwnedBy(?int $adminId): bool
    {
        return $adminId !== null && (int) $this->organization_admin_user_id === $adminId;
    }

    public function isVisibleTo(int $adminId): bool
    {
        if ($this->isOwnedBy($adminId)) {
            return true;
        }

        if ($this->visibility === RegistrationSavedViewVisibility::Public) {
            return true;
        }

        if ($this->visibility === RegistrationSavedViewVisibility::Users) {
            return $this->shares->contains(
                fn (RegistrationSavedViewShare $share) => (int) $share->organization_admin_user_id === $adminId
            );
        }

        return false;
    }
}
