@extends('admin.layout')

@section('title', 'Deals')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Sales</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Deals</h1>
    </div>
    <a href="{{ route('admin.sales.deals.create') }}" class="rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="create-deal">+ New deal</a>
</div>

<form method="GET" class="mb-6 flex flex-wrap gap-3">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search deals..." class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
    <select name="pipeline_id" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        <option value="">All pipelines</option>
        @foreach($pipelines as $p)
            <option value="{{ $p->id }}" @selected(request('pipeline_id') == $p->id)>{{ $p->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Filter</button>
</form>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" data-testid="deals-list">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50"><tr>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Deal</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pipeline</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Stage</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Value</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($deals as $deal)
                <tr data-testid="deal-row-{{ $deal->public_id }}">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.sales.deals.show', $deal) }}" class="font-semibold text-gray-900 hover:text-indigo-600">{{ $deal->title }}</a>
                        <p class="font-mono text-xs text-gray-500">{{ $deal->reference }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $deal->pipeline?->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $deal->stage?->name }}</td>
                    <td class="px-6 py-4 text-sm font-semibold">{{ format_money($deal->value) }}</td>
                    <td class="px-6 py-4"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold">{{ $deal->status->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No deals yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $deals->links() }}</div>
@endsection
