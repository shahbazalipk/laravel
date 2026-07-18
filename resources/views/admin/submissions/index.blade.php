@extends('admin.layout')

@section('title', 'Submissions')

@section('content')
<div class="space-y-6" data-testid="submission-list">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><p class="text-sm font-semibold text-indigo-600">Operations</p><h2 class="text-3xl font-bold text-slate-900">Submissions</h2></div>
        <div class="flex gap-2"><a href="{{ route('admin.submissions.kanban') }}" class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold">Kanban</a><a href="{{ route('admin.submissions.export', request()->query()) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white" data-testid="export-submissions">Export CSV</a></div>
    </div>
    <form class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3 rounded-2xl border bg-white p-4">
        <input name="search" value="{{ request('search') }}" placeholder="Reference or title" class="rounded-xl border-slate-300">
        <select name="type" class="rounded-xl border-slate-300"><option value="">All types</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->name }}</option>@endforeach</select>
        <select name="stage" class="rounded-xl border-slate-300"><option value="">All stages</option>@foreach($stages as $stage)<option value="{{ $stage->id }}" @selected(request('stage') == $stage->id)>{{ $stage->name }}</option>@endforeach</select>
        <select name="status" class="rounded-xl border-slate-300"><option value="">All statuses</option>@foreach(['draft','submitted','review','revision','selected','rejected','withdrawn'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
        <button class="rounded-xl bg-slate-900 px-4 py-2 font-semibold text-white">Filter</button>
    </form>
    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Reference</th><th class="px-4 py-3">Submission</th><th class="px-4 py-3">Applicant</th><th class="px-4 py-3">Stage</th><th class="px-4 py-3">Reviews</th><th class="px-4 py-3">Score</th><th></th></tr></thead>
            <tbody class="divide-y">@forelse($submissions as $submission)<tr class="hover:bg-slate-50">
                <td class="px-4 py-4 font-mono text-xs">{{ $submission->reference_number ?: 'Draft' }}</td>
                <td class="px-4 py-4"><p class="font-semibold text-slate-900">{{ $submission->title }}</p><p class="text-xs text-slate-500">{{ $submission->type->name }}</p></td>
                <td class="px-4 py-4">{{ $submission->applicant->name ?? $submission->applicant->email ?? '—' }}</td>
                <td class="px-4 py-4"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $submission->currentStage->name ?? ucfirst($submission->status->value) }}</span></td>
                <td class="px-4 py-4">{{ $submission->reviews_count }} / {{ $submission->review_assignments_count }}</td>
                <td class="px-4 py-4 font-semibold">{{ number_format($submission->average_score ?? 0, 1) }}</td>
                <td class="px-4 py-4"><a class="font-semibold text-indigo-600" href="{{ route('admin.submissions.show', $submission) }}">Open</a></td>
            </tr>@empty<tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">No submissions match the filters.</td></tr>@endforelse</tbody></table>
        </div>
    </div>
    {{ $submissions->links() }}
</div>
@endsection
