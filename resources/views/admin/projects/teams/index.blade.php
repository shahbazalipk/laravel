@extends('admin.layout')

@section('title', 'Project Teams')

@section('content')
<div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">People and capacity</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Project teams</h1>
    <p class="mt-2 text-sm text-slate-500">Organize event administrators into reusable delivery teams.</p>
</div>

@include('admin.projects._nav')

<div class="grid gap-6 xl:grid-cols-3">
    <section class="space-y-4 xl:col-span-2" data-testid="project-teams-list">
        @forelse($teams as $team)
            <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-900">{{ $team->name }}</h2>
                            <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">{{ $team->code }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">{{ $team->description ?: 'No team description.' }}</p>
                    </div>
                    <div class="flex gap-2 text-xs font-semibold text-slate-500">
                        <span class="rounded-lg bg-slate-100 px-2.5 py-1.5">{{ $team->members_count }} members</span>
                        <span class="rounded-lg bg-slate-100 px-2.5 py-1.5">{{ $team->projects_count }} projects</span>
                    </div>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="grid gap-2 sm:grid-cols-2">
                        @forelse($team->members->where('is_active', true) as $member)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3.5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $member->administrator?->name ?: 'Unknown administrator' }}</p>
                                    <p class="mt-0.5 truncate text-xs capitalize text-slate-500">{{ $member->role }}</p>
                                </div>
                                @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::MANAGE_MEMBERS) && $member->organization_admin_user_id !== $team->lead_admin_id)
                                    <form method="POST" action="{{ route('admin.projects.teams.members.destroy', [$team, $member]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs font-bold text-rose-600 hover:text-rose-700" aria-label="Remove {{ $member->administrator?->name }}">Remove</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No active members.</p>
                        @endforelse
                    </div>

                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::MANAGE_MEMBERS))
                        <form method="POST" action="{{ route('admin.projects.teams.members.store', $team) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-[minmax(0,1fr)_10rem_auto]">
                            @csrf
                            <label class="sr-only" for="team-{{ $team->public_id }}-administrator">Administrator</label>
                            <select id="team-{{ $team->public_id }}-administrator" name="organization_admin_user_id" required class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                                <option value="">Select administrator</option>
                                @foreach($administrators as $administrator)
                                    <option value="{{ $administrator->id }}">{{ $administrator->name }} · {{ $administrator->email }}</option>
                                @endforeach
                            </select>
                            <select name="role" aria-label="Team role" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                                @foreach(['member', 'coordinator', 'manager', 'viewer'] as $role)<option value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach
                            </select>
                            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800">Add member</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <p class="font-bold text-slate-800">No project teams</p>
                <p class="mt-1 text-sm text-slate-500">Create a team to coordinate shared delivery responsibilities.</p>
            </div>
        @endforelse
    </section>

    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CONFIGURE))
        <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-bold text-slate-900">Create team</h2>
            <p class="mt-1 text-sm text-slate-500">The team lead is added automatically.</p>
            <form method="POST" action="{{ route('admin.projects.teams.store') }}" class="mt-5 space-y-4" data-testid="project-team-form">
                @csrf
                <div>
                    <label for="team-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Name</label>
                    <input id="team-name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="team-code" class="mb-1.5 block text-sm font-semibold text-slate-700">Code</label>
                    <input id="team-code" name="code" value="{{ old('code') }}" required maxlength="16" placeholder="LOGISTICS" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                    @error('code')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="team-lead" class="mb-1.5 block text-sm font-semibold text-slate-700">Team lead</label>
                    <select id="team-lead" name="lead_admin_id" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        @foreach($administrators as $administrator)
                            <option value="{{ $administrator->id }}" @selected(old('lead_admin_id', session('admin_id')) == $administrator->id)>{{ $administrator->name }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="default_role" value="member">
                <div>
                    <label for="team-department" class="mb-1.5 block text-sm font-semibold text-slate-700">Department</label>
                    <input id="team-department" name="department" value="{{ old('department') }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="team-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Description</label>
                    <textarea id="team-description" name="description" rows="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('description') }}</textarea>
                </div>
                <button class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">Create team</button>
            </form>
        </aside>
    @endif
</div>
@endsection
