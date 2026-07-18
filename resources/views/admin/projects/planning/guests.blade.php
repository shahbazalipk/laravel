@extends('admin.layout')

@section('title', 'Project Guests')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" data-testid="project-guests">
    @include('admin.projects._nav')

    <div class="mb-6">
        <p class="text-sm font-semibold text-indigo-600">Access control</p>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">External guests</h1>
        <p class="mt-2 text-sm text-slate-500">Grant vendors, contractors and reviewers explicit, time-limited project access.</p>
    </div>

    @if(session('guest_invitation_token'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
            <p class="font-black">Copy this one-time invitation token now</p>
            <code class="mt-2 block break-all rounded-lg bg-white p-3 text-xs">{{ session('guest_invitation_token') }}</code>
        </div>
    @endif

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-black text-slate-900">Invite a guest</h2>
        <form method="POST" id="guest-invitation-form" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <select id="guest-project" required class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">
                <option value="">Select project</option>
                @foreach($projects as $project)
                    <option value="{{ route('admin.projects.guests.store', $project) }}">{{ $project->name }}</option>
                @endforeach
            </select>
            <input name="name" required placeholder="Guest name" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
            <input type="email" name="email" required placeholder="Email address" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
            <input name="organization_name" placeholder="Organization" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
            <select name="type" aria-label="Guest type" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">
                @foreach(['vendor','agency','contractor','volunteer','client','reviewer'] as $type)
                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            <select name="role" aria-label="Guest role" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">
                <option value="viewer">Viewer</option>
                <option value="contributor">Contributor</option>
                <option value="reviewer">Reviewer</option>
            </select>
            <input type="number" name="invitation_days" min="1" max="30" value="7" aria-label="Invitation validity in days" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">Create invitation</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        @foreach(['Guest','Type','Access','Status','Expires'] as $heading)
                            <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($guests as $guest)
                        <tr>
                            <td class="px-4 py-4">
                                <p class="text-sm font-bold text-slate-900">{{ $guest->name }}</p>
                                <p class="text-xs text-slate-500">{{ $guest->email }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-600">{{ ucfirst($guest->type) }}</td>
                            <td class="px-4 py-4 text-sm text-slate-600">{{ $guest->accessGrants->pluck('project.name')->filter()->join(', ') ?: 'No active grant' }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700">{{ ucfirst($guest->status) }}</span></td>
                            <td class="px-4 py-4 text-sm text-slate-600">{{ optional($guest->invitation_expires_at)->format('M j, Y') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">No external guests have been invited.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@push('scripts')
<script>
document.getElementById('guest-invitation-form')?.addEventListener('submit', function (event) {
    const action = document.getElementById('guest-project').value;
    if (!action) {
        event.preventDefault();
        return;
    }
    this.action = action;
});
</script>
@endpush
@endsection
