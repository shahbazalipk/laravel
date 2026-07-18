@extends('admin.layout')

@section('title', 'Projects Dashboard')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Event delivery</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Project command center</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">Coordinate teams, deadlines and execution from a shared event workspace.</p>
    </div>
    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CREATE))
        <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">New project</a>
    @endif
</div>

@include('admin.projects._nav')

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-testid="projects-dashboard-metrics">
    @foreach([
        ['label' => 'Active projects', 'value' => $metrics['active_projects'], 'tone' => 'bg-indigo-50 text-indigo-700'],
        ['label' => 'Open tasks', 'value' => $metrics['open_tasks'], 'tone' => 'bg-sky-50 text-sky-700'],
        ['label' => 'Overdue tasks', 'value' => $metrics['overdue_tasks'], 'tone' => 'bg-rose-50 text-rose-700'],
        ['label' => 'Completed tasks', 'value' => $metrics['completed_tasks'], 'tone' => 'bg-emerald-50 text-emerald-700'],
    ] as $metric)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">{{ $metric['label'] }}</p>
            <p class="mt-4 inline-flex rounded-xl px-3 py-2 text-3xl font-bold tracking-tight {{ $metric['tone'] }}">{{ number_format($metric['value']) }}</p>
        </article>
    @endforeach
</section>

<div class="mt-7 grid gap-6 xl:grid-cols-3">
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <h2 class="font-bold text-slate-900">Projects</h2>
                <p class="mt-1 text-xs text-slate-500">Current event initiatives and delivery progress</p>
            </div>
            <a href="{{ route('admin.projects.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">View all</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($projects as $project)
                <a href="{{ route('admin.projects.show', $project) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50 sm:px-6">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 flex-none rounded-full" style="background-color: {{ $project->color }}"></span>
                            <p class="truncate font-semibold text-slate-900">{{ $project->name }}</p>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $project->key }} · {{ $project->tasks_count }} tasks</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-600">{{ str_replace('_', ' ', $project->status) }}</span>
                </a>
            @empty
                <div class="px-6 py-12 text-center">
                    <p class="font-semibold text-slate-700">No projects yet</p>
                    <p class="mt-1 text-sm text-slate-500">Create the first project to organize event delivery.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl bg-slate-950 p-5 text-white shadow-sm sm:p-6">
        <h2 class="font-bold">Upcoming deadlines</h2>
        <p class="mt-1 text-xs text-slate-400">Next open tasks by due date</p>
        <div class="mt-5 space-y-3">
            @forelse($upcomingTasks as $task)
                <a href="{{ route('admin.projects.show', $task->project) }}" class="block rounded-xl bg-white/5 p-3 hover:bg-white/10">
                    <p class="truncate text-sm font-semibold">{{ $task->title }}</p>
                    <div class="mt-1.5 flex items-center justify-between gap-2 text-xs text-slate-400">
                        <span>{{ $task->key }}</span>
                        <span class="{{ $task->due_date->isPast() ? 'text-rose-300' : '' }}">{{ $task->due_date->format('M j') }}</span>
                    </div>
                </a>
            @empty
                <p class="rounded-xl border border-dashed border-white/20 px-4 py-8 text-center text-sm text-slate-400">No upcoming deadlines.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
