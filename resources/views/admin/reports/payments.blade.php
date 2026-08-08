@extends('admin.layout')

@section('title', 'Payments Report')

@section('content')
@php $currency = $report['totals']['currency']; @endphp

<div class="mb-6" data-testid="reports-payments-page">
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← All reports</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Payments report</h1>
            <p class="mt-1 text-sm text-gray-600">Collected revenue, pending balances, and payment mix</p>
        </div>
        <a href="{{ route('admin.reports.payments.export', ['from' => $from, 'to' => $to]) }}"
           class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
           data-testid="reports-payments-export">
            Export CSV
        </a>
    </div>

    <form method="GET" action="{{ route('admin.reports.payments') }}" class="mb-6 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm sm:flex-row sm:items-end" data-testid="reports-payments-filters">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Apply</button>
        <a href="{{ route('admin.reports.payments') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reset</a>
    </form>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" data-testid="reports-payments-summary">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total revenue</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($report['totals']['revenue'], 2) }} <span class="text-base">{{ $currency }}</span></p>
            <p class="mt-1 text-xs text-gray-500">{{ number_format($report['totals']['registrations']) }} registrations</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Paid revenue</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($report['totals']['paid_revenue'], 2) }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ number_format($report['totals']['paid_count']) }} paid</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Pending revenue</p>
            <p class="mt-2 text-3xl font-bold text-amber-700">{{ number_format($report['totals']['pending_revenue'], 2) }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ number_format($report['totals']['pending_count']) }} pending</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Discounts / tax</p>
            <p class="mt-2 text-xl font-bold text-indigo-700">{{ number_format($report['totals']['discounts'], 2) }}</p>
            <p class="mt-1 text-xs text-gray-500">Tax {{ number_format($report['totals']['tax'], 2) }} {{ $currency }}</p>
        </div>
    </div>

    @if(!empty($report['by_status']) || !empty($report['daily']))
        <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-2" data-testid="reports-payments-charts">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800">Payment status mix</h2>
                <div class="relative mx-auto mt-4 h-64 max-w-md">
                    <canvas id="paymentStatusChart" aria-label="Payment status chart"></canvas>
                </div>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800">Daily revenue trend</h2>
                <div class="relative mt-4 h-64">
                    <canvas id="paymentDailyChart" aria-label="Daily revenue chart"></canvas>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm" data-testid="reports-payments-by-status">
            <h2 class="text-lg font-semibold text-gray-800">By payment status</h2>
            @if(empty($report['by_status']))
                <p class="mt-6 text-sm text-gray-500">No payment data in this range.</p>
            @else
                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach($report['by_status'] as $row)
                        <li class="flex items-center justify-between py-3 text-sm">
                            <div>
                                <p class="font-medium text-gray-800">{{ $row['label'] }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($row['count']) }} registrations</p>
                            </div>
                            <p class="font-semibold text-gray-900">{{ number_format($row['revenue'], 2) }} {{ $currency }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm" data-testid="reports-payments-by-method">
            <h2 class="text-lg font-semibold text-gray-800">By payment method</h2>
            @if(empty($report['by_method']))
                <p class="mt-6 text-sm text-gray-500">No payment methods recorded.</p>
            @else
                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach($report['by_method'] as $row)
                        <li class="flex items-center justify-between py-3 text-sm">
                            <div>
                                <p class="font-medium capitalize text-gray-800">{{ $row['method'] }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($row['count']) }} registrations</p>
                            </div>
                            <p class="font-semibold text-gray-900">{{ number_format($row['revenue'], 2) }} {{ $currency }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm" data-testid="reports-payments-top-categories">
            <h2 class="text-lg font-semibold text-gray-800">Top categories by revenue</h2>
            @if(empty($report['top_categories']))
                <p class="mt-6 text-sm text-gray-500">No category revenue yet.</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach($report['top_categories'] as $row)
                        <li>
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="font-medium text-gray-800">{{ $row['category_name'] }}</span>
                                <span class="text-gray-700">{{ number_format($row['revenue'], 2) }}</span>
                            </div>
                            <p class="text-xs text-gray-500">{{ number_format($row['registrations']) }} registrations</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm" data-testid="reports-payments-daily">
            <h2 class="text-lg font-semibold text-gray-800">Daily revenue</h2>
            <div class="mt-4 max-h-80 overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-2 py-2">Date</th>
                            <th class="px-2 py-2 text-right">Regs</th>
                            <th class="px-2 py-2 text-right">Revenue</th>
                            <th class="px-2 py-2 text-right">Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_reverse($report['daily']) as $day)
                            <tr class="border-b border-gray-50">
                                <td class="px-2 py-2 text-gray-700">{{ $day['date'] }}</td>
                                <td class="px-2 py-2 text-right text-gray-800">{{ $day['registrations'] }}</td>
                                <td class="px-2 py-2 text-right text-gray-900">{{ number_format($day['revenue'], 2) }}</td>
                                <td class="px-2 py-2 text-right text-emerald-700">{{ number_format($day['paid_revenue'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(!empty($report['by_status']) || !empty($report['daily']))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const palette = [
        'rgb(16, 185, 129)', 'rgb(245, 158, 11)', 'rgb(99, 102, 241)', 'rgb(239, 68, 68)',
        'rgb(14, 165, 233)', 'rgb(168, 85, 247)', 'rgb(100, 116, 139)', 'rgb(236, 72, 153)',
    ];

    const statusLabels = @json(collect($report['by_status'])->pluck('label'));
    const statusCounts = @json(collect($report['by_status'])->pluck('count'));
    const statusCanvas = document.getElementById('paymentStatusChart');
    if (statusCanvas && typeof Chart !== 'undefined' && statusLabels.length) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{ data: statusCounts, backgroundColor: statusLabels.map((_, i) => palette[i % palette.length]), borderWidth: 0 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
            },
        });
    }

    const dailyLabels = @json(collect($report['daily'])->pluck('date'));
    const dailyRevenue = @json(collect($report['daily'])->pluck('revenue'));
    const dailyPaid = @json(collect($report['daily'])->pluck('paid_revenue'));
    const dailyCanvas = document.getElementById('paymentDailyChart');
    if (dailyCanvas && typeof Chart !== 'undefined' && dailyLabels.length) {
        new Chart(dailyCanvas, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [
                    {
                        label: 'Total revenue',
                        data: dailyRevenue,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.12)',
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Paid revenue',
                        data: dailyPaid,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        tension: 0.35,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }
</script>
@endif
@endsection
