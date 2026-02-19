@extends('attendee.layout')

@section('title', $speaker->full_name)

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('attendee.speakers') }}" 
       class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Speakers
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Speaker Photo & Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
            @if($speaker->photo)
                <div class="mb-6">
                    <img src="{{ asset('storage/' . $speaker->photo) }}" 
                         alt="{{ $speaker->full_name }}"
                         class="w-full h-auto rounded-lg">
                </div>
            @else
                <div class="mb-6 aspect-square bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <span class="text-6xl font-bold text-white">
                        {{ substr($speaker->full_name, 0, 1) }}
                    </span>
                </div>
            @endif
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $speaker->full_name }}</h1>
            
            @if($speaker->title)
                <p class="text-gray-600 mb-1">{{ $speaker->title }}</p>
            @endif

            @if($speaker->company)
                <p class="text-gray-500 mb-4">{{ $speaker->company }}</p>
            @endif

            <div class="space-y-3 mb-6">
                @if($speaker->email)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <a href="mailto:{{ $speaker->email }}" class="text-gray-700 hover:text-indigo-600 break-all">
                            {{ $speaker->email }}
                        </a>
                    </div>
                @endif

                @if($speaker->phone)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <a href="tel:{{ $speaker->phone }}" class="text-gray-700 hover:text-indigo-600">
                            {{ $speaker->phone }}
                        </a>
                    </div>
                @endif

                @if($speaker->website)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        <a href="{{ $speaker->website }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 break-all">
                            {{ str_replace(['http://', 'https://'], '', $speaker->website) }}
                        </a>
                    </div>
                @endif
            </div>

            <!-- Favorite Button -->
            <button onclick="toggleFavorite('App\\Models\\Speaker', {{ $speaker->id }}, this)" 
                    class="w-full px-4 py-3 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                Add to Favorites
            </button>

            <!-- Social Media -->
            @if($speaker->social_media_links)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Connect</h3>
                <div class="flex gap-2">
                    @foreach($speaker->social_media_links as $platform => $url)
                        <a href="{{ $url }}" target="_blank" 
                           class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm">
                            {{ ucfirst($platform) }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Biography -->
        @if($speaker->bio)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Biography</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $speaker->bio }}</p>
        </div>
        @endif

        <!-- Expertise -->
        @if($speaker->expertise)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Areas of Expertise</h2>
            <div class="flex flex-wrap gap-2">
                @foreach(explode(',', $speaker->expertise) as $area)
                    <span class="px-3 py-2 bg-indigo-50 text-indigo-700 text-sm rounded-lg">
                        {{ trim($area) }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Sessions -->
        @if($speaker->sessions && $speaker->sessions->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Speaking Sessions</h2>
            <div class="space-y-4">
                @foreach($speaker->sessions as $session)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $session->title }}</h3>
                                @if($session->description)
                                    <p class="text-sm text-gray-600 mb-3">{{ Str::limit($session->description, 150) }}</p>
                                @endif
                                <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $session->start_time->format('M d, Y H:i') }}
                                    </div>
                                    @if($session->location)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            </svg>
                                            {{ $session->location->name }}
                                        </div>
                                    @endif
                                    @if($session->track)
                                        <span class="px-2 py-1 text-xs rounded-full" 
                                              style="background-color: {{ $session->track->color }}20; color: {{ $session->track->color }}">
                                            {{ $session->track->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <button onclick="toggleFavorite('App\\Models\\Session', {{ $session->id }}, this)" 
                                    class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
