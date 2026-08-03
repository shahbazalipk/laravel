<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUrl;
use App\Registration\Services\OnlineRegistrationContext;
use App\Services\EventUrlAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class EventUrlTrackingController extends Controller
{
    public function __construct(
        private EventUrlAnalyticsService $analytics,
        private OnlineRegistrationContext $context,
    ) {}

    public function track(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => ['required', 'string', 'max:64'],
            'step' => ['nullable', 'string', 'max:64'],
            'path' => ['nullable', 'string', 'max:500'],
            'query' => ['nullable', 'string', 'max:1000'],
            'referrer' => ['nullable', 'string', 'max:1000'],
            'language' => ['nullable', 'string', 'max:32'],
            'screen_size' => ['nullable', 'string', 'max:32'],
            'active_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'visitor_uuid' => ['nullable', 'uuid'],
            'session_key' => ['nullable', 'string', 'max:64'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ]);

        try {
            [$event, $eventUrl] = $this->resolve($slug);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'slug' => $exception->getMessage(),
            ]);
        }

        $visit = $this->analytics->startOrResumeVisit($eventUrl, $event, $request, $validated);
        $this->analytics->queueCookies($visit->visitor_uuid, $visit->session_key);
        $this->analytics->recordEvent($visit, $validated);

        return response()->json([
            'ok' => true,
            'visitor_uuid' => $visit->visitor_uuid,
            'session_key' => $visit->session_key,
            'duration_seconds' => (int) $visit->fresh()->duration_seconds,
        ]);
    }

    /**
     * @return array{0: Event, 1: EventUrl}
     */
    private function resolve(string $slug): array
    {
        $event = Event::getCurrentEvent();
        if (! $event) {
            throw new InvalidArgumentException('Event context is missing.');
        }

        $eventUrl = EventUrl::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where(function ($query) use ($event) {
                $query->where('event_id', $event->id);
                if (! empty($event->event_id)) {
                    $query->orWhere('event_id', $event->event_id);
                }
            })
            ->first();

        if (! $eventUrl) {
            // Fallback for online registration URLs that use OnlineRegistrationContext rules.
            try {
                $event = $this->context->resolveEvent();
                $eventUrl = $this->context->resolveEventUrl($slug, $event);
            } catch (InvalidArgumentException) {
                abort(404);
            }
        }

        return [$event, $eventUrl];
    }
}
