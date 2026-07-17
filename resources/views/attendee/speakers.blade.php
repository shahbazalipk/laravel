@extends('attendee.layout')

@section('title', 'Speakers')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Speakers</h1>
    <p class="text-gray-600">Meet our expert speakers</p>
</div>

@if($speakers->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Speakers Yet</h3>
        <p class="text-gray-600">Check back later for speaker information</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($speakers as $speaker)
            <a href="{{ route('attendee.speakers.show', $speaker) }}" 
               class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">
                @if($speaker->photo)
                    <div class="h-64 bg-gray-100">
                        <img src="{{ storage_public_url($speaker->photo) }}" 
                             alt="{{ $speaker->full_name }}"
                             class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="h-64 bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                        <span class="text-5xl font-bold text-white">
                            {{ substr($speaker->full_name, 0, 1) }}
                        </span>
                    </div>
                @endif
                
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition mb-1">
                        {{ $speaker->full_name }}
                    </h3>
                    
                    @if($speaker->title)
                        <p class="text-sm text-gray-600 mb-2">{{ $speaker->title }}</p>
                    @endif
                    
                    @if($speaker->company)
                        <p class="text-sm text-gray-500 mb-3">{{ $speaker->company }}</p>
                    @endif
                    
                    @if($speaker->bio)
                        <p class="text-sm text-gray-600 line-clamp-3">{{ Str::limit($speaker->bio, 120) }}</p>
                    @endif
                    
                    @if($speaker->sessions->count() > 0)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500">
                                {{ $speaker->sessions->count() }} {{ Str::plural('session', $speaker->sessions->count()) }}
                            </p>
                        </div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $speakers->links() }}
    </div>
@endif
@endsection
