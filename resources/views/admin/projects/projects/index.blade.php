@extends('admin.layout')

@section('title', 'Projects')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Event delivery</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Projects</h1>
        <p class="mt-2 text-sm text-slate-500">Plan and monitor every workstream behind the event.</p>
    </div>
    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CREATE))
        <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">New project</a>
    @endif
</div>

@include('admin.projects._nav')

<form method="GET" class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_12rem_auto]">
    <label class="sr-only" for="project-search">Search projects</label>
    <input id="project-search" name="search" value="{{ request('search') }}" placeholder="Search by name, key or number"
           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
    <label class="sr-only" for="project-status">Status</label>
    <select id="project-status" name="status" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
        <option value="">All statuses</option>
        @foreach(['planned', 'active', 'on_hold', 'completed', 'cancelled'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
        @endforeach
    </select>
    <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800">Filter</button>
</form>

<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-testid="projects-list">
    @forelse($projects as $project)
        <a href="{{ route('admin.projects.show', $project) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">{{ $project->key }}</p>
                    <h2 class="mt-1 truncate text-lg font-bold text-slate-900 group-hover:text-indigo-700">{{ $project->name }}</h2>
                </div>
                <span class="h-3 w-3 flex-none rounded-full ring-4 ring-slate-100" style="background-color: {{ $project->color }}"></span>
            </div>
            <p class="mt-3 line-clamp-2 min-h-10 text-sm leading-5 text-slate-500">{{ $project->description ?: 'No description provided.' }}</p>
            <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-xs font-semibold text-slate-500">
                <span>{{ $project->tasks_count }} tasks</span>
                <span>{{ $project->members_count }} members</span>
                <span class="capitalize">{{ str_replace('_', ' ', $project->status) }}</span>
            </div>
        </a>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center md:col-span-2 xl:col-span-3">
            <p class="font-bold text-slate-800">No matching projects</p>
            <p class="mt-1 text-sm text-slate-500">Adjust the filters or create a new project.</p>
        </div>
    @endforelse
</section>

<div class="mt-6">{{ $projects->links() }}</div>
@endsection
