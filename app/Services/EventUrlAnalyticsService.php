<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventUrl;
use App\Models\EventUrlVisit;
use App\Models\EventUrlVisitEvent;
use App\Models\Registration;
use App\Registration\Models\RegistrationDraft;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EventUrlAnalyticsService
{
    public const COOKIE_VISITOR = 'event_url_vid';

    public const COOKIE_SESSION = 'event_url_sid';

    public const STEP_ORDER = [
        'entry' => 1,
        'email' => 2,
        'information' => 3,
        'category' => 4,
        'form' => 3,
        'otp' => 4,
        'confirmation' => 5,
        'completed' => 6,
    ];

    public function startOrResumeVisit(EventUrl $eventUrl, Event $event, Request $request, array $client = []): EventUrlVisit
    {
        $visitorUuid = $this->visitorUuid($request, $client['visitor_uuid'] ?? null);
        $sessionKey = $this->sessionKey($request, $client['session_key'] ?? null);
        $ua = Str::limit((string) $request->userAgent(), 512, '');
        $parsed = $this->parseUserAgent($ua);
        $now = now();

        $visit = EventUrlVisit::query()
            ->where('event_url_id', $eventUrl->id)
            ->where('session_key', $sessionKey)
            ->first();

        if ($visit) {
            $visit->fill([
                'last_seen_at' => $now,
                'language' => $client['language'] ?? $visit->language,
                'screen_size' => $client['screen_size'] ?? $visit->screen_size,
            ]);
            $visit->save();

            return $visit->fresh();
        }

        return EventUrlVisit::query()->create([
            'event_id' => $event->id,
            'org_id' => $event->organization_id ?? $event->org_id ?? config('event.org_id'),
            'event_url_id' => $eventUrl->id,
            'visitor_uuid' => $visitorUuid,
            'session_key' => $sessionKey,
            'ip_address' => $request->ip(),
            'user_agent' => $ua !== '' ? $ua : null,
            'browser' => $parsed['browser'],
            'browser_version' => $parsed['browser_version'],
            'platform' => $parsed['platform'],
            'device_type' => $parsed['device_type'],
            'referrer' => Str::limit((string) ($client['referrer'] ?? $request->headers->get('referer')), 1000, ''),
            'landing_path' => Str::limit((string) ($client['path'] ?? $request->path()), 500, ''),
            'landing_query' => Str::limit((string) ($client['query'] ?? $request->getQueryString()), 1000, ''),
            'utm_source' => $this->utm($request, $client, 'utm_source'),
            'utm_medium' => $this->utm($request, $client, 'utm_medium'),
            'utm_campaign' => $this->utm($request, $client, 'utm_campaign'),
            'utm_term' => $this->utm($request, $client, 'utm_term'),
            'utm_content' => $this->utm($request, $client, 'utm_content'),
            'country_code' => $this->countryCode($request),
            'language' => isset($client['language']) ? Str::limit((string) $client['language'], 32, '') : null,
            'screen_size' => isset($client['screen_size']) ? Str::limit((string) $client['screen_size'], 32, '') : null,
            'pageview_count' => 0,
            'duration_seconds' => 0,
            'farthest_step' => 'entry',
            'last_step' => 'entry',
            'registered' => false,
            'is_bounce' => true,
            'started_at' => $now,
            'last_seen_at' => $now,
        ]);
    }

    /**
     * @param  array{event_type?: string, step?: string|null, path?: string|null, meta?: array<string, mixed>, active_seconds?: int}  $payload
     */
    public function recordEvent(EventUrlVisit $visit, array $payload): EventUrlVisitEvent
    {
        $eventType = Str::limit((string) ($payload['event_type'] ?? 'page_view'), 64, '');
        $step = isset($payload['step']) && $payload['step'] !== ''
            ? Str::limit((string) $payload['step'], 64, '')
            : null;
        $now = now();
        $activeSeconds = max(0, (int) ($payload['active_seconds'] ?? 0));

        $visit->duration_seconds = max((int) $visit->duration_seconds, $activeSeconds);
        $visit->last_seen_at = $now;

        if (in_array($eventType, ['page_view', 'step_view'], true)) {
            $visit->pageview_count = (int) $visit->pageview_count + 1;
        }

        if ($step) {
            $visit->last_step = $step;
            if ($this->stepRank($step) >= $this->stepRank($visit->farthest_step)) {
                $visit->farthest_step = $step;
            }
        }

        if ($eventType === 'registration_completed' || $step === 'completed') {
            $visit->registered = true;
            $visit->completed_at = $visit->completed_at ?? $now;
            $visit->farthest_step = 'completed';
            $visit->last_step = 'completed';
            $visit->is_bounce = false;
        }

        if ((int) $visit->pageview_count > 1 || (int) $visit->duration_seconds >= 15 || $visit->registered) {
            $visit->is_bounce = false;
        }

        $visit->save();

        return EventUrlVisitEvent::query()->create([
            'event_id' => $visit->event_id,
            'org_id' => $visit->org_id,
            'event_url_id' => $visit->event_url_id,
            'event_url_visit_id' => $visit->id,
            'event_type' => $eventType,
            'step' => $step,
            'path' => isset($payload['path']) ? Str::limit((string) $payload['path'], 500, '') : null,
            'meta' => $payload['meta'] ?? null,
            'occurred_at' => $now,
        ]);
    }

    public function attachDraft(EventUrlVisit $visit, RegistrationDraft $draft): void
    {
        $visit->registration_draft_id = $draft->id;
        $visit->save();
    }

    public function markRegistered(EventUrlVisit $visit, Registration $registration): void
    {
        $visit->fill([
            'registered' => true,
            'registration_id' => $registration->id,
            'completed_at' => $visit->completed_at ?? now(),
            'farthest_step' => 'completed',
            'last_step' => 'completed',
            'is_bounce' => false,
            'last_seen_at' => now(),
        ])->save();

        EventUrlVisitEvent::query()->create([
            'event_id' => $visit->event_id,
            'org_id' => $visit->org_id,
            'event_url_id' => $visit->event_url_id,
            'event_url_visit_id' => $visit->id,
            'event_type' => 'registration_completed',
            'step' => 'completed',
            'path' => null,
            'meta' => [
                'registration_id' => $registration->id,
                'registration_number' => $registration->registration_number,
            ],
            'occurred_at' => now(),
        ]);
    }

    public function markRegisteredForRequest(Request $request, EventUrl $eventUrl, Registration $registration): void
    {
        $sessionKey = $request->cookie(self::COOKIE_SESSION);
        if (! is_string($sessionKey) || $sessionKey === '') {
            return;
        }

        $visit = EventUrlVisit::query()
            ->where('event_url_id', $eventUrl->id)
            ->where('session_key', $sessionKey)
            ->first();

        if ($visit) {
            $this->markRegistered($visit, $registration);
        }
    }

    /**
     * @return array{
     *   visits: int,
     *   unique_visitors: int,
     *   registrations: int,
     *   conversion_rate: float,
     *   bounce_rate: float,
     *   avg_duration_seconds: float,
     *   total_pageviews: int,
     *   funnel: array<string, int>,
     *   stuck: array<string, int>,
     *   devices: array<string, int>,
     *   browsers: array<string, int>,
     *   platforms: array<string, int>,
     *   utm_sources: array<string, int>,
     *   countries: array<string, int>,
     *   monthly: list<array{month: string, label: string, visits: int, registrations: int}>,
     *   current_month: array{
     *     month: string,
     *     label: string,
     *     visits: int,
     *     registrations: int,
     *     days: list<array{date: string, label: string, visits: int, registrations: int}>
     *   }
     * }
     */
    public function summarize(EventUrl $eventUrl, ?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $query = EventUrlVisit::query()->where('event_url_id', $eventUrl->id);
        if ($from) {
            $query->where('started_at', '>=', $from);
        }
        if ($to) {
            $query->where('started_at', '<=', $to);
        }

        $visits = (clone $query)->get();
        $visitCount = $visits->count();
        $unique = $visits->pluck('visitor_uuid')->unique()->count();
        $registrations = $visits->where('registered', true)->count();
        $bounces = $visits->where('is_bounce', true)->count();
        $avgDuration = $visitCount > 0 ? round((float) $visits->avg('duration_seconds'), 1) : 0.0;
        $pageviews = (int) $visits->sum('pageview_count');

        $funnel = [];
        foreach (array_keys(self::STEP_ORDER) as $step) {
            $funnel[$step] = $visits->filter(fn (EventUrlVisit $v) => $this->stepRank($v->farthest_step) >= $this->stepRank($step))->count();
        }

        $stuck = $visits
            ->where('registered', false)
            ->groupBy(fn (EventUrlVisit $v) => $v->last_step ?: 'entry')
            ->map->count()
            ->sortDesc()
            ->all();

        $groupCount = fn (Collection $items, string $key): array => $items
            ->groupBy(fn (EventUrlVisit $v) => $v->{$key} ?: 'Unknown')
            ->map->count()
            ->sortDesc()
            ->take(10)
            ->all();

        $fromMonth = $from
            ? Carbon::parse($from)->startOfMonth()
            : ($visits->min('started_at')
                ? Carbon::parse($visits->min('started_at'))->startOfMonth()
                : now()->subMonths(5)->startOfMonth());
        $toMonth = $to
            ? Carbon::parse($to)->startOfMonth()
            : now()->startOfMonth();

        $monthlyMap = $visits->groupBy(function (EventUrlVisit $v) {
            $startedAt = $v->started_at;

            return $startedAt ? Carbon::parse($startedAt)->format('Y-m') : 'unknown';
        });

        $monthly = [];
        for ($month = $fromMonth->copy(); $month->lte($toMonth); $month->addMonth()) {
            $key = $month->format('Y-m');
            $bucket = $monthlyMap->get($key, collect());
            $monthly[] = [
                'month' => $key,
                'label' => $month->format('M Y'),
                'visits' => $bucket->count(),
                'registrations' => $bucket->where('registered', true)->count(),
            ];
        }

        return [
            'visits' => $visitCount,
            'unique_visitors' => $unique,
            'registrations' => $registrations,
            'conversion_rate' => $visitCount > 0 ? round(($registrations / $visitCount) * 100, 1) : 0.0,
            'bounce_rate' => $visitCount > 0 ? round(($bounces / $visitCount) * 100, 1) : 0.0,
            'avg_duration_seconds' => $avgDuration,
            'total_pageviews' => $pageviews,
            'funnel' => $funnel,
            'stuck' => $stuck,
            'devices' => $groupCount($visits, 'device_type'),
            'browsers' => $groupCount($visits, 'browser'),
            'platforms' => $groupCount($visits, 'platform'),
            'utm_sources' => $groupCount($visits->filter(fn ($v) => filled($v->utm_source)), 'utm_source'),
            'countries' => $groupCount($visits->filter(fn ($v) => filled($v->country_code)), 'country_code'),
            'monthly' => $monthly,
            'current_month' => $this->currentMonthTrend($eventUrl),
        ];
    }

    /**
     * @return array{
     *   month: string,
     *   label: string,
     *   visits: int,
     *   registrations: int,
     *   days: list<array{date: string, label: string, visits: int, registrations: int}>
     * }
     */
    private function currentMonthTrend(EventUrl $eventUrl): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $through = now()->lt($monthEnd) ? now()->startOfDay() : $monthEnd->copy()->startOfDay();

        $visits = EventUrlVisit::query()
            ->where('event_url_id', $eventUrl->id)
            ->whereBetween('started_at', [$monthStart, $monthEnd])
            ->get();

        $dailyMap = $visits->groupBy(fn (EventUrlVisit $v) => optional($v->started_at)->toDateString() ?: 'unknown');
        $days = [];
        for ($day = $monthStart->copy(); $day->lte($through); $day->addDay()) {
            $key = $day->toDateString();
            $bucket = $dailyMap->get($key, collect());
            $days[] = [
                'date' => $key,
                'label' => $day->format('j'),
                'visits' => $bucket->count(),
                'registrations' => $bucket->where('registered', true)->count(),
            ];
        }

        return [
            'month' => $monthStart->format('Y-m'),
            'label' => $monthStart->format('F Y'),
            'visits' => $visits->count(),
            'registrations' => $visits->where('registered', true)->count(),
            'days' => $days,
        ];
    }
    /**
     * Aggregate URL traffic for the admin dashboard.
     *
     * @return array{
     *   enabled: bool,
     *   total_visits: int,
     *   total_pageviews: int,
     *   unique_visitors: int,
     *   registrations: int,
     *   conversion_rate: float,
     *   today_visits: int,
     *   urls: list<array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     type: string,
     *     full_url: string,
     *     visits: int,
     *     pageviews: int,
     *     unique_visitors: int,
     *     registrations: int,
     *     conversion_rate: float
     *   }>
     * }
     */
    public function dashboardSummary(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $empty = [
            'enabled' => false,
            'total_visits' => 0,
            'total_pageviews' => 0,
            'unique_visitors' => 0,
            'registrations' => 0,
            'conversion_rate' => 0.0,
            'today_visits' => 0,
            'urls' => [],
        ];

        if (! Schema::hasTable('event_url_visits') || ! Schema::hasTable('event_urls')) {
            return $empty;
        }

        $from ??= now()->subDays(29)->startOfDay();
        $to ??= now()->endOfDay();

        $eventId = (int) config('event.event_id');
        $orgId = (int) config('event.org_id');

        $urls = EventUrl::query()
            ->where('event_id', $eventId)
            ->where('organization_id', $orgId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        if ($urls->isEmpty()) {
            return array_merge($empty, ['enabled' => true]);
        }

        $statsByUrl = EventUrlVisit::query()
            ->whereIn('event_url_id', $urls->modelKeys())
            ->whereBetween('started_at', [$from, $to])
            ->select([
                'event_url_id',
                DB::raw('COUNT(*) as visits'),
                DB::raw('COALESCE(SUM(pageview_count), 0) as pageviews'),
                DB::raw('COUNT(DISTINCT visitor_uuid) as unique_visitors'),
                DB::raw('SUM(CASE WHEN registered = 1 THEN 1 ELSE 0 END) as registrations'),
            ])
            ->groupBy('event_url_id')
            ->get()
            ->keyBy('event_url_id');

        $todayVisits = (int) EventUrlVisit::query()
            ->whereIn('event_url_id', $urls->modelKeys())
            ->whereDate('started_at', today())
            ->count();

        $rows = [];
        $totalVisits = 0;
        $totalPageviews = 0;
        $totalRegistrations = 0;
        $uniqueVisitorIds = EventUrlVisit::query()
            ->whereIn('event_url_id', $urls->modelKeys())
            ->whereBetween('started_at', [$from, $to])
            ->distinct()
            ->count('visitor_uuid');

        foreach ($urls as $url) {
            $row = $statsByUrl->get($url->id);
            $visits = (int) ($row->visits ?? 0);
            $pageviews = (int) ($row->pageviews ?? 0);
            $registrations = (int) ($row->registrations ?? 0);
            $unique = (int) ($row->unique_visitors ?? 0);

            $totalVisits += $visits;
            $totalPageviews += $pageviews;
            $totalRegistrations += $registrations;

            $rows[] = [
                'id' => $url->id,
                'name' => $url->name,
                'slug' => $url->slug,
                'type' => $url->type,
                'full_url' => $url->full_url,
                'visits' => $visits,
                'pageviews' => $pageviews,
                'unique_visitors' => $unique,
                'registrations' => $registrations,
                'conversion_rate' => $visits > 0 ? round(($registrations / $visits) * 100, 1) : 0.0,
            ];
        }

        usort($rows, fn (array $a, array $b) => $b['visits'] <=> $a['visits']);

        return [
            'enabled' => true,
            'total_visits' => $totalVisits,
            'total_pageviews' => $totalPageviews,
            'unique_visitors' => $uniqueVisitorIds,
            'registrations' => $totalRegistrations,
            'conversion_rate' => $totalVisits > 0 ? round(($totalRegistrations / $totalVisits) * 100, 1) : 0.0,
            'today_visits' => $todayVisits,
            'urls' => $rows,
        ];
    }

    public function queueCookies(string $visitorUuid, string $sessionKey): void
    {
        cookie()->queue(cookie(self::COOKIE_VISITOR, $visitorUuid, 60 * 24 * 365, '/', null, request()->isSecure(), false, false, 'Lax'));
        cookie()->queue(cookie(self::COOKIE_SESSION, $sessionKey, 60 * 24, '/', null, request()->isSecure(), false, false, 'Lax'));
    }

    public function visitorUuid(Request $request, ?string $provided = null): string
    {
        foreach ([$provided, $request->cookie(self::COOKIE_VISITOR)] as $value) {
            if (is_string($value) && Str::isUuid($value)) {
                return $value;
            }
        }

        return (string) Str::uuid();
    }

    public function sessionKey(Request $request, ?string $provided = null): string
    {
        foreach ([$provided, $request->cookie(self::COOKIE_SESSION)] as $value) {
            if (is_string($value) && preg_match('/^[a-f0-9]{32,64}$/i', $value)) {
                return strtolower($value);
            }
        }

        return bin2hex(random_bytes(16));
    }

    /**
     * @return array{browser: string, browser_version: string|null, platform: string, device_type: string}
     */
    public function parseUserAgent(?string $ua): array
    {
        $ua = (string) $ua;
        $device = 'desktop';
        if (preg_match('/iPad|Tablet|PlayBook/i', $ua)) {
            $device = 'tablet';
        } elseif (preg_match('/Mobile|Android|iPhone|iPod|IEMobile|BlackBerry/i', $ua)) {
            $device = 'mobile';
        }

        $browser = 'Other';
        $version = null;
        $patterns = [
            'Edge' => '/Edg(?:e|A|iOS)?\/([0-9.]+)/',
            'Opera' => '/(?:OPR|Opera)\/([0-9.]+)/',
            'Chrome' => '/Chrome\/([0-9.]+)/',
            'Firefox' => '/Firefox\/([0-9.]+)/',
            'Safari' => '/Version\/([0-9.]+).*Safari/',
            'IE' => '/(?:MSIE |rv:)([0-9.]+)/',
        ];
        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $ua, $matches)) {
                $browser = $name;
                $version = $matches[1] ?? null;
                break;
            }
        }

        $platform = 'Other';
        if (preg_match('/Windows NT/i', $ua)) {
            $platform = 'Windows';
        } elseif (preg_match('/Android/i', $ua)) {
            $platform = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $platform = 'iOS';
        } elseif (preg_match('/Mac OS X/i', $ua)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $platform = 'Linux';
        }

        return [
            'browser' => $browser,
            'browser_version' => $version ? Str::limit($version, 32, '') : null,
            'platform' => $platform,
            'device_type' => $device,
        ];
    }

    private function stepRank(?string $step): int
    {
        if (! $step) {
            return 0;
        }

        return self::STEP_ORDER[$step] ?? 0;
    }

    private function utm(Request $request, array $client, string $key): ?string
    {
        $value = $client[$key] ?? $request->query($key);
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Str::limit(trim($value), 255, '');
    }

    private function countryCode(Request $request): ?string
    {
        $headers = ['CF-IPCountry', 'CloudFront-Viewer-Country', 'X-Country-Code'];
        foreach ($headers as $header) {
            $value = $request->headers->get($header);
            if (is_string($value) && preg_match('/^[A-Z]{2}$/i', $value) && strtoupper($value) !== 'XX') {
                return strtoupper($value);
            }
        }

        return null;
    }
}
