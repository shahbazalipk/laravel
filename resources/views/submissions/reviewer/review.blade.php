@extends('submissions.layout')
@section('title', 'Review '.$assignment->submission->reference_number)
@section('content')
<div class="grid lg:grid-cols-[1fr_24rem] gap-7" data-testid="review-form">
    <div class="space-y-6">
        <div><p class="font-mono text-sm text-indigo-600">{{ $assignment->submission->reference_number }}</p><h1 class="text-3xl font-bold">{{ $assignment->submission->title }}</h1><p class="mt-1 text-slate-500">{{ $assignment->submission->type->name }}</p></div>
        <section class="rounded-2xl border bg-white p-6"><h2 class="text-lg font-bold">Submission content</h2><div class="mt-4 space-y-4">@foreach($visibleAnswers as $answer)<div><p class="text-xs font-semibold uppercase text-slate-500">{{ $answer->question_label }}</p><p class="mt-1 text-sm">{{ $answer->answer_text ?? collect($answer->answer_json)->join(', ') }}</p></div>@endforeach</div></section>
    </div>
    <aside class="space-y-5">
        @if(!$assignment->review?->submitted_at)
        <form method="POST" action="{{ route('reviewer.assignments.review', $assignment) }}" class="rounded-2xl border bg-white p-5 shadow-sm">@csrf
            <h2 class="text-lg font-bold">Scorecard</h2>
            <div class="mt-4 space-y-4">@foreach($assignment->submission->type->scorecards->first()?->criteria ?? [] as $criterion)<label class="block"><span class="text-sm font-semibold">{{ $criterion->name }}</span><input type="number" name="scores[{{ $criterion->id }}]" min="{{ $criterion->min_score }}" max="{{ $criterion->max_score }}" step="0.1" required value="{{ $assignment->review?->answers->firstWhere('criterion_id',$criterion->id)?->score }}" class="mt-2 w-full rounded-xl border-slate-300"></label>@endforeach</div>
            <label class="mt-4 block"><span class="text-sm font-semibold">Recommendation</span><select name="recommendation" class="mt-2 w-full rounded-xl border-slate-300">@foreach(['strongly_accept','accept','accept_with_revision','borderline','waitlist','reject','not_eligible'] as $option)<option>{{ $option }}</option>@endforeach</select></label>
            <label class="mt-4 block"><span class="text-sm font-semibold">Private comments</span><textarea name="comments" rows="4" class="mt-2 w-full rounded-xl border-slate-300">{{ $assignment->review?->summary }}</textarea></label>
            <div class="mt-5 grid grid-cols-2 gap-2"><button name="submit" value="0" class="rounded-xl border px-3 py-2 font-semibold">Save draft</button><button name="submit" value="1" class="rounded-xl bg-indigo-600 px-3 py-2 font-semibold text-white">Submit final</button></div>
        </form>
        <form method="POST" action="{{ route('reviewer.assignments.conflict', $assignment) }}" class="rounded-2xl border border-amber-200 bg-amber-50 p-5">@csrf<h2 class="font-bold text-amber-900">Conflict of interest?</h2><textarea name="reason" required placeholder="Describe the conflict" class="mt-3 w-full rounded-xl border-amber-300 text-sm"></textarea><button class="mt-3 text-sm font-semibold text-amber-900">Declare conflict</button></form>
        @else<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-900"><h2 class="font-bold">Review submitted</h2><p class="mt-2 text-sm">Final reviews are locked. Contact the organizer if it must be reopened.</p></div>@endif
    </aside>
</div>
@endsection
