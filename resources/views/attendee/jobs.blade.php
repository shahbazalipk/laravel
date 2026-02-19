@extends('attendee.layout')

@section('title', 'Job Openings')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Job Openings</h1>
    <p class="text-gray-600 mt-2">Explore career opportunities from our exhibitors</p>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('attendee.jobs') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Job title, description..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Job Type</label>
            <select name="job_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                <option value="Full-time" {{ request('job_type') == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                <option value="Part-time" {{ request('job_type') == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                <option value="Contract" {{ request('job_type') == 'Contract' ? 'selected' : '' }}>Contract</option>
                <option value="Internship" {{ request('job_type') == 'Internship' ? 'selected' : '' }}>Internship</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Experience Level</label>
            <select name="experience_level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">All Levels</option>
                <option value="Entry" {{ request('experience_level') == 'Entry' ? 'selected' : '' }}>Entry Level</option>
                <option value="Mid" {{ request('experience_level') == 'Mid' ? 'selected' : '' }}>Mid Level</option>
                <option value="Senior" {{ request('experience_level') == 'Senior' ? 'selected' : '' }}>Senior Level</option>
            </select>
        </div>
        
        <div class="flex items-end gap-2">
            <button type="submit" 
                    class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                Filter
            </button>
            <a href="{{ route('attendee.jobs') }}" 
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Results Count -->
<div class="mb-4">
    <p class="text-sm text-gray-600">
        Showing {{ $jobs->firstItem() ?? 0 }} - {{ $jobs->lastItem() ?? 0 }} of {{ $jobs->total() }} job openings
    </p>
</div>

<!-- Jobs List -->
<div class="space-y-4">
    @forelse($jobs as $job)
        <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $job->title }}</h2>
                    <a href="{{ route('attendee.exhibitors.show', $job->exhibitor) }}" 
                       class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-2 mb-3">
                        @if($job->exhibitor->logo)
                            <img src="{{ asset('storage/' . $job->exhibitor->logo) }}" 
                                 alt="{{ $job->exhibitor->company_name }}"
                                 class="w-8 h-8 object-contain">
                        @endif
                        {{ $job->exhibitor->company_name }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                @if($job->deadline)
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Apply by</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $job->deadline->format('M d, Y') }}</p>
                    </div>
                @endif
            </div>
            
            <div class="flex flex-wrap gap-2 mb-4">
                @if($job->job_type)
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 text-sm rounded-lg">{{ $job->job_type }}</span>
                @endif
                @if($job->experience_level)
                    <span class="px-3 py-1 bg-purple-50 text-purple-700 text-sm rounded-lg">{{ $job->experience_level }}</span>
                @endif
                @if($job->location)
                    <span class="px-3 py-1 bg-gray-50 text-gray-700 text-sm rounded-lg flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                        {{ $job->location }}
                    </span>
                @endif
                @if($job->salary_range)
                    <span class="px-3 py-1 bg-green-50 text-green-700 text-sm rounded-lg">{{ $job->salary_range }}</span>
                @endif
            </div>
            
            <p class="text-gray-700 mb-4">{{ Str::limit($job->description, 250) }}</p>
            
            @if($job->requirements)
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Requirements:</p>
                    <p class="text-sm text-gray-600">{{ Str::limit($job->requirements, 200) }}</p>
                </div>
            @endif
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="flex gap-4 text-sm text-gray-500">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        {{ $job->views_count }} views
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ $job->applications_count }} applications
                    </span>
                </div>
                
                @if($job->application_email)
                    <a href="mailto:{{ $job->application_email }}?subject=Application for {{ $job->title }}" 
                       class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Apply Now
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Job Openings Found</h3>
            <p class="text-gray-600">Try adjusting your filters or check back later for new opportunities.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($jobs->hasPages())
    <div class="mt-6">
        {{ $jobs->links() }}
    </div>
@endif
@endsection
