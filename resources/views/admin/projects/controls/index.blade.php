@extends('admin.layout')

@section('title', 'Project Controls')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" data-testid="project-controls">
    @include('admin.projects._nav')

    <div class="mb-6">
        <p class="text-sm font-semibold text-indigo-600">Operations</p>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Project controls</h1>
        <p class="mt-2 text-sm text-slate-500">Capacity, timesheets, approvals, risks and active issues in one workspace.</p>
    </div>

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-testid="project-control-metrics">
        @foreach([
            ['label' => 'Team members', 'value' => $weeklyWorkload->count(), 'tone' => 'bg-indigo-50 text-indigo-700'],
            ['label' => 'Overloaded', 'value' => $weeklyWorkload->where('indicator', 'overloaded')->count(), 'tone' => 'bg-rose-50 text-rose-700'],
            ['label' => 'Pending approvals', 'value' => $approvalSteps->count(), 'tone' => 'bg-amber-50 text-amber-700'],
            ['label' => 'Open risks & issues', 'value' => $risks->whereNotIn('status', ['closed'])->count() + $issues->whereNotIn('status', ['resolved', 'closed'])->count(), 'tone' => 'bg-sky-50 text-sky-700'],
        ] as $metric)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-black {{ $metric['tone'] }}">{{ $metric['label'] }}</span>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-4"><h2 class="font-black text-slate-900">Weekly workload</h2><p class="text-xs text-slate-500">Estimated work due this week against each member’s capacity.</p></div>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach($weeklyWorkload as $load)
                @php
                    $tone = match($load['indicator']) {'overloaded' => 'bg-rose-500', 'near_capacity' => 'bg-amber-500', 'balanced' => 'bg-emerald-500', default => 'bg-sky-500'};
                @endphp
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3"><div><h3 class="text-sm font-black text-slate-800">{{ $load['user']->name }}</h3><p class="text-xs text-slate-500">{{ $load['tasks']->count() }} task(s)</p></div><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase text-slate-600">{{ str_replace('_', ' ', $load['indicator']) }}</span></div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full {{ $tone }}" style="width: {{ min(100, $load['utilization_percent']) }}%"></div></div>
                    <p class="mt-2 text-xs font-bold text-slate-600">{{ round($load['assigned_minutes'] / 60, 1) }}h / {{ round($load['capacity_minutes'] / 60, 1) }}h · {{ $load['utilization_percent'] > 200 ? '200+' : $load['utilization_percent'] }}%</p>
                </article>
            @endforeach
        </div>
    </section>

    <div class="mb-6 grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-black text-slate-900">My approval queue</h2>
            <div class="mt-4 space-y-3">
                @forelse($approvalSteps as $step)
                    <article class="rounded-xl bg-slate-50 p-4">
                        <p class="text-sm font-black text-slate-800">{{ $step->request->task->title }}</p>
                        <p class="text-xs text-slate-500">{{ $step->request->task->project->name }} · {{ ucfirst($step->request->mode) }} approval</p>
                        <form method="POST" action="{{ route('admin.projects.approvals.decide', $step) }}" class="mt-3 grid gap-2 sm:grid-cols-[1fr_auto_auto]">
                            @csrf
                            <input name="comments" placeholder="Decision comments" class="rounded-lg border border-slate-300 px-3 py-2 text-xs">
                            <button name="decision" value="changes_requested" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-black text-amber-700">Changes</button>
                            <button name="decision" value="approved" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-black text-white">Approve</button>
                        </form>
                    </article>
                @empty
                    <p class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">No approval decisions are waiting.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-black text-slate-900">Recent time entries</h2>
            <div class="mt-4 space-y-2">
                @forelse($timeLogs->take(8) as $log)
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
                        <div class="min-w-0"><p class="truncate text-sm font-bold text-slate-800">{{ $log->task->key }} · {{ $log->task->title }}</p><p class="text-xs text-slate-500">{{ $log->user?->name }} · {{ $log->logged_on->format('M j') }}</p></div>
                        <span class="flex-none text-sm font-black text-indigo-700">{{ round($log->duration_minutes / 60, 1) }}h</span>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">No time has been logged.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-black text-slate-900">Risk register</h2>
            <form method="POST" id="risk-form" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <select id="risk-project" required class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="">Project</option>@foreach($projects as $project)<option value="{{ route('admin.projects.risks.store', $project) }}">{{ $project->name }}</option>@endforeach</select>
                <input name="title" required placeholder="Risk title" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                <select name="probability" aria-label="Probability" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm">@foreach(range(1,5) as $value)<option value="{{ $value }}">Probability {{ $value }}</option>@endforeach</select>
                <select name="impact" aria-label="Impact" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm">@foreach(range(1,5) as $value)<option value="{{ $value }}">Impact {{ $value }}</option>@endforeach</select>
                <textarea name="mitigation_plan" rows="2" placeholder="Mitigation plan" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm sm:col-span-2"></textarea>
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white sm:col-span-2">Add risk</button>
            </form>
            <div class="mt-5 space-y-2 border-t border-slate-100 pt-4">
                @foreach($risks->take(8) as $risk)<div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5"><div><p class="text-sm font-bold text-slate-800">{{ $risk->title }}</p><p class="text-xs text-slate-500">{{ $risk->project->name }}</p></div><span class="rounded-full px-2.5 py-1 text-xs font-black {{ $risk->score >= 15 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">{{ $risk->score }}/25</span></div>@endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-black text-slate-900">Issue register</h2>
            <form method="POST" id="issue-form" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <select id="issue-project" required class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="">Project</option>@foreach($projects as $project)<option value="{{ route('admin.projects.issues.store', $project) }}">{{ $project->name }}</option>@endforeach</select>
                <input name="title" required placeholder="Issue title" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                <select name="severity" aria-label="Severity" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm">@foreach(['low','medium','high','critical'] as $severity)<option value="{{ $severity }}">{{ ucfirst($severity) }}</option>@endforeach</select>
                <input type="date" name="due_date" aria-label="Issue due date" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                <textarea name="description" rows="2" placeholder="Issue details" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm sm:col-span-2"></textarea>
                <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white sm:col-span-2">Add issue</button>
            </form>
            <div class="mt-5 space-y-2 border-t border-slate-100 pt-4">
                @foreach($issues->take(8) as $issue)<div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5"><div><p class="text-sm font-bold text-slate-800">{{ $issue->title }}</p><p class="text-xs text-slate-500">{{ $issue->project->name }}</p></div><span class="rounded-full px-2.5 py-1 text-xs font-black uppercase {{ $issue->severity === 'critical' ? 'bg-rose-100 text-rose-700' : 'bg-sky-100 text-sky-700' }}">{{ $issue->severity }}</span></div>@endforeach
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script>
for (const [formId, selectId] of [['risk-form', 'risk-project'], ['issue-form', 'issue-project']]) {
    document.getElementById(formId)?.addEventListener('submit', function (event) {
        const action = document.getElementById(selectId).value;
        if (!action) { event.preventDefault(); return; }
        this.action = action;
    });
}
</script>
@endpush
@endsection
