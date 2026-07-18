@extends('admin.layout')

@section('title', 'New Project')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-7 flex items-start gap-4">
        <a href="{{ route('admin.projects.index') }}" class="mt-1 flex h-10 w-10 flex-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50" aria-label="Back to projects">←</a>
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Event delivery</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Create project</h1>
            <p class="mt-2 text-sm text-slate-500">A flexible Kanban workflow is created automatically.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
            <p class="font-bold">Please correct the errors below.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.projects.store') }}" class="space-y-6" data-testid="project-form">
        @csrf
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="font-bold text-slate-900">Project identity</h2>
                <p class="mt-1 text-sm text-slate-500">Give the workstream a concise name and recognizable key.</p>
            </div>
            <div class="mt-6 grid gap-5 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label for="project-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Name <span class="text-rose-500">*</span></label>
                    <input id="project-name" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-xl border px-3.5 py-2.5 text-sm shadow-sm outline-none focus:ring-4 {{ $errors->has('name') ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('name')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="project-key" class="mb-1.5 block text-sm font-semibold text-slate-700">Key <span class="text-rose-500">*</span></label>
                    <input id="project-key" name="key" value="{{ old('key') }}" maxlength="10" required placeholder="OPS"
                           class="w-full rounded-xl border px-3.5 py-2.5 text-sm uppercase shadow-sm outline-none focus:ring-4 {{ $errors->has('key') ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' }}">
                    @error('key')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="project-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Description</label>
                    <textarea id="project-description" name="description" rows="4" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label for="project-type" class="mb-1.5 block text-sm font-semibold text-slate-700">Type</label>
                    <select id="project-type" name="type" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        @foreach(['event_operations' => 'Event operations', 'marketing' => 'Marketing', 'sponsorship' => 'Sponsorship', 'content' => 'Content', 'logistics' => 'Logistics', 'technology' => 'Technology', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', 'event_operations') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="project-priority" class="mb-1.5 block text-sm font-semibold text-slate-700">Priority</label>
                    <select id="project-priority" name="priority" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        @foreach(['low', 'medium', 'high', 'critical'] as $priority)
                            <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="project-status" class="mb-1.5 block text-sm font-semibold text-slate-700">Status</label>
                    <select id="project-status" name="status" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        @foreach(['planned', 'active', 'on_hold'] as $status)
                            <option value="{{ $status }}" @selected(old('status', 'planned') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="project-start" class="mb-1.5 block text-sm font-semibold text-slate-700">Start date</label>
                    <input id="project-start" type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="project-due" class="mb-1.5 block text-sm font-semibold text-slate-700">Due date</label>
                    <input id="project-due" type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                    @error('due_date')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="project-budget" class="mb-1.5 block text-sm font-semibold text-slate-700">Budget</label>
                    <input id="project-budget" type="number" step="0.0001" min="0" name="budget_amount" value="{{ old('budget_amount') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="project-currency" class="mb-1.5 block text-sm font-semibold text-slate-700">Currency</label>
                    <input id="project-currency" name="currency" maxlength="3" value="{{ old('currency', current_event_currency()) }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="project-tags" class="mb-1.5 block text-sm font-semibold text-slate-700">Tags</label>
                    <input id="project-tags" name="tags" value="{{ old('tags') }}" placeholder="venue, launch, priority" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div class="grid grid-cols-[1fr_5rem] gap-3">
                    <div>
                        <label for="project-visibility" class="mb-1.5 block text-sm font-semibold text-slate-700">Visibility</label>
                        <select id="project-visibility" name="visibility" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                            <option value="members">Members</option>
                            <option value="organization">Organization</option>
                        </select>
                    </div>
                    <div>
                        <label for="project-color" class="mb-1.5 block text-sm font-semibold text-slate-700">Color</label>
                        <input id="project-color" type="color" name="color" value="{{ old('color', '#4f46e5') }}" class="h-[42px] w-full rounded-xl border border-slate-300 bg-white p-1 shadow-sm">
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.projects.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</a>
            <button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">Create project</button>
        </div>
    </form>
</div>
@endsection
