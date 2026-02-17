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
            \App\Models\HashMapping::where('model_type', get_class($model))
                ->where('model_id', $model->id)
                ->delete();
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
        $hashService = app(HashService::class);
        return $hashService->resolveHash($value);
    }
}
