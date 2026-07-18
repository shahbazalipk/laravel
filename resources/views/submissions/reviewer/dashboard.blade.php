@extends('submissions.layout')
@section('title', 'Reviewer dashboard')
@section('content')
<div class="space-y-7" data-testid="reviewer-dashboard">
    <div><p class="text-sm font-semibold text-indigo-600">Review committee</p><h1 class="text-3xl font-bold">Assigned submissions</h1><p class="mt-1 text-slate-500">{{ $reviewer->name }} · {{ $reviewer->email }}</p></div>
    <div class="grid sm:grid-cols-3 gap-4">@foreach(['pending' => $assignments->whereIn('status',['assigned','accepted'])->count(), 'in progress' => $assignments->where('status','in_progress')->count(), 'completed' => $assignments->where('status','completed')->count()] as $label => $count)<div class="rounded-2xl border bg-white p-5"><p class="text-xs font-semibold uppercase text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold">{{ $count }}</p></div>@endforeach</div>
    <div class="space-y-3">@forelse($assignments as $assignment)<a href="{{ route('reviewer.assignments.show', $assignment) }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border bg-white p-5 shadow-sm hover:border-indigo-300"><div><p class="font-mono text-xs text-slate-500">{{ $assignment->submission->reference_number }}</p><h2 class="mt-1 font-bold">{{ $assignment->submission->title }}</h2><p class="text-sm text-slate-500">{{ $assignment->submission->type->name }}</p></div><div class="text-left sm:text-right"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ str_replace('_',' ',$assignment->status) }}</span><p class="mt-2 text-xs text-slate-500">{{ $assignment->due_at ? 'Due '.$assignment->due_at->format('M j') : 'No due date' }}</p></div></a>@empty<div class="rounded-2xl border border-dashed bg-white p-12 text-center text-slate-500">No review assignments.</div>@endforelse</div>
    {{ $assignments->links() }}
</div>
@endsection
