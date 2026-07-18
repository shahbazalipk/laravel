@extends('admin.layout')

@section('title', $task->key.' · '.$task->title)

@section('content')
<div class="mb-7 flex items-start gap-4">
    <a href="{{ route('admin.projects.show', $project) }}" class="mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50" aria-label="Back to project">←</a>
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">{{ $task->key }}</p>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-600">{{ str_replace('_', ' ', $task->status) }}</span>
            <span class="rounded-full px-2.5 py-1 text-xs font-bold uppercase {{ $task->priority === 'critical' ? 'bg-rose-100 text-rose-700' : ($task->priority === 'high' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700') }}">{{ $task->priority }}</span>
        </div>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">{{ $task->title }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ $project->name }} · {{ $task->column->name }}</p>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-3">
    <main class="space-y-6 xl:col-span-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-bold text-slate-900">Description</h2>
            <div class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $task->description ?: 'No description has been added.' }}</div>
            <div class="mt-5 grid gap-3 border-t border-slate-100 pt-5 text-sm sm:grid-cols-3">
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Due date</p><p class="mt-1 font-semibold text-slate-700">{{ $task->due_date?->format('M j, Y') ?: 'Not set' }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Estimate</p><p class="mt-1 font-semibold text-slate-700">{{ $task->estimated_minutes ? round($task->estimated_minutes / 60, 1).' hours' : 'Not set' }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Progress</p><p class="mt-1 font-semibold text-slate-700">{{ $task->progress_percent }}%</p></div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-testid="task-planning-controls">
            <h2 class="font-bold text-slate-900">Schedule and dependencies</h2>
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <h3 class="text-sm font-black text-slate-800">Dates</h3>
                    <form method="POST" action="{{ route('admin.projects.tasks.dates.update', [$project, $task]) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                        @csrf
                        @method('PATCH')
                        <label class="text-xs font-bold text-slate-600">Start date<input type="date" name="start_date" value="{{ $task->start_date?->toDateString() }}" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></label>
                        <label class="text-xs font-bold text-slate-600">Due date<input type="date" name="due_date" required value="{{ $task->due_date?->toDateString() }}" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></label>
                        <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white sm:col-span-2">Update dates</button>
                    </form>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <h3 class="text-sm font-black text-slate-800">Dependencies</h3>
                    <div class="mt-2 space-y-2">
                        @forelse($task->dependencies as $dependency)
                            <p class="rounded-lg bg-white px-3 py-2 text-xs text-slate-600"><span class="font-black text-slate-800">{{ $dependency->dependsOn->key }}</span> · {{ str_replace('_', ' ', $dependency->type) }} @if($dependency->is_enforced)<span class="text-rose-600">· enforced</span>@endif</p>
                        @empty
                            <p class="text-xs text-slate-500">No dependencies.</p>
                        @endforelse
                    </div>
                    <form method="POST" action="{{ route('admin.projects.tasks.dependencies.store', [$project, $task]) }}" class="mt-3 grid gap-2">
                        @csrf
                        <select name="depends_on_task_id" required class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                            <option value="">Select task</option>
                            @foreach($availableDependencies as $candidate)<option value="{{ $candidate->id }}">{{ $candidate->key }} · {{ $candidate->title }}</option>@endforeach
                        </select>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="type" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                                <option value="is_blocked_by">Is blocked by</option><option value="starts_after">Starts after</option><option value="finishes_before">Finishes before</option><option value="related_to">Related to</option>
                            </select>
                            <input type="number" name="lag_days" value="0" min="-365" max="365" aria-label="Lag days" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox" name="is_enforced" value="1" checked class="rounded border-slate-300 text-indigo-600"> Enforce before completion</label>
                        <button class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700">Add dependency</button>
                    </form>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50/50 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div><h3 class="text-sm font-black text-slate-800">Recurring task</h3><p class="text-xs text-slate-500">Generate dated copies from this task and its checklist.</p></div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($task->recurrenceRules as $rule)
                            <form method="POST" action="{{ route('admin.projects.recurrence.generate', $rule) }}">
                                @csrf
                                <button class="rounded-lg bg-white px-3 py-2 text-xs font-black text-indigo-700 shadow-sm">{{ ucfirst($rule->frequency) }} · generate due</button>
                            </form>
                        @endforeach
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.projects.tasks.recurrence.store', [$project, $task]) }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    @csrf
                    <select name="frequency" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm"><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option><option value="weekdays">Selected weekdays</option></select>
                    <input type="number" name="interval" min="1" max="365" value="1" aria-label="Recurrence interval" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <input type="date" name="starts_on" required value="{{ today()->toDateString() }}" aria-label="Starts on" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <input type="date" name="ends_on" aria-label="Ends on" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white">Create schedule</button>
                    <fieldset class="flex flex-wrap gap-3 lg:col-span-5">
                        <legend class="mb-1 text-xs font-bold text-slate-600">Selected weekdays (when applicable)</legend>
                        @foreach([1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'] as $day => $label)
                            <label class="flex items-center gap-1.5 text-xs text-slate-600"><input type="checkbox" name="weekdays[]" value="{{ $day }}" class="rounded border-slate-300 text-indigo-600"> {{ $label }}</label>
                        @endforeach
                    </fieldset>
                </form>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-testid="task-time-approval-controls">
            <div class="grid gap-6 lg:grid-cols-2">
                <div>
                    <h2 class="font-bold text-slate-900">Time tracking</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ round($task->logged_minutes / 60, 1) }}h logged against {{ $task->estimated_minutes ? round($task->estimated_minutes / 60, 1).'h estimated' : 'no estimate' }}.</p>
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::LOG_TIME))
                        @php $activeTimer = $task->timeLogs->first(fn ($log) => $log->organization_admin_user_id === (int) session('admin_id') && $log->started_at && !$log->ended_at); @endphp
                        @if($activeTimer)
                            <form method="POST" action="{{ route('admin.projects.timers.stop', $activeTimer) }}" class="mt-3">@csrf @method('PATCH')<button class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-black text-white">Stop timer · started {{ $activeTimer->started_at->diffForHumans() }}</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.projects.timers.start', [$project, $task]) }}" class="mt-3">@csrf<button class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-black text-indigo-700">Start timer</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.projects.time-logs.store', [$project, $task]) }}" class="mt-4 grid gap-2 sm:grid-cols-2">
                            @csrf
                            <input type="date" name="logged_on" required value="{{ today()->toDateString() }}" aria-label="Time log date" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <input type="number" name="duration_minutes" required min="1" max="1440" placeholder="Minutes" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <input name="description" placeholder="Work completed" class="rounded-xl border border-slate-300 px-3 py-2 text-sm sm:col-span-2">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox" name="is_billable" value="1" class="rounded border-slate-300 text-indigo-600"> Billable</label>
                            <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Log time</button>
                        </form>
                    @endif
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Task approval</h2>
                    <div class="mt-3 space-y-2">
                        @foreach($task->approvalRequests as $approval)
                            <div class="rounded-xl bg-slate-50 px-3 py-2.5"><p class="text-xs font-black uppercase text-slate-700">{{ str_replace('_', ' ', $approval->status) }} · {{ $approval->mode }}</p><p class="mt-1 text-xs text-slate-500">{{ $approval->steps->where('status', 'approved')->count() }}/{{ $approval->steps->count() }} approved</p></div>
                        @endforeach
                    </div>
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::APPROVE))
                        <form method="POST" action="{{ route('admin.projects.approvals.store', [$project, $task]) }}" class="mt-4 grid gap-2">
                            @csrf
                            <select name="mode" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm"><option value="any">Any one approver</option><option value="all">All approvers</option><option value="sequential">Sequential</option></select>
                            <select name="approver_admin_ids[]" multiple required aria-label="Approvers" class="min-h-24 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                                @foreach($administrators as $administrator)<option value="{{ $administrator->id }}">{{ $administrator->name }}</option>@endforeach
                            </select>
                            <textarea name="instructions" rows="2" placeholder="Approval instructions" class="rounded-xl border border-slate-300 px-3 py-2 text-sm"></textarea>
                            <button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white">Request approval</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900">Checklist</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $task->checklistItems->where('is_completed', true)->count() }} of {{ $task->checklistItems->count() }} complete</p>
                </div>
                <span class="text-sm font-bold text-indigo-600">{{ $task->progress_percent }}%</span>
            </div>
            <div class="mt-5 space-y-2">
                @forelse($task->checklistItems as $item)
                    <form method="POST" action="{{ route('admin.projects.tasks.checklist.toggle', [$project, $task, $item]) }}" class="flex items-center gap-3 rounded-xl bg-slate-50 px-3.5 py-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_completed" value="{{ $item->is_completed ? 0 : 1 }}">
                        <button class="flex h-5 w-5 flex-none items-center justify-center rounded border {{ $item->is_completed ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 bg-white' }}" aria-label="{{ $item->is_completed ? 'Reopen' : 'Complete' }} {{ $item->text }}">{{ $item->is_completed ? '✓' : '' }}</button>
                        <span class="min-w-0 flex-1 text-sm {{ $item->is_completed ? 'text-slate-400 line-through' : 'font-medium text-slate-700' }}">{{ $item->text }}</span>
                        @if($item->is_required)<span class="text-[10px] font-bold uppercase text-rose-500">Required</span>@endif
                    </form>
                @empty
                    <p class="rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">No checklist items yet.</p>
                @endforelse
            </div>
            @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::EDIT_TASK))
                <form method="POST" action="{{ route('admin.projects.tasks.checklist.store', [$project, $task]) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-[minmax(0,1fr)_auto_auto]">
                    @csrf
                    <input name="text" required placeholder="Add checklist item" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                    <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-600"><input type="checkbox" name="is_required" value="1" class="rounded border-slate-300 text-indigo-600"> Required</label>
                    <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800">Add</button>
                </form>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-bold text-slate-900">Discussion</h2>
            @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::COMMENT))
                <form method="POST" action="{{ route('admin.projects.tasks.comments.store', [$project, $task]) }}" class="mt-4">
                    @csrf
                    <label for="task-comment" class="sr-only">Add comment</label>
                    <textarea id="task-comment" name="body" rows="3" required placeholder="Share an update or decision…" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"></textarea>
                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-600"><input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300 text-indigo-600"> Internal note</label>
                        <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Add comment</button>
                    </div>
                </form>
            @endif
            <div class="mt-6 space-y-4 border-t border-slate-100 pt-5">
                @forelse($task->comments as $comment)
                    <article class="flex gap-3">
                        <div class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">{{ strtoupper(substr($comment->author?->name ?: 'A', 0, 1)) }}</div>
                        <div class="min-w-0 flex-1 rounded-xl bg-slate-50 px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-slate-800">{{ $comment->author?->name ?: 'Administrator' }}</p>
                                <time class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</time>
                                @if($comment->is_internal)<span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-700">Internal</span>@endif
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $comment->body }}</p>
                        </div>
                    </article>
                @empty
                    <p class="py-5 text-center text-sm text-slate-500">No comments yet.</p>
                @endforelse
            </div>
        </section>
    </main>

    <aside class="h-fit rounded-2xl bg-slate-950 p-5 text-white shadow-sm sm:p-6">
        <h2 class="font-bold">Workflow</h2>
        <p class="mt-1 text-xs text-slate-400">Move this task to another stage.</p>
        @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CHANGE_STATUS))
            <form method="POST" action="{{ route('admin.projects.tasks.move', [$project, $task]) }}" class="mt-5">
                @csrf
                @method('PATCH')
                <label for="task-column" class="mb-1.5 block text-xs font-semibold text-slate-300">Current stage</label>
                <select id="task-column" name="column_id" onchange="this.form.submit()" class="w-full rounded-xl border border-white/10 bg-white/10 px-3.5 py-2.5 text-sm font-semibold text-white outline-none focus:ring-4 focus:ring-indigo-500/30">
                    @foreach($task->board->columns as $column)
                        <option value="{{ $column->public_id }}" class="text-slate-900" @selected($column->id === $task->column_id)>{{ $column->name }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        <dl class="mt-6 space-y-4 border-t border-white/10 pt-5 text-sm">
            <div><dt class="text-xs text-slate-400">Reporter</dt><dd class="mt-1 font-semibold">Administrator #{{ $task->reporter_admin_id }}</dd></div>
            <div><dt class="text-xs text-slate-400">Created</dt><dd class="mt-1 font-semibold">{{ $task->created_at->format('M j, Y · g:i A') }}</dd></div>
            <div><dt class="text-xs text-slate-400">Last updated</dt><dd class="mt-1 font-semibold">{{ $task->updated_at->diffForHumans() }}</dd></div>
        </dl>
    </aside>
</div>
@endsection
