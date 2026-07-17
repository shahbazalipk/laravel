@extends('admin.layout')

@section('title', 'Sales Dashboard')

@section('content')
<div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Sales</p>
    <h1 class="mt-1 text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="mt-2 text-gray-600">Overview of pipelines, deals, and inquiry submissions.</p>
</div>

<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" data-testid="sales-dashboard-metrics">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-gray-500">Pipelines</p>
        <p class="mt-2 text-3xl font-bold text-gray-900" data-testid="metric-pipeline-count">{{ $pipelineCount }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-gray-500">Open deals value</p>
        <p class="mt-2 text-3xl font-bold text-indigo-600" data-testid="metric-open-deals-value">{{ format_money($openDealsValue) }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-gray-500">Won deals value</p>
        <p class="mt-2 text-3xl font-bold text-emerald-600" data-testid="metric-won-deals-value">{{ format_money($wonDealsValue) }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-gray-500">Lost deals value</p>
        <p class="mt-2 text-3xl font-bold text-rose-600" data-testid="metric-lost-deals-value">{{ format_money($lostDealsValue) }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-gray-500">New submissions</p>
        <p class="mt-2 text-3xl font-bold text-amber-600" data-testid="metric-new-submissions">{{ $newSubmissions }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-gray-500">Submission conversion</p>
        <p class="mt-2 text-3xl font-bold text-sky-600" data-testid="metric-conversion-rate">{{ $conversionRate }}%</p>
    </div>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="recent-deals">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Recent deals</h2>
            <a href="{{ route('admin.sales.deals.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View all</a>
        </div>
        @forelse($recentDeals as $deal)
            <a href="{{ route('admin.sales.deals.show', $deal) }}" class="flex items-center justify-between border-b border-gray-100 py-3 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
                <div>
                    <p class="font-semibold text-gray-900">{{ $deal->title }}</p>
                    <p class="text-xs text-gray-500">{{ $deal->reference }} · {{ $deal->pipeline?->name }}</p>
                </div>
                <span class="text-sm font-semibold text-gray-700">{{ format_money($deal->value) }}</span>
            </a>
        @empty
            <p class="text-sm text-gray-500">No deals yet.</p>
        @endforelse
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="recent-submissions">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Recent submissions</h2>
            <a href="{{ route('admin.sales.submissions.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View all</a>
        </div>
        @forelse($recentSubmissions as $submission)
            <a href="{{ route('admin.sales.submissions.show', $submission) }}" class="flex items-center justify-between border-b border-gray-100 py-3 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
                <div>
                    <p class="font-semibold text-gray-900">{{ $submission->submitter_name ?: $submission->reference }}</p>
                    <p class="text-xs text-gray-500">{{ $submission->form?->name }}</p>
                </div>
                <span class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">{{ $submission->status->label() }}</span>
            </a>
        @empty
            <p class="text-sm text-gray-500">No submissions yet.</p>
        @endforelse
    </section>
</div>
@endsection
