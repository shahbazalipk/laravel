@extends('attendee.layout')

@section('title', 'Sessions')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Sessions</h1>
    <p class="text-gray-600">Browse all event sessions</p>
</div>

<!-- Track Filter -->
@if($tracks->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <span class="text-sm font-medium text-gray-700 py-2">Filter by Track:</span>
        <button class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">All</button>
        @foreach($tracks as $track)
            <button class="px-4 py-2 text-sm bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition">
                {{ $track->name }}
            </button>
        @endforeach
    </div>
</div>
@endif

@if($sessions->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Sessions Yet</h3>
        <p class="text-gray-600">Check back later for session information</p>
    </div>
@else
    <div class="space-y-4">
        @foreach($sessions as $session)
            <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                <div class="flex flex-col md:flex-row md:items-start gap-6">
                    <!-- Time -->
                    <div class="flex-shrink-0 text-center md:text-left">
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $session->start_time->format('H:i') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $session->start_time->format('M d') }}
                        </div>
                        @if($session->end_time)
                            <div class="text-xs text-gray-400 mt-1">
                                {{ $session->end_time->format('H:i') }}
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-1">
                        <div class="flex flex-wrap items-start justify-between gap-4 mb-2">
                            <h3 class="text-lg font-bold text-gray-900">{{ $session->title }}</h3>
                            @if($session->track)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full"
                                      style="background-color: {{ $session->track->color }}20; color: {{ $session->track->color }}">
                                    {{ $session->track->name }}
                                </span>
                            @endif
                        </div>

                        @if($session->description)
                            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($session->description, 200) }}</p>
                        @endif

                        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                            @if($session->location)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                    {{ $session->location->name }}
                                </div>
                            @endif

                            @if($session->speakers->isNotEmpty())
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $session->speakers->pluck('full_name')->join(', ') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $sessions->links() }}
    </div>
@endif
@endsection
