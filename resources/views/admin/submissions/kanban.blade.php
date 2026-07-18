@extends('admin.layout')

@section('title', 'Submission Kanban')

@section('content')
<div class="space-y-5" data-testid="submission-kanban">
    <div class="flex items-center justify-between"><div><p class="text-sm font-semibold text-indigo-600">Pipeline</p><h2 class="text-3xl font-bold">Submission Kanban</h2></div><a href="{{ route('admin.submissions.index') }}" class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold">Table view</a></div>
    <div class="flex gap-4 overflow-x-auto pb-5 snap-x">
        @foreach($stages as $stage)
            <section class="w-[19rem] shrink-0 snap-start rounded-2xl bg-slate-100 p-3">
                <div class="flex items-center justify-between px-2 py-2"><h3 class="font-bold">{{ $stage->name }}</h3><span class="rounded-full bg-white px-2 py-1 text-xs font-semibold">{{ $stage->submissions->count() }}</span></div>
                <div class="space-y-3">@foreach($stage->submissions as $submission)<a href="{{ route('admin.submissions.show', $submission) }}" class="block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300"><p class="text-xs font-mono text-slate-500">{{ $submission->reference_number }}</p><p class="mt-1 font-semibold text-slate-900">{{ $submission->title }}</p><div class="mt-3 flex justify-between text-xs text-slate-500"><span>{{ $submission->type->name ?? '' }}</span><span>{{ number_format($submission->average_score ?? 0, 1) }}</span></div></a>@endforeach</div>
            </section>
        @endforeach
    </div>
</div>
@endsection
