<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventUrl;
use App\Models\EventUrlVisit;
use App\Services\EventUrlAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventUrlStatsController extends Controller
{
    public function __construct(
        private EventUrlAnalyticsService $analytics,
    ) {}

    public function show(Request $request, EventUrl $eventUrl): View
    {
        $this->assertCurrentTenant($eventUrl);

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->subMonths(11)->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $summary = $this->analytics->summarize($eventUrl, $from, $to);

        $visitsQuery = EventUrlVisit::query()
            ->where('event_url_id', $eventUrl->id)
            ->whereBetween('started_at', [$from, $to])
            ->orderByDesc('started_at');

        if ($request->filled('status')) {
            if ($request->query('status') === 'registered') {
                $visitsQuery->where('registered', true);
            } elseif ($request->query('status') === 'abandoned') {
                $visitsQuery->where('registered', false);
            } elseif ($request->query('status') === 'bounce') {
                $visitsQuery->where('is_bounce', true);
            }
        }

        if ($request->filled('device')) {
            $visitsQuery->where('device_type', $request->query('device'));
        }

        $visits = $visitsQuery->with('registration')->paginate(30)->withQueryString();

        return view('admin.event-urls.stats', [
            'eventUrl' => $eventUrl,
            'summary' => $summary,
            'visits' => $visits,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'status' => (string) $request->query('status', ''),
            'device' => (string) $request->query('device', ''),
        ]);
    }

    private function assertCurrentTenant(EventUrl $eventUrl): void
    {
        abort_unless(
            (int) $eventUrl->event_id === (int) config('event.event_id')
            && (int) $eventUrl->organization_id === (int) config('event.org_id'),
            404
        );
    }
}
