@extends('admin.layout')

@section('title', 'Project Reports')

@section('content')
<div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Delivery intelligence</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Project reports</h1>
    <p class="mt-2 text-sm text-slate-500">A live operational view of throughput, risk and completion.</p>
</div>

@include('admin.projects._nav')

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-testid="project-report-metrics">
    @foreach([
        ['label' => 'Total tasks', 'value' => $summary['total_tasks'], 'tone' => 'bg-indigo-50 text-indigo-700'],
        ['label' => 'Completed', 'value' => $summary['completed_tasks'], 'tone' => 'bg-emerald-50 text-emerald-700'],
        ['label' => 'Completion rate', 'value' => $summary['completion_rate'].'%', 'tone' => 'bg-sky-50 text-sky-700'],
        ['label' => 'Overdue', 'value' => $summary['overdue_tasks'], 'tone' => 'bg-rose-50 text-rose-700'],
        ['label' => 'Estimated hours', 'value' => $summary['estimated_hours'], 'tone' => 'bg-violet-50 text-violet-700'],
        ['label' => 'Logged hours', 'value' => $summary['logged_hours'], 'tone' => 'bg-cyan-50 text-cyan-700'],
        ['label' => 'Open risks', 'value' => $summary['open_risks'], 'tone' => 'bg-amber-50 text-amber-700'],
        ['label' => 'Open issues', 'value' => $summary['open_issues'], 'tone' => 'bg-orange-50 text-orange-700'],
    ] as $metric)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">{{ $metric['label'] }}</p>
            <p class="mt-4 inline-flex rounded-xl px-3 py-2 text-3xl font-bold tracking-tight {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
        </article>
    @endforeach
</section>

<div class="mt-7 grid gap-6 lg:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-bold text-slate-900">Tasks by status</h2>
        <div class="mt-5 space-y-3">
            @forelse($statusBreakdown as $status => $count)
                @php($percentage = $summary['total_tasks'] > 0 ? round(($count / $summary['total_tasks']) * 100) : 0)
                <div>
                    <div class="mb-1.5 flex items-center justify-between text-sm"><span class="font-semibold capitalize text-slate-700">{{ str_replace('_', ' ', $status) }}</span><span class="font-bold text-slate-900">{{ $count }}</span></div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-500" style="width: {{ $percentage }}%"></div></div>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-slate-500">No task data yet.</p>
            @endforelse
        </div>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-bold text-slate-900">Open work by priority</h2>
        <div class="mt-5 grid grid-cols-2 gap-3">
            @foreach(['critical', 'high', 'medium', 'low'] as $priority)
                <div class="rounded-xl p-4 {{ $priority === 'critical' ? 'bg-rose-50' : ($priority === 'high' ? 'bg-amber-50' : ($priority === 'medium' ? 'bg-sky-50' : 'bg-slate-50')) }}">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $priority }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $priorityBreakdown[$priority] ?? 0 }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-bold text-slate-900">Task aging</h2>
        <div class="mt-4 grid grid-cols-2 gap-3">
            @foreach(['0–7 days','8–30 days','31–60 days','60+ days'] as $bucket)
                <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-bold text-slate-500">{{ $bucket }}</p><p class="mt-2 text-2xl font-black text-slate-900">{{ $taskAging[$bucket] ?? 0 }}</p></div>
            @endforeach
        </div>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-bold text-slate-900">Capacity health</h2>
        <div class="mt-4 grid grid-cols-2 gap-3">
            @foreach(['available','balanced','near_capacity','overloaded'] as $indicator)
                <div class="rounded-xl p-4 {{ $indicator === 'overloaded' ? 'bg-rose-50' : ($indicator === 'near_capacity' ? 'bg-amber-50' : 'bg-emerald-50') }}"><p class="text-xs font-bold uppercase text-slate-500">{{ str_replace('_', ' ', $indicator) }}</p><p class="mt-2 text-2xl font-black text-slate-900">{{ $workloadSummary[$indicator] ?? 0 }}</p></div>
            @endforeach
        </div>
    </section>
</div>

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
        <h2 class="font-bold text-slate-900">Project performance</h2>
        <p class="mt-1 text-xs text-slate-500">Completion and overdue workload by project</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50/80">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Project</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Tasks</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Completed</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Overdue</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Est. / actual</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Risks / issues</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($projectPerformance as $project)
                    <tr>
                        <td class="px-5 py-4 sm:px-6"><a href="{{ route('admin.projects.show', $project) }}" class="font-semibold text-slate-900 hover:text-indigo-700">{{ $project->name }}</a><p class="mt-1 text-xs text-slate-500">{{ $project->key }}</p></td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-700">{{ $project->tasks_count }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-emerald-600">{{ $project->completed_tasks_count }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-rose-600 sm:px-6">{{ $project->overdue_tasks_count }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-700">{{ round(($project->tasks_sum_estimated_minutes ?? 0) / 60, 1) }}h / {{ round(($project->tasks_sum_logged_minutes ?? 0) / 60, 1) }}h</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-amber-700 sm:px-6">{{ $project->open_risks_count }} / {{ $project->open_issues_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">No projects to report.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
