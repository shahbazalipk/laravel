@extends('admin.layout')

@section('title', 'Lecture Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.lectures.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $lecture->topic }}</h1>
                <p class="text-gray-600 mt-1">{{ $lecture->start_time->format('M d, Y g:i A') }} - {{ $lecture->end_time->format('g:i A') }}</p>
            </div>
        </div>
        <a href="{{ route('admin.lectures.edit', $lecture) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Lecture
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Lecture Details</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm text-gray-600 mb-1">Session</p>
            <p class="text-sm text-gray-900">{{ $lecture->session->title }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Speaker</p>
            <p class="text-sm text-gray-900">{{ $lecture->speaker->full_name }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Location</p>
            <p class="text-sm text-gray-900">{{ $lecture->location->name }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 mb-1">Created</p>
            <p class="text-sm text-gray-900">{{ $lecture->created_at->format('M d, Y g:i A') }}</p>
        </div>
    </div>

    @if($lecture->description)
        <div class="mt-6">
            <p class="text-sm text-gray-600 mb-1">Description</p>
            <p class="text-sm text-gray-900">{{ $lecture->description }}</p>
        </div>
    @endif
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Speaker Information</h2>
    
    <div class="flex items-center">
        @if($lecture->speaker->profile_image)
            <img src="{{ $lecture->speaker->profile_image }}" alt="{{ $lecture->speaker->full_name }}" class="w-16 h-16 rounded-full mr-4">
        @else
            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                <span class="text-indigo-600 font-semibold text-xl">{{ substr($lecture->speaker->full_name, 0, 2) }}</span>
            </div>
        @endif
        <div>
            <h3 class="text-sm font-semibold text-gray-900">{{ $lecture->speaker->full_name }}</h3>
            @if($lecture->speaker->job_title || $lecture->speaker->company)
                <p class="text-xs text-gray-500">
                    @if($lecture->speaker->job_title){{ $lecture->speaker->job_title }}@endif
                    @if($lecture->speaker->job_title && $lecture->speaker->company) at @endif
                    @if($lecture->speaker->company){{ $lecture->speaker->company }}@endif
                </p>
            @endif
            @if($lecture->speaker->bio)
                <p class="text-xs text-gray-600 mt-2">{{ Str::limit($lecture->speaker->bio, 150) }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
