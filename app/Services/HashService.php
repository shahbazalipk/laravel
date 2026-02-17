<?php

namespace App\Services;

use App\Models\HashMapping;
use Illuminate\Support\Str;

class HashService
{
    public function generateHash($model): string
    {
        do {
            $hash = Str::random(32);
        } while (HashMapping::where('hash', $hash)->exists());

        HashMapping::create([
            'hash' => $hash,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'event_id' => $model->event_id,
            'org_id' => $model->org_id,
        ]);

        return $hash;
    }

    public function resolveHash(string $hash): ?object
    {
        $mapping = HashMapping::where('hash', $hash)->first();

        if (!$mapping) {
            return null;
        }

        return $mapping->model_type::find($mapping->model_id);
    }

    public function getHash($model): ?string
    {
        $mapping = HashMapping::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->first();

        return $mapping?->hash;
    }
}
