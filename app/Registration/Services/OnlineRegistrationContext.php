<?php

namespace App\Registration\Services;

use App\Models\Event;
use App\Models\EventUrl;
use App\Models\Industry;
use App\Models\RegistrationCategory;
use App\Registration\Exceptions\RegistrationUrlClosedException;
use App\Services\RegistrationService;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class OnlineRegistrationContext
{
    public function __construct(
        private RegistrationService $registrationService
    ) {}

    public function resolveEvent(): Event
    {
        $event = Event::getCurrentEvent();

        if (!$event || !$event->registration_form_active) {
            throw new RegistrationUrlClosedException($event);
        }

        if ($event->online_reg_close && now()->greaterThan($event->online_reg_close)) {
            throw new RegistrationUrlClosedException($event, null, 'Online registration has closed for this event.');
        }

        return $event;
    }

    public function resolveEventUrl(string $slug, Event $event): EventUrl
    {
        $eventUrl = $this->findEventUrl($slug, $event);

        if (!$eventUrl) {
            abort(404, 'Registration URL not found or inactive');
        }

        if (!$eventUrl->isRegistrationOpen()) {
            throw new RegistrationUrlClosedException($event, $eventUrl);
        }

        return $eventUrl;
    }

    public function findEventUrl(string $slug, Event $event): ?EventUrl
    {
        $query = EventUrl::query()->where('slug', $slug);

        $eventUrl = (clone $query)
            ->where('event_id', $event->id)
            ->first();

        if (!$eventUrl && !empty($event->event_id)) {
            $eventUrl = (clone $query)
                ->where('event_id', $event->event_id)
                ->first();
        }

        if (!$eventUrl) {
            return null;
        }

        $orgId = $event->organization_id ?? $event->org_id ?? null;
        if ($orgId && $eventUrl->organization_id && (int) $eventUrl->organization_id !== (int) $orgId) {
            return null;
        }

        return $eventUrl;
    }

    public function allowedCategories(Event $event, ?EventUrl $eventUrl = null): Collection
    {
        $query = RegistrationCategory::query()
            ->where('event_id', $event->id)
            ->where('is_active', true)
            ->where('visible', true);

        $enabled = $this->normalizedEnabledCategoryIds($eventUrl);
        if (!empty($enabled)) {
            $query->whereIn('id', $enabled);
        }

        return $query->orderBy('sort_order')->get()->filter(function (RegistrationCategory $category) {
            if ($category->valid_from && now()->lt($category->valid_from)) {
                return false;
            }

            if ($category->valid_to && now()->gt($category->valid_to)) {
                return false;
            }

            return true;
        })->values();
    }

    public function assertCategoryAllowed(
        RegistrationCategory $category,
        Event $event,
        ?EventUrl $eventUrl = null
    ): void {
        if ((int) $category->event_id !== (int) $event->id) {
            throw new InvalidArgumentException('Selected category is not available for this event.');
        }

        if (!$category->is_active || !$category->visible) {
            throw new InvalidArgumentException('Selected category is not available.');
        }

        $enabled = $this->normalizedEnabledCategoryIds($eventUrl);
        if (!empty($enabled) && !in_array((int) $category->id, $enabled, true)) {
            throw new InvalidArgumentException('Selected category is not available for this registration URL.');
        }
    }

    /**
     * Event URL JSON often stores category IDs as strings; normalize for safe comparisons.
     *
     * @return list<int>
     */
    public function normalizedEnabledCategoryIds(?EventUrl $eventUrl): array
    {
        $enabled = $eventUrl?->enabled_categories;

        if (empty($enabled) || !is_array($enabled)) {
            return [];
        }

        return array_values(array_unique(array_map(
            static fn ($id): int => (int) $id,
            $enabled
        )));
    }

    public function categoriesWithPricing(Event $event, ?EventUrl $eventUrl = null): Collection
    {
        return $this->allowedCategories($event, $eventUrl)->map(function (RegistrationCategory $category) use ($event) {
            $category->setAttribute('pricing', $this->registrationService->calculatePrice($category, $event));

            return $category;
        });
    }

    public function industries(Event $event): Collection
    {
        return Industry::query()
            ->where('event_id', $event->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
