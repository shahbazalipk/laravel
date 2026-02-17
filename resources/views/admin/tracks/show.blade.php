@extends('admin.layout')

@section('title', 'Track Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.tracks.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded border-2 border-gray-200 mr-3" style="background-color: {{ $track->color ?? '#6366f1' }}"></div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $track->name }}</h1>
                    <p class="text-gray-600 mt-1">{{ $track->agenda->title }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.tracks.edit', $track) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Track
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Track Details</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm text-gray-600 mb-1">Color</p>
            <div class="flex items-center">
                <div class="w-6 h-6 rounded border-2 border-gray-200 mr-2" style="background-color: {{ $track->color ?? '#6366f1' }}"></div>
                <span class="text-sm text-gray-900 font-mono">{{ $track->color ?? '#6366f1' }}</span>
            </div>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Sort Order</p>
            <p class="text-sm text-gray-900">{{ $track->sort_order ?? 0 }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Sessions</p>
            <p class="text-sm text-gray-900">{{ $track->sessions->count() }} session(s)</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Created</p>
            <p class="text-sm text-gray-900">{{ $track->created_at->format('M d, Y g:i A') }}</p>
        </div>
    </div>

    @if($track->description)
        <div class="mt-6">
            <p class="text-sm text-gray-600 mb-1">Description</p>
            <p class="text-sm text-gray-900">{{ $track->description }}</p>
        </div>
    @endif
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Sessions</h2>
        <a href="{{ route('admin.sessions.create') }}" class="text-indigo-600 hover:text-indigo-900 text-sm transition">+ Add Session</a>
    </div>

    @if($track->sessions->isEmpty())
        <p class="text-gray-500 text-sm">No sessions in this track yet.</p>
    @else
        <div class="space-y-3">
            @foreach($track->sessions->sortBy('start_time') as $session)
                <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800 mb-2 inline-block">
                                {{ ucfirst($session->type) }}
                            </span>
                            <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $session->title }}</h3>
                            <div class="flex items-center text-xs text-gray-500 space-x-4">
                                <span>{{ $session->start_time->format('M d, g:i A') }} - {{ $session->end_time->format('g:i A') }}</span>
                                <span>{{ $session->location->name }}</span>
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
