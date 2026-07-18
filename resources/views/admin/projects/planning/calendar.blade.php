@extends('admin.layout')

@section('title', 'Project Calendar')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" data-testid="project-calendar">
    @include('admin.projects._nav')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Planning</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Calendar</h1>
            <p class="mt-2 text-sm text-slate-500">Start dates, deadlines and milestones across active projects.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="?month={{ $month->subMonth()->format('Y-m') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700">Previous</a>
            <span class="min-w-36 text-center text-sm font-black text-slate-900">{{ $month->format('F Y') }}</span>
            <a href="?month={{ $month->addMonth()->format('Y-m') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700">Next</a>
        </div>
    </div>

    @php
        $firstCell = $month->startOfWeek();
        $tasksByDate = $tasks->groupBy(fn ($task) => optional($task->due_date)->toDateString());
    @endphp
    <section class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="min-w-[52rem]">
            <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
                @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
                    <div class="px-3 py-2 text-center text-xs font-black uppercase tracking-wide text-slate-500">{{ $day }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7">
                @for($index = 0; $index < 42; $index++)
                    @php $date = $firstCell->addDays($index); @endphp
                    <div class="min-h-32 border-b border-r border-slate-100 p-2 {{ $date->month !== $month->month ? 'bg-slate-50/70' : '' }}"
                         data-calendar-date="{{ $date->toDateString() }}">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-bold {{ $date->isToday() ? 'rounded-full bg-indigo-600 px-2 py-1 text-white' : 'text-slate-500' }}">{{ $date->day }}</span>
                        </div>
                        <div class="space-y-1.5">
                            @foreach($tasksByDate->get($date->toDateString(), collect()) as $task)
                                <a href="{{ route('admin.projects.tasks.show', [$task->project, $task]) }}"
                                   draggable="true"
                                   data-task-date-url="{{ route('admin.projects.tasks.dates.update', [$task->project, $task]) }}"
                                   class="block rounded-lg border-l-4 bg-slate-50 px-2 py-1.5 text-xs shadow-sm hover:bg-indigo-50"
                                   style="border-color: {{ $task->project->color }}">
                                    <span class="block truncate font-black text-slate-800">{{ $task->key }} · {{ $task->title }}</span>
                                    <span class="block truncate text-[10px] text-slate-500">{{ $task->project->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-black text-slate-900">Saved calendar filters</h2>
                <p class="text-xs text-slate-500">{{ $filters->count() }} personal or shared filter(s).</p>
            </div>
            <form method="POST" action="{{ route('admin.projects.filters.store') }}" class="flex flex-col gap-2 sm:flex-row">
                @csrf
                <input type="hidden" name="view" value="calendar">
                <input type="hidden" name="visibility" value="private">
                <input name="name" required placeholder="Filter name" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">Save current view</button>
            </form>
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let dragged = null;
    document.querySelectorAll('[data-task-date-url]').forEach((task) => {
        task.addEventListener('dragstart', () => dragged = task);
    });
    document.querySelectorAll('[data-calendar-date]').forEach((day) => {
        day.addEventListener('dragover', (event) => event.preventDefault());
        day.addEventListener('drop', async (event) => {
            event.preventDefault();
            if (!dragged) return;
            const response = await fetch(dragged.dataset.taskDateUrl, {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
                body: JSON.stringify({due_date: day.dataset.calendarDate})
            });
            if (response.ok) window.location.reload();
        });
    });
});
</script>
@endpush
@endsection
