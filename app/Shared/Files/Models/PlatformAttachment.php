<?php

namespace App\Shared\Files\Models;

use App\Shared\Tenancy\TenantScopedModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformAttachment extends TenantScopedModel
{
    use SoftDeletes;

    protected $table = 'platform_attachments';

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'is_sensitive' => 'boolean',
            'version' => 'integer',
            'expires_at' => 'immutable_datetime',
        ];
    }
}
