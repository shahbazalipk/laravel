<?php

namespace App\Shared\Authorization;

use App\Shared\Authorization\Models\ModuleAccessGrant;

class ModuleAuthorizer
{
    public function allows(string $module, string $ability, ?int $adminId = null): bool
    {
        if (! session('admin_logged_in')) {
            return false;
        }

        if (session('admin_is_primary') === true) {
            return true;
        }

        $adminId ??= (int) session('admin_id');
        if ($adminId < 1) {
            return false;
        }

        $grant = ModuleAccessGrant::query()
            ->where('organization_admin_user_id', $adminId)
            ->where('module', $module)
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $grant?->isUsable()) {
            return false;
        }

        $abilities = $grant->abilities ?? [];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    public function authorize(string $module, string $ability, ?int $adminId = null): void
    {
        abort_unless($this->allows($module, $ability, $adminId), 403);
    }
}
