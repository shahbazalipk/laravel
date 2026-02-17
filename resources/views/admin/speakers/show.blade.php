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
                    <img src="{{ $speaker->profile_image }}" alt="{{ $speaker->full_name }}" class="w-16 h-16 rounded-full mr-4">
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
