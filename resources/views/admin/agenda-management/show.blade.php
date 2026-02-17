@extends('admin.layout')

@section('title', 'Agenda Details')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.agenda-management.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $agenda->title }}</h1>
                <p class="text-gray-600 mt-1">{{ $agenda->start_date->format('M d, Y') }} - {{ $agenda->end_date->format('M d, Y') }}</p>
            </div>
        </div>
        <a href="{{ route('admin.agenda-management.edit', $agenda) }}" 
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Agenda
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Sessions</p>
                <p class="text-2xl font-bold text-gray-900">{{ $statistics['total_sessions'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Tracks</p>
                <p class="text-2xl font-bold text-green-600">{{ $statistics['total_tracks'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Speakers</p>
                <p class="text-2xl font-bold text-blue-600">{{ $statistics['total_speakers'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Lectures</p>
                <p class="text-2xl font-bold text-purple-600">{{ $statistics['total_lectures'] }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Agenda Details -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Agenda Details</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm text-gray-600 mb-1">Status</p>
            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                {{ $agenda->status === 'published' ? 'bg-green-100 text-green-800' : '' }}
                {{ $agenda->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $agenda->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}">
                {{ ucfirst($agenda->status) }}
            </span>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Created</p>
            <p class="text-sm text-gray-900">{{ $agenda->created_at->format('M d, Y g:i A') }}</p>
        </div>
    </div>

    @if($agenda->description)
        <div class="mt-6">
            <p class="text-sm text-gray-600 mb-1">Description</p>
            <p class="text-sm text-gray-900">{{ $agenda->description }}</p>
        </div>
    @endif
</div>

<!-- Tracks -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Tracks</h2>
        <a href="{{ route('admin.tracks.create') }}" 
           class="text-indigo-600 hover:text-indigo-900 text-sm transition">
            + Add Track
        </a>
    </div>

    @if($agenda->tracks->isEmpty())
        <p class="text-gray-500 text-sm">No tracks defined yet.</p>
    @else
        <div class="space-y-2">
            @foreach($agenda->tracks as $track)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $track->color ?? '#6366f1' }}"></div>
                        <span class="text-sm font-medium text-gray-900">{{ $track->name }}</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ $track->sessions->count() }} sessions</span>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Sessions -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Sessions</h2>
        <a href="{{ route('admin.sessions.create') }}" 
           class="text-indigo-600 hover:text-indigo-900 text-sm transition">
            + Add Session
        </a>
    </div>

    @if($agenda->sessions->isEmpty())
        <p class="text-gray-500 text-sm">No sessions scheduled yet.</p>
    @else
        <div class="space-y-3">
            @foreach($agenda->sessions->sortBy('start_time') as $session)
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
                            <div class="flex items-center text-xs text-gray-500 space-x-4">
                                <span>{{ $session->start_time->format('M d, g:i A') }} - {{ $session->end_time->format('g:i A') }}</span>
                                <span>{{ $session->location->name }}</span>
                                @if($session->speakers->count() > 0)
                                    <span>{{ $session->speakers->count() }} speaker(s)</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.sessions.show', $session) }}" 
                           class="text-indigo-600 hover:text-indigo-900 ml-4">
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
