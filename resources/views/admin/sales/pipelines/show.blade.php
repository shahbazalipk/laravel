@extends('admin.layout')

@section('title', $pipeline->name)

@section('content')
@php
    $tab = request('tab', 'overview');
@endphp
<div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Pipeline</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">{{ $pipeline->name }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $pipeline->type?->name }} · {{ $pipeline->status->label() }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.sales.pipelines.kanban', $pipeline) }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700" data-testid="pipeline-kanban-link">Kanban board</a>
        <a href="{{ route('admin.sales.deals.create', ['pipeline' => $pipeline->public_id]) }}" class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700">+ New deal</a>
        <form action="{{ route('admin.sales.pipelines.archive', $pipeline) }}" method="POST">@csrf<button type="submit" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Archive</button></form>
    </div>
</div>

<nav class="mb-6 flex gap-1 rounded-xl border border-gray-200 bg-gray-50 p-1" data-testid="pipeline-tabs">
    @foreach(['overview' => 'Overview', 'deals' => 'Deals', 'settings' => 'Settings'] as $key => $label)
        <a href="{{ route('admin.sales.pipelines.show', [$pipeline, 'tab' => $key]) }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold {{ $tab === $key ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
           data-testid="pipeline-tab-{{ $key }}">{{ $label }}</a>
    @endforeach
</nav>

@if($tab === 'overview')
    <div class="grid gap-6 lg:grid-cols-3" data-testid="pipeline-overview">
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Stages</h2>
            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach($pipeline->stages as $stage)
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $stage->name }}</p>
                        <p class="text-xs text-gray-500">{{ $pipeline->deals->where('sales_pipeline_stage_id', $stage->id)->count() }} deals · {{ $stage->probability }}%</p>
                    </div>
                @endforeach
            </div>
        </div>
        <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-gray-500">Deals</dt><dd class="font-bold text-gray-900">{{ $pipeline->deals->count() }}</dd></div>
                <div><dt class="text-gray-500">Revenue target</dt><dd class="font-bold text-gray-900">{{ format_money($pipeline->revenue_target) }}</dd></div>
                <div><dt class="text-gray-500">Created</dt><dd>{{ $pipeline->created_at->format('M j, Y') }}</dd></div>
            </dl>
        </aside>
    </div>
@elseif($tab === 'deals')
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" data-testid="pipeline-deals">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Deal</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Stage</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Value</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pipeline->deals as $deal)
                    <tr>
                        <td class="px-6 py-4"><a href="{{ route('admin.sales.deals.show', $deal) }}" class="font-semibold text-indigo-600">{{ $deal->title }}</a></td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $deal->stage?->name }}</td>
                        <td class="px-6 py-4 text-sm font-semibold">{{ format_money($deal->value) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-12 text-center text-gray-500">No deals in this pipeline.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@else
    <div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="pipeline-settings">
        <p class="text-sm text-gray-600">{{ $pipeline->description ?: 'No description.' }}</p>
        <div class="mt-4"><a href="{{ route('admin.sales.pipelines.edit', $pipeline) }}" class="text-sm font-semibold text-indigo-600">Edit pipeline settings</a></div>
    </div>
@endif
@endsection
