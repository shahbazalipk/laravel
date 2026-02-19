@extends('attendee.layout')

@section('title', 'Partners')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Event Partners</h1>
    <p class="text-gray-600">Our valued partners supporting this event</p>
</div>

@if($partners->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Partners Yet</h3>
        <p class="text-gray-600">Check back later for partner information</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($partners as $partner)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
                @if($partner->logo)
                    <div class="h-48 bg-gray-50 flex items-center justify-center p-6">
                        <img src="{{ asset('storage/' . $partner->logo) }}" 
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
                    
                    @if($partner->description)
                        <p class="text-sm text-gray-600 mb-4">{{ Str::limit($partner->description, 120) }}</p>
                    @endif
                    
                    @if($partner->type)
                        <div class="mb-4">
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">
                                {{ ucfirst($partner->type) }} Partner
                            </span>
                        </div>
                    @endif
                    
                    <div class="space-y-2">
                        @if($partner->website)
                            <a href="{{ $partner->website }}" 
                               target="_blank"
                               class="flex items-center text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                Visit Website
                            </a>
                        @endif
                        
                        @if($partner->email)
                            <a href="mailto:{{ $partner->email }}" 
                               class="flex items-center text-sm text-gray-600 hover:text-gray-900">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                {{ $partner->email }}
                            </a>
                        @endif
                        
                        @if($partner->phone)
                            <a href="tel:{{ $partner->phone }}" 
                               class="flex items-center text-sm text-gray-600 hover:text-gray-900">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $partner->phone }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
