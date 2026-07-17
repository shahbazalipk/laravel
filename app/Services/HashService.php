<?php

namespace App\Services;

use App\Models\HashMapping;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HashService
{
    public function generateHash(Model $model): string
    {
        do {
            $hash = Str::random(32);
        } while (
            HashMapping::withoutGlobalScopes()
                ->where('hash', $hash)
                ->exists()
        );

        HashMapping::withoutGlobalScopes()->create([
            'hash' => $hash,
            'model_type' => $model::class,
            'model_id' => $model->getKey(),
            'event_id' => $model->getAttribute('event_id'),
            'org_id' => $model->getAttribute('org_id'),
        ]);

        return $hash;
    }

    public function resolveHash(string $hash, ?string $expectedType = null): ?Model
    {
        $mapping = HashMapping::withoutGlobalScopes()
            ->where('hash', $hash)
            ->first();

        if (!$mapping) {
            return null;
        }

        if (!$this->mappingMatchesCurrentEvent($mapping)) {
            return null;
        }

        if ($expectedType && $mapping->model_type !== $expectedType) {
            return null;
        }

        $modelClass = $mapping->model_type;

        if (!is_string($modelClass) || !class_exists($modelClass)) {
            return null;
        }

        // Model global scopes require event context; bootstrap it from the mapping
        // when public hash links are opened without an existing session context.
        $this->ensureEventContextFromMapping($mapping);

        /** @var Model|null $model */
        $model = $modelClass::query()->find($mapping->model_id);

        return $model instanceof Model ? $model : null;
    }

    public function getHash(Model $model): ?string
    {
        $mapping = HashMapping::withoutGlobalScopes()
            ->where('model_type', $model::class)
            ->where('model_id', $model->getKey())
            ->first();

        return $mapping?->hash;
    }

    public function deleteHash(Model $model): void
    {
        HashMapping::withoutGlobalScopes()
            ->where('model_type', $model::class)
            ->where('model_id', $model->getKey())
            ->delete();
    }

    private function mappingMatchesCurrentEvent(HashMapping $mapping): bool
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        // No tenant selected yet (public hash links) — allow lookup.
        if (!$eventId || !$orgId) {
            return true;
        }

        return (int) $mapping->event_id === (int) $eventId
            && (int) $mapping->org_id === (int) $orgId;
    }

    private function ensureEventContextFromMapping(HashMapping $mapping): void
    {
        if (config('event.event_id') && config('event.org_id')) {
            return;
        }

        config([
            'event.event_id' => (int) $mapping->event_id,
            'event.org_id' => (int) $mapping->org_id,
        ]);
    }
}
