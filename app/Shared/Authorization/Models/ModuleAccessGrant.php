<?php

namespace App\Shared\Authorization\Models;

use App\Shared\Tenancy\TenantScopedModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleAccessGrant extends TenantScopedModel
{
    use SoftDeletes;

    protected $table = 'module_access_grants';

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function isUsable(): bool
    {
        return $this->is_active && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
