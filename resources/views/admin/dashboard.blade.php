@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="mt-2 text-gray-600">Welcome to {{ $event->title }} management</p>
</div>

<!-- Main Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Total Registrations -->
    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 overflow-hidden shadow-lg rounded-lg">
        <div class="p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm font-medium">Total Registrations</p>
                    <p class="text-4xl font-bold mt-2">{{ number_format($stats['total_registrations']) }}</p>
                    <p class="text-indigo-100 text-xs mt-2">
                        <span class="font-semibold">+{{ $stats['today_registrations'] }}</span> today
                    </p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Exhibitors -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 overflow-hidden shadow-lg rounded-lg">
        <div class="p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Exhibitors</p>
                    <p class="text-4xl font-bold mt-2">{{ number_format($stats['total_exhibitors']) }}</p>
                    <p class="text-green-100 text-xs mt-2">Active companies</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 overflow-hidden shadow-lg rounded-lg">
        <div class="p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Total Sessions</p>
                    <p class="text-4xl font-bold mt-2">{{ number_format($stats['total_sessions']) }}</p>
                    <p class="text-purple-100 text-xs mt-2">Scheduled events</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Speakers -->
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 overflow-hidden shadow-lg rounded-lg">
        <div class="p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Total Speakers</p>
                    <p class="text-4xl font-bold mt-2">{{ number_format($stats['total_speakers']) }}</p>
                    <p class="text-orange-100 text-xs mt-2">Industry experts</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Registration Trend Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Registration Trend (Last 7 Days)</h2>
        <div style="position: relative; height: 300px;">
            <canvas id="registrationTrendChart"></canvas>
        </div>
    </div>

    <!-- Category Distribution Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Registration by Category</h2>
        <div style="position: relative; height: 300px;">
            <canvas id="categoryDistributionChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Lists -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top Exhibitors -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Top Exhibitors</h2>
            <a href="{{ route('admin.exhibitors.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($topExhibitors as $exhibitor)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center space-x-3">
                        @if($exhibitor->logo)
                            <img src="{{ asset('storage/' . $exhibitor->logo) }}" 
                                 alt="{{ $exhibitor->company_name }}"
                                 class="w-10 h-10 rounded object-cover">
                        @else
                            <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center">
                                <span class="text-green-600 font-semibold text-sm">{{ substr($exhibitor->company_name, 0, 2) }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-900">{{ $exhibitor->company_name }}</p>
                            <p class="text-xs text-gray-500">{{ $exhibitor->booth_number ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1 text-yellow-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">{{ $exhibitor->favorites_count }}</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No exhibitors yet</p>
            @endforelse
        </div>
    </div>

    <!-- Top Sessions -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Top Sessions</h2>
            <a href="{{ route('admin.sessions.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($topSessions as $session)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ Str::limit($session->title, 40) }}</p>
                        <p class="text-xs text-gray-500">{{ $session->start_time ? $session->start_time->format('M d, g:i A') : 'TBD' }}</p>
                    </div>
                    <div class="flex items-center space-x-1 text-yellow-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">{{ $session->favorites_count }}</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No sessions yet</p>
            @endforelse
        </div>
    </div>
</div>

<!-- More Top Lists -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top Speakers -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Top Speakers</h2>
            <a href="{{ route('admin.speakers.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($topSpeakers as $speaker)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center space-x-3">
                        @if($speaker->profile_image)
                            <img src="{{ asset('storage/' . $speaker->profile_image) }}" 
                                 alt="{{ $speaker->full_name }}"
                                 class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <span class="text-purple-600 font-semibold text-sm">{{ substr($speaker->full_name, 0, 2) }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-900">{{ $speaker->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $speaker->job_title ?? 'Speaker' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1 text-yellow-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">{{ $speaker->favorites_count }}</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No speakers yet</p>
            @endforelse
        </div>
    </div>

    <!-- Top Categories -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Top Categories</h2>
            <a href="{{ route('admin.registration-categories.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($topCategories as $category)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $category->name }}</p>
                        <p class="text-xs text-gray-500">{{ $category->registrations_count }} registrations</p>
                    </div>
                    <div class="w-16 bg-gray-200 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" 
                             style="width: {{ $stats['total_registrations'] > 0 ? ($category->registrations_count / $stats['total_registrations'] * 100) : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No categories yet</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('admin.registrations.index') }}" 
           class="flex flex-col items-center justify-center p-4 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
            <svg class="w-8 h-8 text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-900">Registrations</span>
        </a>
        
        <a href="{{ route('admin.exhibitors.index') }}" 
           class="flex flex-col items-center justify-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition">
            <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <span class="text-sm font-medium text-gray-900">Exhibitors</span>
        </a>
        
        <a href="{{ route('admin.sessions.index') }}" 
           class="flex flex-col items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
            <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-900">Sessions</span>
        </a>
        
        <a href="{{ route('admin.speakers.index') }}" 
           class="flex flex-col items-center justify-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition">
            <svg class="w-8 h-8 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-900">Speakers</span>
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Registration Trend Chart
    const trendCtx = document.getElementById('registrationTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($trendData, 'date')) !!},
                datasets: [{
                    label: 'Registrations',
                    data: {!! json_encode(array_column($trendData, 'count')) !!},
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Category Distribution Chart
    const categoryCtx = document.getElementById('categoryDistributionChart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($categoryDistribution->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($categoryDistribution->pluck('count')) !!},
                    backgroundColor: [
                        'rgb(99, 102, 241)',
                        'rgb(34, 197, 94)',
                        'rgb(168, 85, 247)',
                        'rgb(251, 146, 60)',
                        'rgb(236, 72, 153)',
                        'rgb(14, 165, 233)',
                        'rgb(234, 179, 8)',
                        'rgb(239, 68, 68)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.5,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 10
                        }
                    }
                }
            }
        });
    }
</script>
@endsection
