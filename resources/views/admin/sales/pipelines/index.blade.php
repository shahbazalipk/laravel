@extends('admin.layout')

@section('title', 'Pipelines')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Sales</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Pipelines</h1>
    </div>
    <a href="{{ route('admin.sales.pipelines.create') }}" class="rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="create-pipeline">+ New pipeline</a>
</div>

<form method="GET" class="mb-6 flex flex-wrap gap-3">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search..." class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
    <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Filter</button>
</form>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" data-testid="pipelines-list">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50"><tr>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Name</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Type</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Deals</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
            <th class="px-6 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($pipelines as $pipeline)
                <tr data-testid="pipeline-row-{{ $pipeline->public_id }}">
                    <td class="px-6 py-4"><a href="{{ route('admin.sales.pipelines.show', $pipeline) }}" class="font-semibold text-gray-900 hover:text-indigo-600">{{ $pipeline->name }}</a></td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $pipeline->type?->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $pipeline->deals_count }}</td>
                    <td class="px-6 py-4"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $pipeline->status->label() }}</span></td>
                    <td class="px-6 py-4 text-right"><a href="{{ route('admin.sales.pipelines.kanban', $pipeline) }}" class="text-sm font-semibold text-indigo-600">Kanban</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No pipelines yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $pipelines->links() }}</div>
@endsection
