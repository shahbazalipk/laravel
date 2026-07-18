@extends('admin.layout')

@section('title', $project->name)

@section('content')
<div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div class="flex items-start gap-4">
        <a href="{{ route('admin.projects.index') }}" class="mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50" aria-label="Back to projects">←</a>
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">{{ $project->key }}</p>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-600">{{ str_replace('_', ' ', $project->status) }}</span>
            </div>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">{{ $project->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">{{ $project->description ?: 'No project description has been added.' }}</p>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2 pl-14 lg:pl-0">
        <span class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm">{{ $project->tasks_count }} tasks</span>
        <span class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm">{{ $project->members_count }} members</span>
        @if($project->due_date)
            <span class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm">Due {{ $project->due_date->format('M j, Y') }}</span>
        @endif
        @if(config('modules.finance.enabled')
            && app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::VIEW_FINANCIAL)
            && app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::VIEW))
            <a href="{{ route('admin.projects.finance.show', $project) }}" class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700">Financials</a>
        @endif
    </div>
</div>

@if($errors->any())
    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
        <p class="font-bold">Please correct the highlighted task details.</p>
        <p class="mt-1">{{ $errors->first() }}</p>
    </div>
@endif

@if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CREATE_TASK))
    <details class="group mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm" @if($errors->any()) open @endif>
        <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4 sm:px-6">
            <div>
                <h2 class="font-bold text-slate-900">Add task</h2>
                <p class="mt-1 text-xs text-slate-500">Capture a deliverable, owner-ready work item or milestone.</p>
            </div>
            <span class="rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-bold text-indigo-700 group-open:hidden">New task</span>
            <span class="hidden text-2xl text-slate-400 group-open:block">×</span>
        </summary>
        <form method="POST" action="{{ route('admin.projects.tasks.store', $project) }}" class="grid gap-4 border-t border-slate-100 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-4" data-testid="project-task-form">
            @csrf
            <div class="sm:col-span-2 lg:col-span-2">
                <label for="task-title" class="mb-1.5 block text-sm font-semibold text-slate-700">Task title <span class="text-rose-500">*</span></label>
                <input id="task-title" name="title" value="{{ old('title') }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            </div>
            <div>
                <label for="task-type" class="mb-1.5 block text-sm font-semibold text-slate-700">Type</label>
                <select id="task-type" name="type" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                    @foreach(['task', 'milestone', 'approval', 'bug'] as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="task-priority" class="mb-1.5 block text-sm font-semibold text-slate-700">Priority</label>
                <select id="task-priority" name="priority" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                    @foreach(['low', 'medium', 'high', 'critical'] as $priority)<option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>{{ ucfirst($priority) }}</option>@endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="task-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Description</label>
                <textarea id="task-description" name="description" rows="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="task-due" class="mb-1.5 block text-sm font-semibold text-slate-700">Due date</label>
                <input id="task-due" type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            </div>
            <div class="flex items-end">
                <button class="w-full rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">Add task</button>
            </div>
        </form>
    </details>
@endif

<section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-bold text-slate-900">Project members</h2>
            <p class="mt-1 text-xs text-slate-500">People with direct access to this project.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($project->members->where('is_active', true) as $member)
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $member->administrator?->name ?: 'Administrator #'.$member->organization_admin_user_id }} · {{ ucfirst($member->role) }}</span>
            @endforeach
        </div>
    </div>
    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::MANAGE_MEMBERS))
        <form method="POST" action="{{ route('admin.projects.members.store', $project) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-[minmax(0,1fr)_10rem_auto_auto]">
            @csrf
            <label class="sr-only" for="project-member-administrator">Administrator</label>
            <select id="project-member-administrator" name="organization_admin_user_id" required class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                <option value="">Select administrator</option>
                @foreach($administrators as $administrator)
                    <option value="{{ $administrator->id }}">{{ $administrator->name }} · {{ $administrator->email }}</option>
                @endforeach
            </select>
            <select name="role" aria-label="Project role" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                @foreach(['member', 'coordinator', 'manager', 'viewer'] as $role)<option value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach
            </select>
            <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-600"><input type="checkbox" name="can_view_financials" value="1" class="rounded border-slate-300 text-indigo-600"> Financials</label>
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800">Add member</button>
        </form>
    @endif
</section>

@if($board)
    <div class="mb-3 flex items-center justify-between">
        <div>
            <h2 class="font-bold text-slate-900">{{ $board->name }}</h2>
            <p class="mt-1 text-xs text-slate-500">Workflow board · scroll horizontally on smaller screens</p>
        </div>
    </div>
    <section class="-mx-4 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8" aria-label="{{ $board->name }}" data-testid="project-kanban">
        <div class="flex min-w-max gap-4">
            @foreach($board->columns as $column)
                <div class="w-[18rem] flex-none rounded-2xl bg-slate-100/80 p-3">
                    <div class="mb-3 flex items-center justify-between px-1">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $column->color }}"></span>
                            <h3 class="text-sm font-bold text-slate-800">{{ $column->name }}</h3>
                        </div>
                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-slate-500 shadow-sm">{{ $column->tasks->count() }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($column->tasks as $task)
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-xs font-bold text-indigo-600">{{ $task->key }}</p>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $task->priority === 'critical' ? 'bg-rose-100 text-rose-700' : ($task->priority === 'high' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ $task->priority }}</span>
                                </div>
                                <h4 class="mt-2 text-sm font-bold leading-5 text-slate-900">
                                    <a href="{{ route('admin.projects.tasks.show', [$project, $task]) }}" class="hover:text-indigo-700">{{ $task->title }}</a>
                                </h4>
                                @if($task->due_date)
                                    <p class="mt-3 text-xs font-semibold {{ $task->due_date->isPast() && !$column->is_terminal ? 'text-rose-600' : 'text-slate-500' }}">Due {{ $task->due_date->format('M j') }}</p>
                                @endif
                                @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CHANGE_STATUS))
                                    <form method="POST" action="{{ route('admin.projects.tasks.move', [$project, $task]) }}" class="mt-3 border-t border-slate-100 pt-3">
                                        @csrf
                                        @method('PATCH')
                                        <label for="move-{{ $task->public_id }}" class="sr-only">Move {{ $task->title }}</label>
                                        <select id="move-{{ $task->public_id }}" name="column_id" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs font-semibold text-slate-600 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                            @foreach($board->columns as $targetColumn)
                                                <option value="{{ $targetColumn->public_id }}" @selected($targetColumn->id === $column->id)>Move to {{ $targetColumn->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-xs font-medium text-slate-400">No tasks</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@else
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">No workflow board is configured.</div>
@endif
@endsection
