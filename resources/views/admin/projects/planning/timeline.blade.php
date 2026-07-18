@extends('admin.layout')

@section('title', 'Project Timeline')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" data-testid="project-timeline">
    @include('admin.projects._nav')

    <div class="mb-6">
        <p class="text-sm font-semibold text-indigo-600">Planning</p>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Timeline</h1>
        <p class="mt-2 text-sm text-slate-500">Project schedules, progress, milestones and dependency conflicts.</p>
    </div>

    @php
        $allTasks = $projects->flatMap->tasks->filter(fn ($task) => $task->start_date || $task->due_date);
        $rangeStart = $allTasks->pluck('start_date')->merge($allTasks->pluck('due_date'))->filter()->min() ?? today();
        $rangeEnd = $allTasks->pluck('due_date')->merge($allTasks->pluck('start_date'))->filter()->max() ?? today()->addMonth();
        $rangeDays = max(1, $rangeStart->diffInDays($rangeEnd));
    @endphp

    <div class="space-y-5">
        @forelse($projects as $project)
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('admin.projects.show', $project) }}" class="font-black text-slate-900 hover:text-indigo-700">{{ $project->name }}</a>
                        <p class="mt-1 text-xs text-slate-500">{{ $project->tasks->count() }} tasks · {{ $project->progress_percent }}% complete</p>
                    </div>
                    <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                </div>
                <div class="overflow-x-auto">
                    <div class="min-w-[52rem] divide-y divide-slate-100">
                        @foreach($project->tasks as $task)
                            @php
                                $start = $task->start_date ?? $task->due_date ?? $rangeStart;
                                $end = $task->due_date ?? $start;
                                $left = min(98, ($rangeStart->diffInDays($start, false) / $rangeDays) * 100);
                                $width = max(1.5, ($start->diffInDays($end) / $rangeDays) * 100);
                                $conflict = $task->dependencies->first(fn ($dependency) =>
                                    $dependency->dependsOn->due_date && $start->lt($dependency->dependsOn->due_date->addDays($dependency->lag_days))
                                );
                            @endphp
                            <div class="grid grid-cols-[14rem_minmax(32rem,1fr)] items-center gap-4 px-4 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.projects.tasks.show', [$project, $task]) }}" class="block truncate text-sm font-bold text-slate-800 hover:text-indigo-700">{{ $task->key }} · {{ $task->title }}</a>
                                    <span class="text-[11px] {{ $conflict ? 'font-bold text-rose-600' : 'text-slate-500' }}">
                                        {{ $start->format('M j') }} – {{ $end->format('M j') }}
                                        @if($conflict) · Dependency conflict @endif
                                    </span>
                                </div>
                                <div class="relative h-8 rounded-lg bg-slate-100">
                                    <div class="absolute top-1.5 h-5 rounded-md shadow-sm {{ $conflict ? 'bg-rose-500' : ($task->type === 'milestone' ? 'bg-amber-500' : 'bg-indigo-500') }}"
                                         style="left: {{ max(0, $left) }}%; width: {{ min(100 - max(0, $left), $width) }}%">
                                        <span class="block truncate px-2 text-[10px] font-black leading-5 text-white">{{ $task->progress_percent }}%</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-500">No active projects to display.</div>
        @endforelse
    </div>
</div>
@endsection
