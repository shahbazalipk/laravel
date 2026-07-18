<?php

namespace App\Shared\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class TenantScopedModel extends Model
{
    protected $guarded = [];

    /** @var array<string, bool> */
    private static array $publicIdColumns = [];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = TenantContext::fromConfig();
            $model = $builder->getModel();

            $builder
                ->where($model->qualifyColumn('event_id'), $context->eventId)
                ->where($model->qualifyColumn('org_id'), $context->organizationId);
        });

        static::creating(function (self $model): void {
            $context = TenantContext::fromConfig();

            if ($model->event_id !== null && (int) $model->event_id !== $context->eventId) {
                $context->assertMatches((int) $model->event_id, (int) ($model->org_id ?? 0));
            }

            if ($model->org_id !== null && (int) $model->org_id !== $context->organizationId) {
                $context->assertMatches((int) ($model->event_id ?? 0), (int) $model->org_id);
            }

            $model->event_id = $context->eventId;
            $model->org_id = $context->organizationId;

            $schemaKey = ($model->getConnectionName() ?? 'default').':'.$model->getTable();
            $hasPublicId = self::$publicIdColumns[$schemaKey]
                ??= $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'public_id');
            if ($hasPublicId) {
                $model->public_id ??= (string) Str::uuid();
            }
        });

        static::updating(function (self $model): void {
            TenantContext::fromConfig()->assertMatches(
                (int) $model->getOriginal('event_id'),
                (int) $model->getOriginal('org_id'),
            );

            if ($model->isDirty(['event_id', 'org_id'])) {
                throw new \LogicException('Tenant ownership cannot be changed.');
            }
        });

        static::deleting(function (self $model): void {
            TenantContext::fromConfig()->assertMatches(
                (int) $model->event_id,
                (int) $model->org_id,
            );
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
