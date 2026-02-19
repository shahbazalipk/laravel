@extends('attendee.layout')

@section('title', 'Attendees')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Attendees</h1>
    <p class="text-gray-600">Connect with other event attendees</p>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('attendee.attendees') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" 
                       name="search" 
                       id="search" 
                       value="{{ request('search') }}"
                       placeholder="Name, company, or job title..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" 
                        id="category"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Industry Filter -->
            <div>
                <label for="industry" class="block text-sm font-medium text-gray-700 mb-2">Industry</label>
                <select name="industry" 
                        id="industry"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Industries</option>
                    @foreach($industries as $industry)
                        <option value="{{ $industry->id }}" {{ request('industry') == $industry->id ? 'selected' : '' }}>
                            {{ $industry->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                Apply Filters
            </button>
            <a href="{{ route('attendee.attendees') }}" 
               class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Results Count -->
<div class="mb-4">
    <p class="text-sm text-gray-600">
        Showing {{ $attendees->firstItem() ?? 0 }} - {{ $attendees->lastItem() ?? 0 }} of {{ $attendees->total() }} attendees
    </p>
</div>

@if($attendees->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Attendees Found</h3>
        <p class="text-gray-600">Try adjusting your search or filters</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($attendees as $attendee)
            <a href="{{ route('attendee.attendees.show', $attendee) }}" 
               class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-6 group">
                <div class="flex items-start gap-4">
                    <!-- Profile Picture -->
                    @if($attendee->profile_picture)
                        <img src="{{ asset('storage/' . $attendee->profile_picture) }}" 
                             alt="{{ $attendee->full_name }}"
                             class="w-16 h-16 rounded-full object-cover flex-shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                            <span class="text-xl font-bold text-white">
                                {{ substr($attendee->first_name, 0, 1) }}{{ substr($attendee->last_name, 0, 1) }}
                            </span>
                        </div>
                    @endif

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition truncate">
                            {{ $attendee->full_name }}
                        </h3>
                        
                        @if($attendee->job_title)
                            <p class="text-sm text-gray-600 truncate">{{ $attendee->job_title }}</p>
                        @endif
                        
                        @if($attendee->company_name)
                            <p class="text-sm text-gray-500 truncate">{{ $attendee->company_name }}</p>
                        @endif

                        <div class="flex flex-wrap gap-2 mt-3">
                            @if($attendee->registrationCategory)
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded">
                                    {{ $attendee->registrationCategory->name }}
                                </span>
                            @endif
                            @if($attendee->industry)
                                <span class="px-2 py-1 bg-green-50 text-green-700 text-xs rounded">
                                    {{ $attendee->industry->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $attendees->appends(request()->query())->links() }}
    </div>
@endif
@endsection
