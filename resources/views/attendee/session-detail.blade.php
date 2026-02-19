@extends('attendee.layout')

@section('title', $session->title)

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('attendee.sessions') }}" 
       class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Sessions
    </a>
</div>

<!-- Session Header -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
        <div class="flex-1">
            <div class="flex items-start gap-3 mb-3">
                <h1 class="text-3xl font-bold text-gray-900 flex-1">{{ $session->title }}</h1>
                @if($session->track)
                    <span class="px-3 py-1 text-sm font-semibold rounded-full whitespace-nowrap"
                          style="background-color: {{ $session->track->color }}20; color: {{ $session->track->color }}">
                        {{ $session->track->name }}
                    </span>
                @endif
            </div>
            
            @if($session->description)
                <p class="text-gray-700 mb-4">{{ $session->description }}</p>
            @endif
        </div>
    </div>
    
    <!-- Session Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="text-xs text-gray-500">Time</p>
                <p class="text-sm font-semibold text-gray-900">
                    {{ $session->start_time->format('M d, Y') }}
                </p>
                <p class="text-sm text-gray-700">
                    {{ $session->start_time->format('H:i') }} - {{ $session->end_time ? $session->end_time->format('H:i') : 'TBD' }}
                </p>
            </div>
        </div>
        
        @if($session->location)
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            </svg>
            <div>
                <p class="text-xs text-gray-500">Location</p>
                <p class="text-sm font-semibold text-gray-900">{{ $session->location->name }}</p>
                @if($session->location->capacity)
                    <p class="text-xs text-gray-600">Capacity: {{ $session->location->capacity }}</p>
                @endif
            </div>
        </div>
        @endif
        
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <div>
                <p class="text-xs text-gray-500">Type</p>
                <p class="text-sm font-semibold text-gray-900 capitalize">{{ $session->type }}</p>
            </div>
        </div>
    </div>
    
    <!-- Favorite Button -->
    <div class="flex justify-end">
        <button onclick="toggleFavorite('App\\Models\\Session', {{ $session->id }}, this)" 
                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
            Add to Favorites
        </button>
    </div>
</div>

<!-- Speakers -->
@if($session->speakers->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        Speakers
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($session->speakers as $speaker)
            <a href="{{ route('attendee.speakers.show', $speaker) }}" 
               class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                @if($speaker->profile_image)
                    <img src="{{ asset('storage/' . $speaker->profile_image) }}" 
                         alt="{{ $speaker->full_name }}"
                         class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center">
                        <span class="text-xl font-bold text-white">
                            {{ substr($speaker->full_name, 0, 1) }}
                        </span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 truncate">{{ $speaker->full_name }}</h3>
                    @if($speaker->job_title)
                        <p class="text-sm text-gray-600 truncate">{{ $speaker->job_title }}</p>
                    @endif
                    @if($speaker->company)
                        <p class="text-xs text-gray-500 truncate">{{ $speaker->company }}</p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</div>
@endif

<!-- Lectures -->
@if($session->lectures->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Lectures in this Session
    </h2>
    <div class="space-y-4">
        @foreach($session->lectures as $lecture)
            <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $lecture->topic }}</h3>
                        @if($lecture->description)
                            <p class="text-sm text-gray-600 mb-3">{{ $lecture->description }}</p>
                        @endif
                    </div>
                    <button onclick="toggleFavorite('App\\Models\\Lecture', {{ $lecture->id }}, this)" 
                            class="flex-shrink-0 p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="flex flex-wrap gap-4 text-sm">
                    @if($lecture->start_time && $lecture->end_time)
                        <div class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $lecture->start_time->format('H:i') }} - {{ $lecture->end_time->format('H:i') }}
                        </div>
                    @endif
                    
                    @if($lecture->speaker)
                        <a href="{{ route('attendee.speakers.show', $lecture->speaker) }}" 
                           class="flex items-center text-indigo-600 hover:text-indigo-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ $lecture->speaker->full_name }}
                        </a>
                    @endif
                    
                    @if($lecture->location)
                        <div class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            {{ $lecture->location->name }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

@if($session->lectures->isEmpty() && $session->speakers->isEmpty())
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Additional Details</h3>
    <p class="text-gray-600">Speakers and lectures will be added closer to the event date.</p>
</div>
@endif
@endsection
