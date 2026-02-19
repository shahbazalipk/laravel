@extends('attendee.layout')

@section('title', 'Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 text-white mb-8">
    <h2 class="text-3xl font-bold mb-2">Welcome, {{ $registration->first_name }}!</h2>
    <p class="text-indigo-100">Here's your registration information and event details</p>
</div>

<!-- Registration Status -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Registration Status</p>
                <p class="text-2xl font-bold text-gray-900">
                    {{ $registration->registrationStatus->name ?? 'N/A' }}
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Check-in Status</p>
                <p class="text-2xl font-bold text-gray-900">
                    {{ $registration->checked_in ? 'Checked In' : 'Not Checked In' }}
                </p>
            </div>
            <div class="w-12 h-12 {{ $registration->checked_in ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 {{ $registration->checked_in ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Badge Status</p>
                <p class="text-2xl font-bold text-gray-900">
                    {{ $registration->badge_printed ? 'Printed' : 'Not Printed' }}
                </p>
            </div>
            <div class="w-12 h-12 {{ $registration->badge_printed ? 'bg-blue-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 {{ $registration->badge_printed ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <a href="{{ route('attendee.exhibitors') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center group-hover:bg-purple-200 transition">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <span class="text-3xl font-bold text-gray-900">{{ $stats['exhibitors'] }}</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Exhibitors</h3>
        <p class="text-sm text-gray-600">Browse exhibiting companies</p>
    </a>

    <a href="{{ route('attendee.speakers') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center group-hover:bg-blue-200 transition">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <span class="text-3xl font-bold text-gray-900">{{ $stats['speakers'] }}</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Speakers</h3>
        <p class="text-sm text-gray-600">Meet our expert speakers</p>
    </a>

    <a href="{{ route('attendee.sessions') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-200 transition">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <span class="text-3xl font-bold text-gray-900">{{ $stats['sessions'] }}</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Sessions</h3>
        <p class="text-sm text-gray-600">View all event sessions</p>
    </a>
</div>

<!-- Registration Details -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Personal Information -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Personal Information
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Registration Number</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->registration_number }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Full Name</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->full_name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Email</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->email }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Phone</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->phone ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Job Title</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->job_title ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-sm text-gray-600">Company</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->company_name ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- Registration Details -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Registration Details
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Category</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->registrationCategory->name ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Registration Type</span>
                <span class="text-sm font-medium text-gray-900">{{ ucfirst($registration->registration_type) }}</span>
            </div>
            @if($registration->badge_number)
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Badge Number</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->badge_number }}</span>
            </div>
            @endif
            @if($registration->checked_in_at)
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Checked In At</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->checked_in_at->format('M d, Y H:i') }}</span>
            </div>
            @endif
            <div class="flex justify-between py-2">
                <span class="text-sm text-gray-600">Registered On</span>
                <span class="text-sm font-medium text-gray-900">{{ $registration->created_at->format('M d, Y') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Section -->
<div class="mt-6 bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4 text-center">Your QR Code</h3>
    <div class="flex justify-center">
        <div class="bg-white p-4 rounded-lg border-2 border-gray-200">
            @if($registration->qr_code)
                <img src="data:image/png;base64,{{ $registration->qr_code }}" alt="QR Code" class="w-48 h-48">
            @else
                <!-- Generate QR Code using Google Charts API -->
                <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ urlencode(json_encode([
                    'registration_number' => $registration->registration_number,
                    'badge_number' => $registration->badge_number,
                    'name' => $registration->full_name,
                    'email' => $registration->email,
                    'company' => $registration->company_name
                ])) }}&choe=UTF-8" 
                     alt="QR Code" 
                     class="w-48 h-48">
            @endif
        </div>
    </div>
    <p class="text-center text-sm text-gray-600 mt-4">
        Show this QR code at the event for check-in
    </p>
</div>
@endsection

