@extends('admin.layout')

@section('title', 'Reviewers')

@section('content')
<div class="grid lg:grid-cols-[1fr_22rem] gap-6" data-testid="reviewer-management">
    <div class="space-y-5">
        <div><p class="text-sm font-semibold text-indigo-600">Review committee</p><h2 class="text-3xl font-bold">Reviewers</h2></div>
        <form><input name="search" value="{{ request('search') }}" placeholder="Search reviewers" class="w-full max-w-md rounded-xl border-slate-300"></form>
        <div class="overflow-hidden rounded-2xl border bg-white"><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Reviewer</th><th class="px-4 py-3">Expertise</th><th class="px-4 py-3">Assignments</th><th class="px-4 py-3">Reviews</th><th class="px-4 py-3">Status</th></tr></thead><tbody class="divide-y">@forelse($reviewers as $reviewer)<tr><td class="px-4 py-4"><p class="font-semibold">{{ $reviewer->name }}</p><p class="text-xs text-slate-500">{{ $reviewer->email }}</p></td><td class="px-4 py-4">{{ collect($reviewer->expertise)->take(3)->join(', ') ?: '—' }}</td><td class="px-4 py-4">{{ $reviewer->assignments_count }}</td><td class="px-4 py-4">{{ $reviewer->reviews_count }}</td><td class="px-4 py-4"><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">{{ $reviewer->status }}</span></td></tr>@empty<tr><td colspan="5" class="p-10 text-center text-slate-500">No reviewers yet.</td></tr>@endforelse</tbody></table></div></div>
        {{ $reviewers->links() }}
    </div>
    <aside class="rounded-2xl border bg-white p-5 shadow-sm h-fit">
        <h3 class="text-lg font-bold">Add reviewer</h3>
        <form method="POST" action="{{ route('admin.submissions.reviewers.store') }}" class="mt-4 space-y-3">@csrf
            <input name="name" required placeholder="Full name" class="w-full rounded-xl border-slate-300">
            <input name="email" type="email" required placeholder="Email" class="w-full rounded-xl border-slate-300">
            <input name="organization" placeholder="Organization" class="w-full rounded-xl border-slate-300">
            <input name="expertise" placeholder="Expertise, comma separated" class="w-full rounded-xl border-slate-300">
            <input name="maximum_capacity" type="number" min="1" placeholder="Assignment capacity" class="w-full rounded-xl border-slate-300">
            <button class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 font-semibold text-white">Add reviewer</button>
        </form>
    </aside>
</div>
@endsection
