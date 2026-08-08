@extends('admin.layout')

@section('title', 'Category Report')

@section('content')
@php $currency = $report['totals']['currency']; @endphp

<div class="mb-6" data-testid="reports-categories-page">
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← All reports</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Category report</h1>
            <p class="mt-1 text-sm text-gray-600">Registration volume and revenue by category</p>
        </div>
        <a href="{{ route('admin.reports.categories.export', ['from' => $from, 'to' => $to]) }}"
           class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
           data-testid="reports-categories-export">
            Export CSV
        </a>
    </div>

    <form method="GET" action="{{ route('admin.reports.categories') }}" class="mb-6 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm sm:flex-row sm:items-end" data-testid="reports-categories-filters">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Apply</button>
        <a href="{{ route('admin.reports.categories') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reset</a>
    </form>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" data-testid="reports-categories-summary">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Registrations</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($report['totals']['registrations']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Revenue</p>
            <p class="mt-2 text-3xl font-bold text-indigo-700">{{ number_format($report['totals']['revenue'], 2) }} <span class="text-base font-medium">{{ $currency }}</span></p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Paid</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($report['totals']['paid_count']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Pending</p>
            <p class="mt-2 text-3xl font-bold text-amber-700">{{ number_format($report['totals']['pending_count']) }}</p>
            <p class="mt-1 text-xs text-gray-500">Discounts {{ number_format($report['totals']['discounts'], 2) }} {{ $currency }}</p>
        </div>
    </div>

    @if(!empty($report['rows']))
        <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-2" data-testid="reports-categories-charts">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800">Registrations by category</h2>
                <div class="relative mx-auto mt-4 h-64 max-w-md">
                    <canvas id="categoryRegsChart" aria-label="Registrations by category chart"></canvas>
                </div>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800">Revenue by category</h2>
                <div class="relative mt-4 h-64">
                    <canvas id="categoryRevenueChart" aria-label="Revenue by category chart"></canvas>
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" data-testid="reports-categories-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Category</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Regs</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Pending</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Checked in</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Discounts</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($report['rows'] as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['category_name'] }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-800">{{ number_format($row['registrations']) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-emerald-700">{{ number_format($row['paid']) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-amber-700">{{ number_format($row['pending']) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['checked_in']) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['discount_sum'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-indigo-700">{{ number_format($row['revenue'], 2) }} {{ $row['currency'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">No registrations in this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(!empty($report['rows']))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const categoryLabels = @json(collect($report['rows'])->pluck('category_name'));
    const categoryRegs = @json(collect($report['rows'])->pluck('registrations'));
    const categoryRevenue = @json(collect($report['rows'])->pluck('revenue'));
    const palette = [
        'rgb(99, 102, 241)', 'rgb(16, 185, 129)', 'rgb(245, 158, 11)', 'rgb(236, 72, 153)',
        'rgb(14, 165, 233)', 'rgb(168, 85, 247)', 'rgb(239, 68, 68)', 'rgb(100, 116, 139)',
    ];

    const regsCanvas = document.getElementById('categoryRegsChart');
    if (regsCanvas && typeof Chart !== 'undefined') {
        new Chart(regsCanvas, {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{ data: categoryRegs, backgroundColor: categoryLabels.map((_, i) => palette[i % palette.length]), borderWidth: 0 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
            },
        });
    }

    const revenueCanvas = document.getElementById('categoryRevenueChart');
    if (revenueCanvas && typeof Chart !== 'undefined') {
        new Chart(revenueCanvas, {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Revenue',
                    data: categoryRevenue,
                    backgroundColor: 'rgba(99, 102, 241, 0.85)',
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }
</script>
@endif
@endsection
