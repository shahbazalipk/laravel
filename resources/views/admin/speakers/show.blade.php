@extends('admin.layout')

@section('title', 'Speaker Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.speakers.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div class="flex items-center">
                @if($speaker->profile_image)
                    <img src="{{ storage_public_url($speaker->profile_image) }}" alt="{{ $speaker->full_name }}" class="w-16 h-16 rounded-full object-cover mr-4">
                @else
                    <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                        <span class="text-indigo-600 font-semibold text-xl">{{ substr($speaker->full_name, 0, 2) }}</span>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $speaker->full_name }}</h1>
                    @if($speaker->job_title || $speaker->company)
                        <p class="text-gray-600 mt-1">
                            @if($speaker->job_title){{ $speaker->job_title }}@endif
                            @if($speaker->job_title && $speaker->company) at @endif
                            @if($speaker->company){{ $speaker->company }}@endif
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('admin.speakers.edit', $speaker) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Speaker
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Speaker Details</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if($speaker->email)
            <div>
                <p class="text-sm text-gray-600 mb-1">Email</p>
                <p class="text-sm text-gray-900">{{ $speaker->email }}</p>
            </div>
        @endif

        @if($speaker->phone)
            <div>
                <p class="text-sm text-gray-600 mb-1">Phone</p>
                <p class="text-sm text-gray-900">{{ $speaker->phone }}</p>
            </div>
        @endif
    </div>

    @if($speaker->bio)
        <div class="mt-6">
            <p class="text-sm text-gray-600 mb-1">Biography</p>
            <p class="text-sm text-gray-900">{{ $speaker->bio }}</p>
        </div>
    @endif
</div>

@if($speaker->submissionLinks->isNotEmpty())
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6" data-testid="speaker-operations">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800">Onboarding</h2>
        @foreach($speaker->submissionLinks as $link)
            <div class="mt-4 rounded-lg bg-gray-50 p-4">
                <p class="font-semibold">{{ $link->submission->title }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $link->onboarding->status ?? 'not_started')) }} · {{ $link->onboarding->completion_percent ?? 0 }}%</p>
            </div>
        @endforeach
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800">Assign session</h2>
        <form method="POST" action="{{ route('admin.submissions.speakers.sessions', $speaker) }}" class="mt-4 space-y-3">@csrf
            <select name="session_id" required class="w-full rounded-lg border-gray-300">@foreach($sessions as $session)<option value="{{ $session->id }}">{{ $session->title }} · {{ $session->start_time->format('M j, g:i A') }}</option>@endforeach</select>
            <input name="role" required value="Speaker" placeholder="Role" class="w-full rounded-lg border-gray-300">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Assign</button>
        </form>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800">Commercial details</h2>
        @php($commercial = $speaker->submissionLinks->first()->commercialTerm)
        <form method="POST" action="{{ route('admin.submissions.speakers.commercial', $speaker) }}" class="mt-4 space-y-3">@csrf @method('PUT')
            <div class="grid grid-cols-[1fr_5rem] gap-2"><input type="number" step="0.01" min="0" name="speaker_fee" value="{{ $commercial->fee_amount ?? '' }}" placeholder="Speaker fee" class="rounded-lg border-gray-300"><input name="currency" value="{{ $commercial->currency ?? 'USD' }}" class="rounded-lg border-gray-300"></div>
            <select name="payment_status" class="w-full rounded-lg border-gray-300">@foreach(['not_applicable','pending_approval','approved','invoice_requested','invoice_received','processing','paid','partially_paid'] as $status)<option @selected(($commercial->payment_status ?? '') === $status)>{{ $status }}</option>@endforeach</select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Save protected details</button>
        </form>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800">Contract</h2>
        <form method="POST" action="{{ route('admin.submissions.speakers.contracts', $speaker) }}" class="mt-4 space-y-3">@csrf
            <input type="date" name="expires_at" class="w-full rounded-lg border-gray-300">
            <textarea name="terms" required rows="4" placeholder="Agreement terms" class="w-full rounded-lg border-gray-300"></textarea>
            <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Send new version</button>
        </form>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800">Travel and visa</h2>
    @php($travel = $speaker->submissionLinks->first()->travel ?? null)
    <form method="POST" action="{{ route('admin.submissions.speakers.travel', $speaker) }}" class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">@csrf @method('PUT')
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="travel_required" value="1" @checked(($travel->status ?? '') === 'required')> Travel required</label>
        <input name="departure_country" value="{{ $travel->itinerary['departure_country'] ?? '' }}" placeholder="Departure country" class="rounded-lg border-gray-300">
        <input name="departure_city" value="{{ $travel->itinerary['departure_city'] ?? '' }}" placeholder="Departure city" class="rounded-lg border-gray-300">
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="visa_required" value="1" @checked($travel->documents['visa_required'] ?? false)> Visa support required</label>
        <button class="sm:col-span-2 lg:col-span-1 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Save travel details</button>
    </form>
</div>
@endif

<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Schedule</h2>

    @if($schedule->isEmpty())
        <p class="text-gray-500 text-sm">No sessions or lectures scheduled yet.</p>
    @else
        <div class="space-y-3">
            @foreach($schedule as $item)
                <div class="p-4 border border-gray-200 rounded-lg">
                    <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800 mb-2 inline-block">
                        {{ $item['type'] }}
                    </span>
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $item['title'] }}</h3>
                    <div class="text-xs text-gray-500">
                        {{ $item['start_time']->format('M d, g:i A') }} - {{ $item['end_time']->format('g:i A') }}
                        @if($item['location'])
                            • {{ $item['location'] }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
