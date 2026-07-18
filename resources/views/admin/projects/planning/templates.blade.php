@extends('admin.layout')

@section('title', 'Project Templates')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" data-testid="project-templates">
    @include('admin.projects._nav')

    <div class="mb-6">
        <p class="text-sm font-semibold text-indigo-600">Planning</p>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Project templates</h1>
        <p class="mt-2 text-sm text-slate-500">Reuse task schedules, checklists, date offsets and dependencies.</p>
    </div>

    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CONFIGURE))
        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-black text-slate-900">Capture an existing project</h2>
            <form method="POST" id="capture-template-form" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @csrf
                <select id="template-source-project" required class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">
                    <option value="">Source project</option>
                    @foreach($projects as $project)
                        <option value="{{ route('admin.projects.templates.capture', $project) }}">{{ $project->name }}</option>
                    @endforeach
                </select>
                <input name="name" required placeholder="Template name" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                <input name="category" placeholder="Category" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-600">
                    <input type="checkbox" name="is_shared" value="1" class="rounded border-slate-300 text-indigo-600"> Shared
                </label>
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">Create template</button>
            </form>
        </section>
    @endif

    <div class="grid gap-5 lg:grid-cols-2">
        @forelse($templates as $template)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-black text-slate-900">{{ $template->name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $template->description ?: 'Reusable project structure' }}</p>
                    </div>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-black text-indigo-700">{{ $template->task_count }} tasks</span>
                </div>
                @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CREATE))
                    <form method="POST" action="{{ route('admin.projects.templates.instantiate', $template) }}" class="mt-5 grid gap-3 border-t border-slate-100 pt-5 sm:grid-cols-2">
                        @csrf
                        <input name="name" required placeholder="New project name" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                        <input name="key" required maxlength="16" placeholder="Project key" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase">
                        <input type="date" name="start_date" required value="{{ today()->toDateString() }}" aria-label="Start date" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                        <select name="visibility" aria-label="Visibility" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">
                            <option value="members">Members only</option>
                            <option value="organization">Organization</option>
                            <option value="private">Private</option>
                        </select>
                        <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white sm:col-span-2">Create project from template</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-500 lg:col-span-2">No templates have been created yet.</div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
document.getElementById('capture-template-form')?.addEventListener('submit', function (event) {
    const action = document.getElementById('template-source-project').value;
    if (!action) {
        event.preventDefault();
        return;
    }
    this.action = action;
});
</script>
@endpush
@endsection
