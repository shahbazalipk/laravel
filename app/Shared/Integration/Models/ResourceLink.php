<?php

namespace App\Shared\Integration\Models;

use App\Shared\Tenancy\TenantScopedModel;

class ResourceLink extends TenantScopedModel
{
    protected $table = 'platform_resource_links';

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
