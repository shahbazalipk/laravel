@extends('admin.layout')

@section('title', 'Session Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.sessions.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $session->title }}</h1>
                <p class="text-gray-600 mt-1">{{ $session->start_time->format('M d, Y g:i A') }} - {{ $session->end_time->format('g:i A') }}</p>
            </div>
        </div>
        <a href="{{ route('admin.sessions.edit', $session) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Session
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Session Details</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm text-gray-600 mb-1">Type</p>
            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                {{ ucfirst($session->type) }}
            </span>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Agenda</p>
            <p class="text-sm text-gray-900">{{ $session->agenda->title }}</p>
        </div>

        @if($session->track)
            <div>
                <p class="text-sm text-gray-600 mb-1">Track</p>
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $session->track->color ?? '#6366f1' }}"></div>
                    <span class="text-sm text-gray-900">{{ $session->track->name }}</span>
                </div>
            </div>
        @endif

        <div>
            <p class="text-sm text-gray-600 mb-1">Location</p>
            <p class="text-sm text-gray-900">{{ $session->location->name }}</p>
        </div>

        @if($session->max_attendees)
            <div>
                <p class="text-sm text-gray-600 mb-1">Maximum Attendees</p>
                <p class="text-sm text-gray-900">{{ $session->max_attendees }}</p>
            </div>
        @endif

        <div>
            <p class="text-sm text-gray-600 mb-1">Created</p>
            <p class="text-sm text-gray-900">{{ $session->created_at->format('M d, Y g:i A') }}</p>
        </div>
    </div>

    @if($session->description)
        <div class="mt-6">
            <p class="text-sm text-gray-600 mb-1">Description</p>
            <p class="text-sm text-gray-900">{{ $session->description }}</p>
        </div>
    @endif
</div>

@if($session->speakers->isNotEmpty())
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Speakers</h2>
        <div class="space-y-3">
            @foreach($session->speakers as $speaker)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        @if($speaker->profile_image)
                            <img src="{{ $speaker->profile_image }}" alt="{{ $speaker->full_name }}" class="w-10 h-10 rounded-full mr-3">
                        @else
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                <span class="text-indigo-600 font-semibold text-sm">{{ substr($speaker->full_name, 0, 2) }}</span>
                            </div>
                        @endif
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $speaker->full_name }}</div>
                            @if($speaker->job_title || $speaker->company)
                                <div class="text-xs text-gray-500">
                                    @if($speaker->job_title){{ $speaker->job_title }}@endif
                                    @if($speaker->job_title && $speaker->company) at @endif
                                    @if($speaker->company){{ $speaker->company }}@endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.speakers.show', $speaker) }}" class="text-indigo-600 hover:text-indigo-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if($session->lectures->isNotEmpty())
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Lectures</h2>
        <div class="space-y-3">
            @foreach($session->lectures as $lecture)
                <div class="p-4 border border-gray-200 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $lecture->topic }}</h3>
                    <div class="text-xs text-gray-500">
                        {{ $lecture->start_time->format('g:i A') }} - {{ $lecture->end_time->format('g:i A') }}
                        • {{ $lecture->speaker->full_name }}
                        • {{ $lecture->location->name }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection
