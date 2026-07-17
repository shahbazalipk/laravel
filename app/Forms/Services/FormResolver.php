<?php

namespace App\Forms\Services;

use App\Forms\Enums\FormAudience;
use App\Forms\Models\CustomForm;
use Illuminate\Database\Eloquent\Collection;

/**
 * Resolves active custom forms for a given audience with definition relations eager-loaded.
 */
class FormResolver
{
    /**
     * Active forms for the audience, ordered by name then id, with questions/options/conditions.
     *
     * @return Collection<int, CustomForm>
     */
    public function activeForAudience(FormAudience|string $audience): Collection
    {
        $value = $audience instanceof FormAudience ? $audience : FormAudience::from($audience);

        return CustomForm::query()
            ->active()
            ->forAudience($value)
            ->orderBy('name')
            ->orderBy('id')
            ->with([
                'questions' => fn ($query) => $query->active()->orderBy('sort_order'),
                'questions.options' => fn ($query) => $query->active()->orderBy('sort_order'),
                'conditions' => fn ($query) => $query->active()->orderBy('sort_order'),
            ])
            ->get();
    }

    /**
     * Resolve a single active form by public id (or null when missing/inactive).
     */
    public function findActiveByPublicId(string $publicId): ?CustomForm
    {
        return CustomForm::query()
            ->active()
            ->where('public_id', $publicId)
            ->with([
                'questions' => fn ($query) => $query->active()->orderBy('sort_order'),
                'questions.options' => fn ($query) => $query->active()->orderBy('sort_order'),
                'conditions' => fn ($query) => $query->active()->orderBy('sort_order'),
            ])
            ->first();
    }

    /**
     * Resolve a single active form by slug for the current tenant.
     */
    public function findActiveBySlug(string $slug): ?CustomForm
    {
        return CustomForm::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'questions' => fn ($query) => $query->active()->orderBy('sort_order'),
                'questions.options' => fn ($query) => $query->active()->orderBy('sort_order'),
                'conditions' => fn ($query) => $query->active()->orderBy('sort_order'),
            ])
            ->first();
    }
}
