<?php

namespace App\Shared\Audit\Models;

use App\Shared\Tenancy\TenantScopedModel;
use LogicException;

class PlatformAuditEntry extends TenantScopedModel
{
    public $timestamps = false;

    protected $table = 'platform_audit_entries';

    protected function casts(): array
    {
        return [
            'before_values' => 'array',
            'after_values' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        parent::booted();

        static::updating(function (): never {
            throw new LogicException('Audit entries are immutable.');
        });

        static::deleting(function (): never {
            throw new LogicException('Audit entries cannot be deleted.');
        });
    }
}
