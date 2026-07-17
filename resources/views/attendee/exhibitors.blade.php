@extends('attendee.layout')

@section('title', 'Exhibitors')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Exhibitors</h1>
    <p class="text-gray-600">Browse companies exhibiting at the event</p>
</div>

@if($exhibitors->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Exhibitors Yet</h3>
        <p class="text-gray-600">Check back later for exhibitor information</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($exhibitors as $exhibitor)
            <a href="{{ route('attendee.exhibitors.show', $exhibitor) }}" 
               class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">
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
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition">
                            {{ $exhibitor->company_name }}
                        </h3>
                        @if($exhibitor->is_featured)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">
                                Featured
                            </span>
                        @endif
                    </div>
                    
                    @if($exhibitor->tagline)
                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($exhibitor->tagline, 80) }}</p>
                    @endif
                    
                    <div class="flex flex-wrap gap-2 mb-3">
                        @if($exhibitor->industry)
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded">
                                {{ $exhibitor->industry->name }}
                            </span>
                        @endif
                        @if($exhibitor->boothType)
                            <span class="px-2 py-1 bg-green-50 text-green-700 text-xs rounded">
                                {{ $exhibitor->boothType->name }}
                            </span>
                        @endif
                    </div>
                    
                    @if($exhibitor->booth_number)
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            Booth {{ $exhibitor->booth_number }}
                        </div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $exhibitors->links() }}
    </div>
@endif
@endsection
