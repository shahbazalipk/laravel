@extends('submissions.layout')
@section('title', 'My submissions')
@section('content')
<div class="space-y-7" data-testid="applicant-dashboard">
    <div><p class="text-sm font-semibold text-indigo-600">Welcome back</p><h1 class="text-3xl font-bold">My submissions</h1><p class="mt-1 text-slate-500">{{ $user->email }}</p></div>
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($submissions as $submission)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex justify-between gap-3"><span class="font-mono text-xs text-slate-500">{{ $submission->reference_number ?: 'Draft' }}</span><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $submission->currentStage->name ?? ucfirst($submission->status->value) }}</span></div>
                <h2 class="mt-4 text-lg font-bold">{{ $submission->title }}</h2><p class="mt-1 text-sm text-slate-500">{{ $submission->type->name }}</p>
                <div class="mt-5 flex flex-wrap gap-2">@if($submission->is_draft || $submission->type->allow_editing_after_submission)<a href="{{ route('submissions.public.edit', ['eventSlug' => request()->getHost(), 'submission' => $submission]) }}" class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white">{{ $submission->is_draft ? 'Continue' : 'View / edit' }}</a>@endif @foreach($submission->speakerLinks as $link)<a href="{{ route('submissions.speaker.onboarding', $link) }}" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">Speaker onboarding</a>@endforeach</div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center"><h2 class="font-bold">No submissions yet</h2><p class="mt-2 text-sm text-slate-500">Open a public call page to start your application.</p></div>
        @endforelse
    </div>
    {{ $submissions->links() }}
</div>
@endsection
