@extends('attendee.layout')

@section('title', 'My Favorites')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">My Favorites</h1>
    <p class="text-gray-600">Items you've marked as favorites</p>
</div>

@if($exhibitors->isEmpty() && $speakers->isEmpty() && $sessions->isEmpty() && $lectures->isEmpty() && $partners->isEmpty() && $attendees->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Favorites Yet</h3>
        <p class="text-gray-600 mb-4">Start exploring and mark items as favorites</p>
        <div class="flex justify-center gap-3">
            <a href="{{ route('attendee.exhibitors') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Browse Exhibitors
            </a>
            <a href="{{ route('attendee.speakers') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Browse Speakers
            </a>
        </div>
    </div>
@else
    <!-- Exhibitors -->
    @if($exhibitors->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Exhibitors ({{ $exhibitors->count() }})</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($exhibitors as $exhibitor)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
                    @if($exhibitor->logo)
                        <div class="h-48 bg-gray-100 flex items-center justify-center p-6">
                            <img src="{{ storage_public_url($exhibitor->logo) }}" 
                                 alt="{{ $exhibitor->company_name }}"
                                 class="max-h-full max-w-full object-contain">
                        </div>
                    @else
                        <div class="h-48 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <span class="text-4xl font-bold text-white">
                                {{ substr($exhibitor->company_name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $exhibitor->company_name }}</h3>
                        @if($exhibitor->booth_number)
                            <p class="text-sm text-gray-600 mb-3">Booth {{ $exhibitor->booth_number }}</p>
                        @endif
                        <div class="flex gap-2">
                            <a href="{{ route('attendee.exhibitors.show', $exhibitor) }}" 
                               class="flex-1 px-4 py-2 bg-indigo-600 text-white text-center rounded-lg hover:bg-indigo-700 transition text-sm">
                                View Details
                            </a>
                            <button onclick="toggleFavorite('App\\Models\\Exhibitor', {{ $exhibitor->id }}, this)" 
                                    class="px-4 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Speakers -->
    @if($speakers->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Speakers ({{ $speakers->count() }})</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($speakers as $speaker)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
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
                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $speaker->full_name }}</h3>
                        @if($speaker->title)
                            <p class="text-sm text-gray-600 mb-3">{{ $speaker->title }}</p>
                        @endif
                        <div class="flex gap-2">
                            <a href="{{ route('attendee.speakers.show', $speaker) }}" 
                               class="flex-1 px-4 py-2 bg-indigo-600 text-white text-center rounded-lg hover:bg-indigo-700 transition text-sm">
                                View Details
                            </a>
                            <button onclick="toggleFavorite('App\\Models\\Speaker', {{ $speaker->id }}, this)" 
                                    class="px-4 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Sessions -->
    @if($sessions->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Sessions ({{ $sessions->count() }})</h2>
        <div class="space-y-4">
            @foreach($sessions as $session)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $session->title }}</h3>
                            @if($session->description)
                                <p class="text-sm text-gray-600 mb-3">{{ Str::limit($session->description, 150) }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                                <span>{{ $session->start_time->format('M d, H:i') }}</span>
                                @if($session->location)
                                    <span>• {{ $session->location->name }}</span>
                                @endif
                            </div>
                        </div>
                        <button onclick="toggleFavorite('App\\Models\\Session', {{ $session->id }}, this)" 
                                class="px-4 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex-shrink-0">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Partners -->
    @if($partners->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Partners ({{ $partners->count() }})</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($partners as $partner)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
                    @if($partner->logo)
                        <div class="h-48 bg-gray-50 flex items-center justify-center p-6">
                            <img src="{{ storage_public_url($partner->logo) }}" 
                                 alt="{{ $partner->name }}"
                                 class="max-h-full max-w-full object-contain">
                        </div>
                    @else
                        <div class="h-48 bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                            <span class="text-4xl font-bold text-white">
                                {{ substr($partner->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $partner->name }}</h3>
                        <div class="flex gap-2">
                            @if($partner->website)
                                <a href="{{ $partner->website }}" target="_blank"
                                   class="flex-1 px-4 py-2 bg-indigo-600 text-white text-center rounded-lg hover:bg-indigo-700 transition text-sm">
                                    Visit Website
                                </a>
                            @endif
                            <button onclick="toggleFavorite('App\\Models\\Partner', {{ $partner->id }}, this)" 
                                    class="px-4 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Attendees -->
    @if($attendees->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Attendees ({{ $attendees->count() }})</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($attendees as $attendee)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-start gap-4">
                        @if($attendee->profile_picture)
                            <img src="{{ storage_public_url($attendee->profile_picture) }}" 
                                 alt="{{ $attendee->full_name }}"
                                 class="w-16 h-16 rounded-full object-cover flex-shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-xl font-bold text-white">
                                    {{ substr($attendee->first_name, 0, 1) }}{{ substr($attendee->last_name, 0, 1) }}
                                </span>
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-gray-900 truncate">{{ $attendee->full_name }}</h3>
                            @if($attendee->job_title)
                                <p class="text-sm text-gray-600 truncate">{{ $attendee->job_title }}</p>
                            @endif
                            @if($attendee->company_name)
                                <p class="text-sm text-gray-500 truncate">{{ $attendee->company_name }}</p>
                            @endif
                        </div>
                        
                        <button onclick="toggleFavorite('App\\Models\\Registration', {{ $attendee->id }}, this)" 
                                class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex-shrink-0">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
@endif

<script>
function toggleFavorite(type, id, button) {
    fetch('{{ route('attendee.favorites.toggle') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type, id })
    })
    .then(response => response.json())
    .then(data => {
        // Remove the item from the page
        button.closest('.bg-white').remove();
        
        // Check if section is empty and reload page
        setTimeout(() => {
            const sections = document.querySelectorAll('.bg-white.rounded-xl');
            if (sections.length === 0) {
                location.reload();
            }
        }, 300);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update favorite');
    });
}
</script>
@endsection
