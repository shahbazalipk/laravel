@extends('attendee.layout')

@section('title', 'Agenda')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Event Agenda</h1>
    <p class="text-gray-600">Full schedule of event sessions</p>
</div>

@if($sessions->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Agenda Yet</h3>
        <p class="text-gray-600">Check back later for the event schedule</p>
    </div>
@else
    @foreach($sessions as $date => $daySessions)
        <div class="mb-8">
            <!-- Date Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl p-6 mb-4">
                <h2 class="text-2xl font-bold">
                    {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                </h2>
            </div>

            <!-- Sessions for this day -->
            <div class="space-y-4">
                @foreach($daySessions as $session)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Time Column -->
                            <div class="flex-shrink-0 md:w-32">
                                <div class="text-lg font-bold text-indigo-600">
                                    {{ $session->start_time->format('H:i') }}
                                </div>
                                @if($session->end_time)
                                    <div class="text-sm text-gray-500">
                                        to {{ $session->end_time->format('H:i') }}
                                    </div>
                                @endif
                            </div>

                            <!-- Session Details -->
                            <div class="flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-4 mb-3">
                                    <h3 class="text-xl font-bold text-gray-900">{{ $session->title }}</h3>
                                    @if($session->track)
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full"
                                              style="background-color: {{ $session->track->color }}20; color: {{ $session->track->color }}">
                                            {{ $session->track->name }}
                                        </span>
                                    @endif
                                </div>

                                @if($session->description)
                                    <p class="text-gray-600 mb-4">{{ $session->description }}</p>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @if($session->location)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            </svg>
                                            <span class="font-medium">{{ $session->location->name }}</span>
                                        </div>
                                    @endif

                                    @if($session->speakers->isNotEmpty())
                                        <div class="flex items-start text-sm text-gray-600">
                                            <svg class="w-5 h-5 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <div>
                                                @foreach($session->speakers as $speaker)
                                                    <div class="font-medium">{{ $speaker->full_name }}</div>
                                                    @if($speaker->title)
                                                        <div class="text-xs text-gray-500">{{ $speaker->title }}</div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif
@endsection
