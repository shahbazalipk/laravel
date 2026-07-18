<?php

namespace App\Shared\Notifications\Models;

use App\Shared\Tenancy\TenantScopedModel;

class PlatformNotification extends TenantScopedModel
{
    protected $table = 'platform_notifications';

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'immutable_datetime',
            'emailed_at' => 'immutable_datetime',
        ];
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
