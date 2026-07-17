@extends('admin.layout')

@section('title', 'Pipeline Types')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Sales</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Pipeline Types</h1>
        <p class="mt-2 text-gray-600">Reusable templates for stages and custom fields.</p>
    </div>
    <a href="{{ route('admin.sales.pipeline-types.create') }}"
       class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-indigo-700"
       data-testid="create-pipeline-type">+ New type</a>
</div>

<form method="GET" class="mb-6 flex flex-wrap gap-3">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search types..."
           class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
           data-testid="pipeline-types-search">
    <select name="status" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm" data-testid="pipeline-types-status-filter">
        <option value="">All statuses</option>
        <option value="active" @selected(request('status') === 'active')>Active</option>
        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
    </select>
    <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Filter</button>
</form>

@if($types->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center shadow-sm" data-testid="pipeline-types-empty">
        <h2 class="text-xl font-bold text-gray-900">No pipeline types yet</h2>
        <p class="mt-2 text-gray-600">Create a type with stages and custom fields, then spin up pipelines from it.</p>
    </div>
@else
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" data-testid="pipeline-types-list">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Stages</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fields</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pipelines</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($types as $type)
                    <tr data-testid="pipeline-type-row-{{ $type->public_id }}">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.sales.pipeline-types.show', $type) }}" class="font-semibold text-gray-900 hover:text-indigo-600">{{ $type->name }}</a>
                            <p class="font-mono text-xs text-gray-500">{{ $type->slug }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $type->stages_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $type->fields_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $type->pipelines_count }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $type->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('admin.sales.pipeline-types.edit', $type) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $types->links() }}</div>
@endif
@endsection
