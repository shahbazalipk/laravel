@extends('admin.layout')

@section('title', 'Exhibitor Jobs')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Exhibitor Job Openings</h1>
            <p class="text-gray-600 mt-1">Review and manage job postings from all exhibitors</p>
        </div>
        <a href="{{ route('admin.exhibitors.index') }}" 
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
            Back to Exhibitors
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-6">
    <div class="flex space-x-2 border-b border-gray-200">
        <a href="{{ route('admin.exhibitor-jobs.index', ['status' => 'all']) }}" 
           class="px-4 py-2 font-medium text-sm transition {{ $status === 'all' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
            All Jobs
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $status === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $totalCount }}
            </span>
        </a>
        <a href="{{ route('admin.exhibitor-jobs.index', ['status' => 'active']) }}" 
           class="px-4 py-2 font-medium text-sm transition {{ $status === 'active' ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-600 hover:text-gray-900' }}">
            Active
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $activeCount }}
            </span>
        </a>
        <a href="{{ route('admin.exhibitor-jobs.index', ['status' => 'inactive']) }}" 
           class="px-4 py-2 font-medium text-sm transition {{ $status === 'inactive' ? 'text-gray-600 border-b-2 border-gray-600' : 'text-gray-600 hover:text-gray-900' }}">
            Inactive
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $status === 'inactive' ? 'bg-gray-200 text-gray-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $inactiveCount }}
            </span>
        </a>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Total Jobs</p>
        <p class="text-2xl font-bold text-gray-900">{{ $jobs->total() }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Active</p>
        <p class="text-2xl font-bold text-green-600">{{ $activeCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Inactive</p>
        <p class="text-2xl font-bold text-gray-600">{{ $inactiveCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Total Applications</p>
        <p class="text-2xl font-bold text-indigo-600">{{ $jobs->sum('applications_count') }}</p>
    </div>
</div>

@if($jobs->isEmpty())
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Job Openings Yet</h3>
        <p class="text-gray-600">Job postings from exhibitors will appear here for review.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach($jobs as $job)
            <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $job->title }}</h3>
                        <a href="{{ route('admin.exhibitors.show', $job->exhibitor) }}" 
                           class="text-indigo-600 hover:text-indigo-900 font-medium">
                            {{ $job->exhibitor->company_name }}
                        </a>
                    </div>
                    <div class="flex items-center gap-2">
                        <form action="{{ route('admin.exhibitor-jobs.toggle-active', $job) }}" method="POST">
                            @csrf
                            <button type="submit" class="focus:outline-none">
                                @if($job->is_active)
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 hover:bg-green-200 transition">
                                        Active
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 hover:bg-gray-200 transition">
                                        Inactive
                                    </span>
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2 mb-4">
                    @if($job->job_type)
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-sm rounded-lg">{{ $job->job_type }}</span>
                    @endif
                    @if($job->experience_level)
                        <span class="px-3 py-1 bg-purple-50 text-purple-700 text-sm rounded-lg">{{ $job->experience_level }}</span>
                    @endif
                    @if($job->location)
                        <span class="px-3 py-1 bg-gray-50 text-gray-700 text-sm rounded-lg">{{ $job->location }}</span>
                    @endif
                    @if($job->salary_range)
                        <span class="px-3 py-1 bg-green-50 text-green-700 text-sm rounded-lg">{{ $job->salary_range }}</span>
                    @endif
                </div>
                
                <p class="text-sm text-gray-700 mb-4">{{ Str::limit($job->description, 200) }}</p>
                
                @if($job->requirements)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs font-medium text-gray-700 mb-1">Requirements:</p>
                        <p class="text-xs text-gray-600">{{ Str::limit($job->requirements, 150) }}</p>
                    </div>
                @endif
                
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <div class="flex gap-4 text-sm text-gray-600">
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
                        @if($job->deadline)
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Deadline: {{ $job->deadline->format('M d, Y') }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="{{ route('admin.exhibitors.show', $job->exhibitor) }}" 
                           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm">
                            View Exhibitor
                        </a>
                        <form action="{{ route('admin.exhibitor-jobs.destroy', $job) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this job?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition text-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $jobs->appends(['status' => $status])->links() }}
    </div>
@endif
@endsection
