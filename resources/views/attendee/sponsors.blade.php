@extends('attendee.layout')

@section('title', 'Sponsors')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Event Sponsors</h1>
    <p class="text-gray-600">Thank you to our sponsors for making this event possible</p>
</div>

@if($sponsors->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Sponsors Yet</h3>
        <p class="text-gray-600">Check back later for sponsor information</p>
    </div>
@else
    @foreach($sponsors as $tier => $tierSponsors)
        <div class="mb-12">
            <!-- Tier Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-1">
                    {{ ucfirst($tier) }} Sponsors
                </h2>
                <div class="h-1 w-20 bg-gradient-to-r from-indigo-600 to-purple-600 rounded"></div>
            </div>

            <!-- Sponsors Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ $tier === 'platinum' ? '2' : ($tier === 'gold' ? '3' : '4') }} gap-6">
                @foreach($tierSponsors as $sponsor)
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
                        @if($sponsor->logo)
                            <div class="h-{{ $tier === 'platinum' ? '64' : ($tier === 'gold' ? '48' : '40') }} bg-gray-50 flex items-center justify-center p-8">
                                <img src="{{ storage_public_url($sponsor->logo) }}" 
                                     alt="{{ $sponsor->name }}"
                                     class="max-h-full max-w-full object-contain">
                            </div>
                        @else
                            <div class="h-{{ $tier === 'platinum' ? '64' : ($tier === 'gold' ? '48' : '40') }} bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                <span class="text-4xl font-bold text-white">
                                    {{ substr($sponsor->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $sponsor->name }}</h3>
                            
                            @if($sponsor->description)
                                <p class="text-sm text-gray-600 mb-4">{{ Str::limit($sponsor->description, 120) }}</p>
                            @endif
                            
                            <div class="flex flex-wrap gap-3">
                                @if($sponsor->website)
                                    <a href="{{ $sponsor->website }}" 
                                       target="_blank"
                                       class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                        </svg>
                                        Visit Website
                                    </a>
                                @endif
                                
                                @if($sponsor->email)
                                    <a href="mailto:{{ $sponsor->email }}" 
                                       class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Contact
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif
@endsection
