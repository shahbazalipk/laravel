<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Speaker Submissions') · {{ config('event.name', 'Event') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ route('submissions.portal.dashboard') }}" class="flex items-center gap-3">
                @if(config('event.logo'))<img src="{{ storage_public_url(config('event.logo')) }}" class="h-10 w-auto" alt="">@endif
                <div><p class="font-bold">{{ config('event.name', 'Event') }}</p><p class="text-xs text-slate-500">Speaker submissions</p></div>
            </a>
            @if(session()->has('submission_portal_user_id'))<form method="POST" action="{{ route('submissions.portal.logout') }}">@csrf<button class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Sign out</button></form>@endif
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</body>
</html>
