@extends('attendee.layout')

@section('title', 'My Profile')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">My Profile</h1>
    <p class="text-gray-600">View your registration details</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            @if($registration->profile_picture)
                <img src="{{ storage_public_url($registration->profile_picture) }}" 
                     alt="{{ $registration->full_name }}"
                     class="w-32 h-32 rounded-full mx-auto mb-4 object-cover">
            @else
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 mx-auto mb-4 flex items-center justify-center">
                    <span class="text-4xl font-bold text-white">
                        {{ substr($registration->first_name, 0, 1) }}{{ substr($registration->last_name, 0, 1) }}
                    </span>
                </div>
            @endif
            
            <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $registration->full_name }}</h2>
            <p class="text-sm text-gray-600 mb-4">{{ $registration->email }}</p>
            
            <div class="space-y-2">
                <div class="px-4 py-2 bg-indigo-50 rounded-lg">
                    <p class="text-xs text-gray-600">Registration Number</p>
                    <p class="text-sm font-semibold text-indigo-600">{{ $registration->registration_number }}</p>
                </div>
                
                @if($registration->badge_number)
                <div class="px-4 py-2 bg-blue-50 rounded-lg">
                    <p class="text-xs text-gray-600">Badge Number</p>
                    <p class="text-sm font-semibold text-blue-600">{{ $registration->badge_number }}</p>
                </div>
                @endif
            </div>

            @if($registration->qr_code || $registration->registration_number)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-sm font-medium text-gray-700 mb-3">Your QR Code</p>
                <div class="bg-white p-3 rounded-lg border-2 border-gray-200 inline-block">
                    @if($registration->qr_code)
                        <img src="data:image/png;base64,{{ $registration->qr_code }}" alt="QR Code" class="w-32 h-32">
                    @else
                        <!-- Generate QR Code using Google Charts API -->
                        <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{ urlencode(json_encode([
                            'registration_number' => $registration->registration_number,
                            'badge_number' => $registration->badge_number,
                            'name' => $registration->full_name,
                            'email' => $registration->email,
                            'company' => $registration->company_name
                        ])) }}&choe=UTF-8" 
                             alt="QR Code" 
                             class="w-32 h-32">
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-2">For event check-in</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Personal Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500">Salutation</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->salutation ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Full Name</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->full_name }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Email</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->email }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Phone</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->phone ?? 'N/A' }}</p>
                </div>
                @if($registration->mobile_phone)
                <div>
                    <label class="text-xs text-gray-500">Mobile Phone</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->mobile_phone }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Professional Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Professional Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500">Job Title</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->job_title ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Department</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->department ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Company</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->company_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Company Website</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->company_website ?? 'N/A' }}</p>
                </div>
                @if($registration->industry)
                <div>
                    <label class="text-xs text-gray-500">Industry</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->industry->name }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Registration Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Registration Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500">Category</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->registrationCategory->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->registrationStatus->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Registration Type</label>
                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($registration->registration_type) }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Registered On</label>
                    <p class="text-sm font-medium text-gray-900">{{ $registration->created_at->format('M d, Y H:i') }}</p>
                </div>
                @if($registration->checked_in)
                <div>
                    <label class="text-xs text-gray-500">Checked In</label>
                    <p class="text-sm font-medium text-green-600">{{ $registration->checked_in_at->format('M d, Y H:i') }}</p>
                </div>
                @endif
                @if($registration->badge_printed)
                <div>
                    <label class="text-xs text-gray-500">Badge Printed</label>
                    <p class="text-sm font-medium text-blue-600">{{ $registration->badge_printed_at->format('M d, Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
