@extends('submissions.layout')
@section('title', $type->public_title ?: $type->name)
@section('content')
<div class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-2xl" data-testid="submission-landing">
    <div class="grid lg:grid-cols-[1.2fr_.8fr]">
        <section class="p-7 sm:p-12 lg:p-16">
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-300">Call for submissions</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold leading-tight sm:text-5xl">{{ $type->public_title ?: $type->name }}</h1>
            <div class="prose prose-invert mt-6 max-w-2xl text-slate-300">{!! nl2br(e($type->instructions ?: $type->description)) !!}</div>
            <div class="mt-8 flex flex-wrap gap-4 text-sm text-slate-300">
                @if($type->opens_at)<span>Opens {{ $type->opens_at->timezone($type->timezone)->format('M j, Y g:i A') }}</span>@endif
                @if($type->closes_at)<span>Closes {{ $type->closes_at->timezone($type->timezone)->format('M j, Y g:i A T') }}</span>@endif
            </div>
        </section>
        <aside class="bg-gradient-to-br from-indigo-600 to-violet-700 p-7 sm:p-12 flex items-center">
            <div><h2 class="text-2xl font-bold">Share your expertise</h2><p class="mt-3 leading-7 text-indigo-100">Save a draft, return from any device, and track every decision in your private portal.</p>
                @if(session()->has('submission_portal_user_id'))<form class="mt-7" method="POST" action="{{ route('submissions.public.start', ['eventSlug' => $eventSlug, 'submissionType' => $type]) }}">@csrf<button class="rounded-xl bg-white px-5 py-3 font-bold text-indigo-700 shadow-lg">Start submission</button></form>
                @else<a href="{{ route('submissions.portal.login') }}" class="mt-7 inline-flex rounded-xl bg-white px-5 py-3 font-bold text-indigo-700 shadow-lg">Sign in to begin</a>@endif
            </div>
        </aside>
    </div>
</div>
@endsection
