@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="mt-2 text-gray-600">Welcome to {{ $event->title }} management</p>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Tracks</dt>
                        <dd class="text-3xl font-semibold text-gray-900">{{ $stats['total_tracks'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Sessions</dt>
                        <dd class="text-3xl font-semibold text-gray-900">{{ $stats['total_sessions'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Featured</dt>
                        <dd class="text-3xl font-semibold text-gray-900">{{ $stats['featured_sessions'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Upcoming</dt>
                        <dd class="text-3xl font-semibold text-gray-900">{{ $stats['upcoming_sessions'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="space-y-3">
            <a href="{{ route('admin.tracks.create') }}" 
               class="block w-full bg-indigo-600 text-white text-center px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                Add New Track
            </a>
            <a href="{{ route('admin.sessions.create') }}" 
               class="block w-full bg-green-600 text-white text-center px-4 py-2 rounded-md hover:bg-green-700 transition">
                Add New Session
            </a>
            <a href="{{ route('admin.speakers.create') }}" 
               class="block w-full bg-purple-600 text-white text-center px-4 py-2 rounded-md hover:bg-purple-700 transition">
                Add New Speaker
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Event Information</h2>
        <dl class="space-y-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Event Name</dt>
                <dd class="text-sm text-gray-900">{{ $event->title }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Dates</dt>
                <dd class="text-sm text-gray-900">{{ $event->start_date->format('M d, Y') }} - {{ $event->end_date->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Location</dt>
                <dd class="text-sm text-gray-900">{{ $event->location ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Format</dt>
                <dd class="text-sm text-gray-900">{{ ucfirst($event->format) }}</dd>
            </div>
        </dl>
    </div>
</div>

<!-- Recent Tracks -->
<div class="bg-white shadow rounded-lg p-6 mb-8">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Tracks</h2>
        <a href="{{ route('admin.tracks.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all →</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse($tracks->take(3) as $track)
            <div class="border-l-4 p-4 bg-gray-50 rounded" style="border-color: {{ $track->color ?? '#6366f1' }}">
                <h3 class="font-semibold text-gray-900">{{ $track->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $track->sessions ? $track->sessions->count() : 0 }} sessions</p>
            </div>
        @empty
            <p class="text-gray-500 col-span-3">No tracks yet</p>
        @endforelse
    </div>
</div>

<!-- Recent Sessions -->
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Recent Sessions</h2>
        <a href="{{ route('admin.sessions.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all →</a>
    </div>
    <div class="space-y-3">
        @forelse($agendaItems as $item)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <div class="flex-1">
                    <h3 class="font-medium text-gray-900">{{ $item->title }}</h3>
                    <p class="text-sm text-gray-600">{{ $item->start_time->format('M d, Y g:i A') }}</p>
                </div>
                <span class="px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                    {{ ucfirst($item->type) }}
                </span>
            </div>
        @empty
            <p class="text-gray-500">No sessions yet</p>
        @endforelse
    </div>
</div>
@endsection
