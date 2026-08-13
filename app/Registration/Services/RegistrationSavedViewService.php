<?php

namespace App\Registration\Services;

use App\Models\OrganizationAdminUser;
use App\Registration\Enums\RegistrationSavedViewVisibility;
use App\Registration\Models\RegistrationSavedView;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RegistrationSavedViewService
{
    public function __construct(
        private RegistrationListColumnCatalog $catalog
    ) {}

    public function viewsFor(int $adminId): Collection
    {
        if (! Schema::hasTable('registration_saved_views')) {
            return collect();
        }

        return RegistrationSavedView::query()
            ->with('shares')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->filter(fn (RegistrationSavedView $view) => $view->isVisibleTo($adminId))
            ->values();
    }

    public function resolve(?string $publicId, int $adminId): ?RegistrationSavedView
    {
        $views = $this->viewsFor($adminId);

        if ($publicId) {
            $selected = $views->firstWhere('public_id', $publicId);
            if ($selected) {
                return $selected;
            }
        }

        return $views->firstWhere('is_default', true);
    }

    public function create(int $adminId, array $data): RegistrationSavedView
    {
        return DB::transaction(function () use ($adminId, $data) {
            $visibility = RegistrationSavedViewVisibility::from($data['visibility'] ?? 'private');
            $columns = $this->normalizeColumns($data['columns'] ?? []);
            $shareIds = $this->validatedShareIds($visibility, $data['share_user_ids'] ?? []);

            $this->assertUniqueName($adminId, (string) $data['name']);

            if ($data['is_default'] ?? false) {
                $this->clearDefault($adminId);
            }

            $view = RegistrationSavedView::query()->create([
                'organization_admin_user_id' => $adminId,
                'name' => $data['name'],
                'visibility' => $visibility,
                'is_default' => (bool) ($data['is_default'] ?? false),
                'columns' => $columns,
            ]);

            $this->syncShares($view, $shareIds);

            return $view->load('shares');
        });
    }

    public function update(RegistrationSavedView $view, int $adminId, array $data): RegistrationSavedView
    {
        abort_unless($view->isOwnedBy($adminId), 403);

        return DB::transaction(function () use ($view, $adminId, $data) {
            $visibility = RegistrationSavedViewVisibility::from($data['visibility'] ?? $view->visibility->value);
            $columns = $this->normalizeColumns($data['columns'] ?? $view->columns ?? []);
            $shareIds = $this->validatedShareIds($visibility, $data['share_user_ids'] ?? []);
            $this->assertUniqueName($adminId, (string) ($data['name'] ?? $view->name), $view->id);

            if ($data['is_default'] ?? false) {
                $this->clearDefault($adminId);
            }

            $view->update([
                'name' => $data['name'] ?? $view->name,
                'visibility' => $visibility,
                'is_default' => (bool) ($data['is_default'] ?? false),
                'columns' => $columns,
            ]);

            $this->syncShares($view, $shareIds);

            return $view->fresh('shares');
        });
    }

    public function delete(RegistrationSavedView $view, int $adminId): void
    {
        abort_unless($view->isOwnedBy($adminId), 403);
        $view->shares()->delete();
        $view->delete();
    }

    public function eventAdministrators(): Collection
    {
        if (! Schema::hasTable('organization_admin_users')) {
            return collect();
        }

        $query = OrganizationAdminUser::query()
            ->where('organization_id', config('event.org_id'))
            ->where('status', 'active');

        if (Schema::hasTable('event_user')) {
            $assignedIds = DB::table('event_user')
                ->where('event_id', config('event.event_id'))
                ->pluck('organization_user_id');

            if ($assignedIds->isNotEmpty()) {
                $query->whereIn('id', $assignedIds);
            }
        }

        return $query->orderBy('name')->get(['id', 'name', 'email']);
    }

    private function assertUniqueName(int $adminId, string $name, ?int $ignoreId = null): void
    {
        $exists = RegistrationSavedView::query()
            ->where('organization_admin_user_id', $adminId)
            ->where('name', $name)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'You already have a view with this name.',
            ]);
        }
    }

    private function clearDefault(int $adminId): void
    {
        RegistrationSavedView::query()
            ->where('organization_admin_user_id', $adminId)
            ->update(['is_default' => false]);
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function normalizeColumns(array $columns): array
    {
        $allowed = array_keys($this->catalog->keyed());
        $normalized = [];

        foreach ($columns as $key) {
            $key = (string) $key;
            if (in_array($key, $allowed, true) && ! in_array($key, $normalized, true)) {
                $normalized[] = $key;
            }
        }

        if ($normalized === []) {
            throw ValidationException::withMessages([
                'columns' => 'Select at least one column for this view.',
            ]);
        }

        return $normalized;
    }

    /**
     * @param  array<int, mixed>  $shareIds
     * @return array<int, int>
     */
    private function validatedShareIds(RegistrationSavedViewVisibility $visibility, array $shareIds): array
    {
        if ($visibility !== RegistrationSavedViewVisibility::Users) {
            return [];
        }

        $allowed = $this->eventAdministrators()->pluck('id')->map(fn ($id) => (int) $id);
        $ids = collect($shareIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0 && $allowed->contains($id))
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw ValidationException::withMessages([
                'share_user_ids' => 'Choose at least one person to share this view with.',
            ]);
        }

        return $ids;
    }

    /**
     * @param  array<int, int>  $shareIds
     */
    private function syncShares(RegistrationSavedView $view, array $shareIds): void
    {
        $view->shares()->delete();

        foreach ($shareIds as $adminId) {
            $view->shares()->create([
                'organization_admin_user_id' => $adminId,
            ]);
        }
    }
}
