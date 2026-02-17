@extends('admin.layout')

@section('title', 'Location Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.locations.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $location->name }}</h1>
                @if($location->room_number)
                    <p class="text-gray-600 mt-1">Room {{ $location->room_number }}</p>
                @endif
            </div>
        </div>
        <a href="{{ route('admin.locations.edit', $location) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Location
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Location Details</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if($location->capacity)
            <div>
                <p class="text-sm text-gray-600 mb-1">Capacity</p>
                <p class="text-sm text-gray-900">{{ $location->capacity }} attendees</p>
            </div>
        @endif

        <div>
            <p class="text-sm text-gray-600 mb-1">Sessions</p>
            <p class="text-sm text-gray-900">{{ $location->sessions->count() }} session(s)</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Lectures</p>
            <p class="text-sm text-gray-900">{{ $location->lectures->count() }} lecture(s)</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Created</p>
            <p class="text-sm text-gray-900">{{ $location->created_at->format('M d, Y g:i A') }}</p>
        </div>
    </div>

    @if($location->address)
        <div class="mt-6">
            <p class="text-sm text-gray-600 mb-1">Address</p>
            <p class="text-sm text-gray-900">{{ $location->address }}</p>
        </div>
    @endif
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Sessions at this Location</h2>
    </div>

    @if($location->sessions->isEmpty())
        <p class="text-gray-500 text-sm">No sessions scheduled at this location yet.</p>
    @else
        <div class="space-y-3">
            @foreach($location->sessions->sortBy('start_time') as $session)
                <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800 mr-2">
                                    {{ ucfirst($session->type) }}
                                </span>
                                @if($session->track)
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 rounded-full mr-1" style="background-color: {{ $session->track->color ?? '#6366f1' }}"></div>
                                        <span class="text-xs text-gray-600">{{ $session->track->name }}</span>
                                    </div>
                                @endif
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $session->title }}</h3>
                            <div class="text-xs text-gray-500">
                                {{ $session->start_time->format('M d, g:i A') }} - {{ $session->end_time->format('g:i A') }}
                            </div>
                        </div>
                        <a href="{{ route('admin.sessions.show', $session) }}" class="text-indigo-600 hover:text-indigo-900 ml-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
