@extends('admin.layout')

@section('title', 'URL Analytics')

@section('content')
@php
    $avgMinutes = intdiv((int) $summary['avg_duration_seconds'], 60);
    $avgSeconds = ((int) $summary['avg_duration_seconds']) % 60;
    $avgLabel = $avgMinutes > 0
        ? $avgMinutes.'m '.str_pad((string) $avgSeconds, 2, '0', STR_PAD_LEFT).'s'
        : ((int) $summary['avg_duration_seconds']).'s';
@endphp

<div class="mb-6" data-testid="event-url-stats-page">
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-3">
            <a href="{{ route('admin.event-urls.index') }}"
               class="mt-1 text-gray-600 hover:text-gray-900"
               aria-label="Back to event URLs">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Event URL analytics</p>
                <h1 class="text-2xl font-bold text-gray-800">{{ $eventUrl->name }}</h1>
                <p class="mt-1 font-mono text-sm text-gray-500">{{ $eventUrl->full_url }}</p>
            </div>
        </div>
        <a href="{{ route('admin.event-urls.edit', $eventUrl) }}"
           class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Edit URL
        </a>
    </div>

    <form method="GET"
          action="{{ route('admin.event-urls.stats', $eventUrl) }}"
          class="mb-6 grid grid-cols-1 gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5"
          data-testid="event-url-stats-filters">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">Status</label>
            <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="">All visits</option>
                <option value="registered" {{ $status === 'registered' ? 'selected' : '' }}>Registered</option>
                <option value="abandoned" {{ $status === 'abandoned' ? 'selected' : '' }}>Abandoned</option>
                <option value="bounce" {{ $status === 'bounce' ? 'selected' : '' }}>Bounces</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">Device</label>
            <select name="device" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="">All devices</option>
                <option value="desktop" {{ $device === 'desktop' ? 'selected' : '' }}>Desktop</option>
                <option value="mobile" {{ $device === 'mobile' ? 'selected' : '' }}>Mobile</option>
                <option value="tablet" {{ $device === 'tablet' ? 'selected' : '' }}>Tablet</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Apply</button>
            <a href="{{ route('admin.event-urls.stats', $eventUrl) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" data-testid="event-url-stats-summary">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Visits</p>
            <p class="mt-2 text-3xl font-bold text-gray-900" data-testid="stats-visits">{{ number_format($summary['visits']) }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ number_format($summary['unique_visitors']) }} unique visitors</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Registrations</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700" data-testid="stats-registrations">{{ number_format($summary['registrations']) }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $summary['conversion_rate'] }}% conversion</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Avg. time spent</p>
            <p class="mt-2 text-3xl font-bold text-indigo-700" data-testid="stats-avg-duration">{{ $avgLabel }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ number_format($summary['total_pageviews']) }} page views</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Bounce rate</p>
            <p class="mt-2 text-3xl font-bold text-amber-700" data-testid="stats-bounce-rate">{{ $summary['bounce_rate'] }}%</p>
            <p class="mt-1 text-xs text-gray-500">Single short visits</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm" data-testid="event-url-funnel">
            <h2 class="text-lg font-semibold text-gray-800">Registration funnel</h2>
            <p class="mt-1 text-sm text-gray-500">How far visitors progressed</p>
            <div class="mt-4 space-y-3">
                @php $funnelMax = max(1, max($summary['funnel'] ?: [0])); @endphp
                @foreach($summary['funnel'] as $step => $count)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="font-medium capitalize text-gray-700">{{ str_replace('_', ' ', $step) }}</span>
                            <span class="text-gray-500">{{ number_format($count) }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ round(($count / $funnelMax) * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm" data-testid="event-url-stuck">
            <h2 class="text-lg font-semibold text-gray-800">Where visitors stuck</h2>
            <p class="mt-1 text-sm text-gray-500">Last step for abandoned sessions</p>
            @if(empty($summary['stuck']))
                <p class="mt-6 text-sm text-gray-500">No abandoned sessions in this range.</p>
            @else
                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach($summary['stuck'] as $step => $count)
                        <li class="flex items-center justify-between py-2.5 text-sm">
                            <span class="capitalize text-gray-700">{{ str_replace('_', ' ', $step) }}</span>
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ number_format($count) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        @foreach([
            'devices' => 'Devices',
            'browsers' => 'Browsers',
            'platforms' => 'Platforms',
        ] as $key => $label)
            <div class="rounded-xl bg-white p-5 shadow-sm" data-testid="event-url-{{ $key }}">
                <h2 class="text-sm font-semibold text-gray-800">{{ $label }}</h2>
                @if(empty($summary[$key]))
                    <p class="mt-4 text-sm text-gray-500">No data yet.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach($summary[$key] as $name => $count)
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-700">{{ $name }}</span>
                                <span class="font-medium text-gray-900">{{ number_format($count) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    @if(!empty($summary['utm_sources']) || !empty($summary['countries']))
        <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-800">UTM sources</h2>
                @if(empty($summary['utm_sources']))
                    <p class="mt-4 text-sm text-gray-500">No UTM traffic in this range.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach($summary['utm_sources'] as $name => $count)
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-700">{{ $name }}</span>
                                <span class="font-medium text-gray-900">{{ number_format($count) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-800">Countries</h2>
                @if(empty($summary['countries']))
                    <p class="mt-4 text-sm text-gray-500">No country headers available.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach($summary['countries'] as $name => $count)
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-700">{{ $name }}</span>
                                <span class="font-medium text-gray-900">{{ number_format($count) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    <div class="mb-6 rounded-xl bg-white p-5 shadow-sm" data-testid="event-url-trend-tabs">
        <div class="mb-4 flex flex-col gap-3 border-b border-gray-100 pb-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Traffic trends</h2>
                <p class="mt-1 text-sm text-gray-500">Switch between long-range monthly view and this month’s daily activity</p>
            </div>
            <div class="inline-flex rounded-lg bg-slate-100 p-1" role="tablist" aria-label="Traffic trend period">
                <button type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition"
                        role="tab"
                        aria-selected="true"
                        data-trend-tab="monthly"
                        data-testid="event-url-tab-monthly">
                    Monthly trend
                </button>
                <button type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition"
                        role="tab"
                        aria-selected="false"
                        data-trend-tab="current"
                        data-testid="event-url-tab-current-month">
                    {{ $summary['current_month']['label'] }}
                </button>
            </div>
        </div>

        <div data-trend-panel="monthly" data-testid="event-url-monthly">
            <p class="text-sm text-gray-500">Visits and registrations by month for the selected range</p>
            @if(empty($summary['monthly']))
                <p class="mt-8 text-center text-sm text-gray-500">No visit data in this range.</p>
            @else
                <div class="relative mt-4 h-72" data-testid="event-url-monthly-chart">
                    <canvas id="eventUrlMonthlyChart" aria-label="Monthly visits and registrations chart"></canvas>
                </div>
            @endif
        </div>

        <div class="hidden" data-trend-panel="current" data-testid="event-url-current-month">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <p class="text-sm text-gray-500">Daily visits and registrations in {{ $summary['current_month']['label'] }}</p>
                <p class="text-sm text-gray-600">
                    <span class="font-semibold text-indigo-700">{{ number_format($summary['current_month']['visits']) }}</span> visits ·
                    <span class="font-semibold text-emerald-700">{{ number_format($summary['current_month']['registrations']) }}</span> registrations
                </p>
            </div>
            @if(empty($summary['current_month']['days']))
                <p class="mt-8 text-center text-sm text-gray-500">No days to display for the current month.</p>
            @else
                <div class="relative mt-4 h-72" data-testid="event-url-current-month-chart">
                    <canvas id="eventUrlCurrentMonthChart" aria-label="Current month daily visits chart"></canvas>
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm" data-testid="event-url-visits-table">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Recent visits</h2>
            <p class="mt-1 text-sm text-gray-500">IP, browser, time spent, and where they stopped</p>
        </div>
        @if($visits->isEmpty())
            <div class="px-5 py-12 text-center text-sm text-gray-500">No visits match these filters.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">When</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Visitor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Device</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Last step</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Result</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($visits as $visit)
                            <tr class="hover:bg-gray-50" data-testid="event-url-visit-{{ $visit->id }}">
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $visit->started_at?->format('M d, Y H:i') ?? '—' }}
                                    @if($visit->utm_source)
                                        <span class="mt-1 block text-xs text-indigo-600">utm: {{ $visit->utm_source }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-mono text-xs text-gray-800">{{ $visit->ip_address ?: '—' }}</div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $visit->country_code ?: '—' }}
                                        @if($visit->language) · {{ $visit->language }} @endif
                                    </div>
                                    @if($visit->referrer)
                                        <div class="mt-1 max-w-xs truncate text-xs text-gray-400" title="{{ $visit->referrer }}">{{ $visit->referrer }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <div>{{ $visit->device_type ?: '—' }} · {{ $visit->browser ?: '—' }}{{ $visit->browser_version ? ' '.$visit->browser_version : '' }}</div>
                                    <div class="text-xs text-gray-500">{{ $visit->platform ?: '—' }}@if($visit->screen_size) · {{ $visit->screen_size }}@endif</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $visit->durationLabel() }}
                                    <div class="text-xs text-gray-500">{{ $visit->pageview_count }} views</div>
                                </td>
                                <td class="px-4 py-3 text-sm capitalize text-gray-700">
                                    {{ str_replace('_', ' ', $visit->last_step ?: 'entry') }}
                                    <div class="text-xs text-gray-500">farthest: {{ str_replace('_', ' ', $visit->farthest_step ?: 'entry') }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($visit->registered)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Registered</span>
                                        @if($visit->registration)
                                            <a href="{{ route('admin.registrations.show', $visit->registration) }}"
                                               class="mt-1 block text-xs font-medium text-indigo-600 hover:text-indigo-800">View registration</a>
                                        @endif
                                    @elseif($visit->is_bounce)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Bounce</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Abandoned</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $visits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let currentMonthChart = null;

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12, padding: 16 },
            },
        },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 } },
        },
    };

    @if(!empty($summary['monthly']))
    const monthlyCanvas = document.getElementById('eventUrlMonthlyChart');
    if (monthlyCanvas && typeof Chart !== 'undefined') {
        new Chart(monthlyCanvas, {
            type: 'bar',
            data: {
                labels: @json(collect($summary['monthly'])->pluck('label')),
                datasets: [
                    {
                        label: 'Visits',
                        data: @json(collect($summary['monthly'])->pluck('visits')),
                        backgroundColor: 'rgba(99, 102, 241, 0.85)',
                        borderRadius: 6,
                        maxBarThickness: 42,
                    },
                    {
                        label: 'Registrations',
                        data: @json(collect($summary['monthly'])->pluck('registrations')),
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        borderRadius: 6,
                        maxBarThickness: 42,
                    },
                ],
            },
            options: chartOptions,
        });
    }
    @endif

    @if(!empty($summary['current_month']['days']))
    const currentMonthCanvas = document.getElementById('eventUrlCurrentMonthChart');
    if (currentMonthCanvas && typeof Chart !== 'undefined') {
        currentMonthChart = new Chart(currentMonthCanvas, {
            type: 'line',
            data: {
                labels: @json(collect($summary['current_month']['days'])->pluck('label')),
                datasets: [
                    {
                        label: 'Visits',
                        data: @json(collect($summary['current_month']['days'])->pluck('visits')),
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 2,
                    },
                    {
                        label: 'Registrations',
                        data: @json(collect($summary['current_month']['days'])->pluck('registrations')),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 2,
                    },
                ],
            },
            options: chartOptions,
        });
    }
    @endif

    (function () {
        const tabs = document.querySelectorAll('[data-trend-tab]');
        const panels = document.querySelectorAll('[data-trend-panel]');

        const activate = (name) => {
            tabs.forEach((tab) => {
                const active = tab.getAttribute('data-trend-tab') === name;
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
                tab.classList.toggle('bg-white', active);
                tab.classList.toggle('text-indigo-700', active);
                tab.classList.toggle('shadow-sm', active);
                tab.classList.toggle('text-slate-600', !active);
            });

            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.getAttribute('data-trend-panel') !== name);
            });

            if (name === 'current' && currentMonthChart) {
                currentMonthChart.resize();
            }
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activate(tab.getAttribute('data-trend-tab')));
        });

        activate('monthly');
    })();
</script>
@endsection
