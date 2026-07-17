@extends('admin.layout')

@section('title', 'Ads Management')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Ads Management</h1>
            <p class="text-gray-600 mt-1">Manage advertisements displayed across the event platform</p>
        </div>
        <a href="{{ route('admin.ads.create') }}" 
           class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
            Create New Ad
        </a>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select name="type" class="w-full border-gray-300 rounded-lg">
                <option value="">All Types</option>
                <option value="banner" {{ request('type') === 'banner' ? 'selected' : '' }}>Banner</option>
                <option value="sidebar" {{ request('type') === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                <option value="popup" {{ request('type') === 'popup' ? 'selected' : '' }}>Popup</option>
                <option value="footer" {{ request('type') === 'footer' ? 'selected' : '' }}>Footer</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Placement</label>
            <select name="placement" class="w-full border-gray-300 rounded-lg">
                <option value="">All Placements</option>
                <option value="home" {{ request('placement') === 'home' ? 'selected' : '' }}>Home</option>
                <option value="exhibitors" {{ request('placement') === 'exhibitors' ? 'selected' : '' }}>Exhibitors</option>
                <option value="sessions" {{ request('placement') === 'sessions' ? 'selected' : '' }}>Sessions</option>
                <option value="speakers" {{ request('placement') === 'speakers' ? 'selected' : '' }}>Speakers</option>
                <option value="agenda" {{ request('placement') === 'agenda' ? 'selected' : '' }}>Agenda</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Total Ads</p>
        <p class="text-2xl font-bold text-gray-900">{{ $ads->total() }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Active</p>
        <p class="text-2xl font-bold text-green-600">{{ $ads->where('is_active', true)->count() }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Total Impressions</p>
        <p class="text-2xl font-bold text-indigo-600">{{ number_format($ads->sum('impressions_count')) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Total Clicks</p>
        <p class="text-2xl font-bold text-purple-600">{{ number_format($ads->sum('clicks_count')) }}</p>
    </div>
</div>

@if($ads->isEmpty())
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Ads Yet</h3>
        <p class="text-gray-600 mb-4">Create your first advertisement to display across the platform.</p>
        <a href="{{ route('admin.ads.create') }}" 
           class="inline-block px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
            Create New Ad
        </a>
    </div>
@else
    <div class="space-y-4">
        @foreach($ads as $ad)
            <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-start gap-4">
                    @if($ad->image)
                        <img src="{{ storage_public_url($ad->image) }}" 
                             alt="{{ $ad->title }}"
                             class="w-32 h-20 object-cover rounded">
                    @else
                        <div class="w-32 h-20 bg-gray-100 rounded flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $ad->title }}</h3>
                                <div class="flex gap-2 mt-1">
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded">{{ ucfirst($ad->type) }}</span>
                                    <span class="px-2 py-1 bg-purple-50 text-purple-700 text-xs rounded">{{ ucfirst($ad->placement) }}</span>
                                    <span class="px-2 py-1 bg-gray-50 text-gray-700 text-xs rounded">Order: {{ $ad->display_order }}</span>
                                </div>
                            </div>
                            
                            <form action="{{ route('admin.ads.toggle-active', $ad) }}" method="POST">
                                @csrf
                                <button type="submit" class="focus:outline-none">
                                    @if($ad->is_active)
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
                        
                        @if($ad->link_url)
                            <p class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Link:</span> {{ Str::limit($ad->link_url, 60) }}
                            </p>
                        @endif
                        
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-3 text-sm">
                            <div>
                                <p class="text-gray-500">Impressions</p>
                                <p class="font-semibold text-gray-900">{{ number_format($ad->impressions_count) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Clicks</p>
                                <p class="font-semibold text-gray-900">{{ number_format($ad->clicks_count) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">CTR</p>
                                <p class="font-semibold text-gray-900">{{ $ad->ctr }}%</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Start Date</p>
                                <p class="font-semibold text-gray-900">{{ $ad->start_date ? $ad->start_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">End Date</p>
                                <p class="font-semibold text-gray-900">{{ $ad->end_date ? $ad->end_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('admin.ads.edit', $ad) }}" 
                           class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-sm transition">
                            Edit
                        </a>
                        <form action="{{ route('admin.ads.destroy', $ad) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this ad?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded text-sm transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $ads->links() }}
    </div>
@endif
@endsection
