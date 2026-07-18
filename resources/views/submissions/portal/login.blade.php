@extends('submissions.layout')
@section('title', 'Secure sign in')
@section('content')
<div class="mx-auto max-w-md py-12" data-testid="submission-login">
    <div class="rounded-3xl border border-slate-200 bg-white p-7 sm:p-9 shadow-xl shadow-slate-200/50">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-xl text-indigo-700">✦</div>
        <h1 class="mt-6 text-3xl font-bold">Your secure portal</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Enter your email and we’ll send a single-use link. No password required.</p>
        <form method="POST" action="{{ route('submissions.portal.send') }}" class="mt-7 space-y-4">@csrf
            <label class="block"><span class="text-sm font-semibold">Email address</span><input type="email" name="email" required autofocus autocomplete="email" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></label>
            <input type="hidden" name="role" value="{{ request('role', 'applicant') }}">
            <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white hover:bg-indigo-700">Email my secure link</button>
        </form>
        <p class="mt-5 text-center text-xs text-slate-500">Links expire after 20 minutes and can be used once.</p>
    </div>
</div>
@endsection
