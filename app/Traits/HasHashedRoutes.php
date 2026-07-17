<?php

namespace App\Traits;

use App\Services\HashService;

trait HasHashedRoutes
{
    protected static function bootHasHashedRoutes()
    {
        static::created(function ($model) {
            $hashService = app(HashService::class);
            $hashService->generateHash($model);
        });

        static::deleting(function ($model) {
            // Keep hashes for soft-deleted records so restored models stay addressable.
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return;
            }

            app(HashService::class)->deleteHash($model);
        });
    }

    public function getHashAttribute(): ?string
    {
        $hashService = app(HashService::class);

        return $hashService->getHash($this);
    }

    public function getRouteKeyName()
    {
        return 'hash';
    }

    public function getRouteKey()
    {
        return $this->hash;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $resolved = app(HashService::class)->resolveHash((string) $value, static::class);

        return $resolved instanceof static ? $resolved : null;
    }
}
