@extends('admin.layout')

@section('title', 'Payment Status Report')

@section('content')
@php $currency = $report['currency']; @endphp

<div class="mb-6" data-testid="reports-payment-status-page">
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← All reports</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Payment status report</h1>
            <p class="mt-1 text-sm text-gray-600">Registrations grouped by payment status with paid and pending amounts</p>
        </div>
        <a href="{{ route('admin.reports.payment-status.export', ['from' => $from, 'to' => $to]) }}"
           class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
           data-testid="reports-payment-status-export">
            Export CSV
        </a>
    </div>

    <form method="GET" action="{{ route('admin.reports.payment-status') }}" class="mb-6 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm sm:flex-row sm:items-end" data-testid="reports-payment-status-filters">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Apply</button>
        <a href="{{ route('admin.reports.payment-status') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reset</a>
    </form>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" data-testid="reports-payment-status-summary">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Registrations</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($report['totals']['registrations']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total price</p>
            <p class="mt-2 text-3xl font-bold text-indigo-700">{{ number_format($report['totals']['total_price'], 2) }} <span class="text-base font-medium">{{ $currency }}</span></p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Paid</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($report['totals']['paid'], 2) }} <span class="text-base font-medium">{{ $currency }}</span></p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Pending</p>
            <p class="mt-2 text-3xl font-bold text-amber-700">{{ number_format($report['totals']['pending'], 2) }} <span class="text-base font-medium">{{ $currency }}</span></p>
        </div>
    </div>

    @forelse($report['groups'] as $group)
        <section class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm" data-testid="reports-payment-status-group-{{ $group['status'] }}">
            <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $group['badge_classes'] }}">
                        {{ $group['label'] }}
                    </span>
                    <span class="text-sm text-gray-600">{{ number_format($group['count']) }} registration{{ $group['count'] === 1 ? '' : 's' }}</span>
                </div>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span>Total <strong class="text-gray-900">{{ number_format($group['total_price'], 2) }} {{ $currency }}</strong></span>
                    <span>Paid <strong class="text-emerald-700">{{ number_format($group['paid'], 2) }}</strong></span>
                    <span>Pending <strong class="text-amber-700">{{ number_format($group['pending'], 2) }}</strong></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 sm:px-6">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 sm:px-6">Phone</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 sm:px-6">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500 sm:px-6">Total price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500 sm:px-6">Paid</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500 sm:px-6">Pending</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($group['rows'] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm sm:px-6">
                                    <a href="{{ $row['show_url'] }}" class="font-medium text-indigo-700 hover:text-indigo-900">
                                        {{ $row['name'] }}
                                    </a>
                                    @if($row['registration_number'])
                                        <div class="text-xs text-gray-500">{{ $row['registration_number'] }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 sm:px-6">{{ $row['phone'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 sm:px-6">{{ $row['category_name'] }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 sm:px-6">{{ number_format($row['total_price'], 2) }} {{ $row['currency'] }}</td>
                                <td class="px-4 py-3 text-right text-sm text-emerald-700 sm:px-6">{{ number_format($row['paid'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm text-amber-700 sm:px-6">{{ number_format($row['pending'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <div class="rounded-xl bg-white px-6 py-12 text-center shadow-sm">
            <p class="text-sm text-gray-500">No registrations in this date range.</p>
        </div>
    @endforelse
</div>
@endsection
