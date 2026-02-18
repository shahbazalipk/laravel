@extends('admin.layout')

@section('title', 'Registration Details')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.registrations.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Registration Details</h1>
                <p class="text-gray-600 mt-1">{{ $registration->registration_number }}</p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.registrations.edit', $registration) }}" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Edit Registration
            </a>
            @if(!$registration->checked_in)
            <form action="{{ route('admin.registrations.check-in', $registration) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Check In
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- Status Badges -->
<div class="mb-6 flex space-x-3">
    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
        {{ $registration->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
        {{ $registration->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
        {{ $registration->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
        {{ $registration->payment_status === 'refunded' ? 'bg-gray-100 text-gray-800' : '' }}">
        Payment: {{ ucfirst($registration->payment_status) }}
    </span>
    
    @if($registration->checked_in)
    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
        Checked In
    </span>
    @endif
    
    @if($registration->email_verified)
    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
        Email Verified
    </span>
    @endif
    
    @if($registration->badge_printed)
    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
        Badge Printed
    </span>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Sidebar with Profile Picture -->
    <div class="lg:col-span-1">
        @if($registration->profile_picture)
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Profile Picture</h2>
            <div class="flex justify-center">
                <img src="{{ asset('storage/' . $registration->profile_picture) }}" 
                     alt="{{ $registration->full_name }}" 
                     class="w-48 h-48 rounded-full object-cover border-4 border-gray-200">
            </div>
        </div>
        @endif
        
        <!-- QR Code and Badge -->
        @if($registration->qr_code)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Badge QR Code</h2>
            <div class="flex justify-center">
                <img src="data:image/png;base64,{{ $registration->qr_code }}" 
                     alt="QR Code" 
                     class="w-32 h-32">
            </div>
            @if($registration->badge_number)
                <p class="text-center text-sm text-gray-600 mt-2">Badge: {{ $registration->badge_number }}</p>
            @endif
            @if(!$registration->badge_printed)
            <form action="{{ route('admin.registrations.print-badge', $registration) }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="w-full px-3 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition">
                    Mark as Printed
                </button>
            </form>
            @endif
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Personal Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Full Name</p>
                    <p class="font-medium text-gray-900">{{ $registration->full_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="font-medium text-gray-900">{{ $registration->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p class="font-medium text-gray-900">{{ $registration->phone }}</p>
                </div>
                @if($registration->mobile_phone)
                <div>
                    <p class="text-sm text-gray-600">Mobile</p>
                    <p class="font-medium text-gray-900">{{ $registration->mobile_phone }}</p>
                </div>
                @endif
                @if($registration->job_title)
                <div>
                    <p class="text-sm text-gray-600">Job Title</p>
                    <p class="font-medium text-gray-900">{{ $registration->job_title }}</p>
                </div>
                @endif
                @if($registration->department)
                <div>
                    <p class="text-sm text-gray-600">Department</p>
                    <p class="font-medium text-gray-900">{{ $registration->department }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Company Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Company Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Company Name</p>
                    <p class="font-medium text-gray-900">{{ $registration->company_name }}</p>
                </div>
                @if($registration->industry)
                <div>
                    <p class="text-sm text-gray-600">Industry</p>
                    <p class="font-medium text-gray-900">{{ $registration->industry->name }}</p>
                </div>
                @endif
                @if($registration->businessActivity)
                <div>
                    <p class="text-sm text-gray-600">Business Activity</p>
                    <p class="font-medium text-gray-900">{{ $registration->businessActivity->name }}</p>
                </div>
                @endif
                @if($registration->company_size)
                <div>
                    <p class="text-sm text-gray-600">Company Size</p>
                    <p class="font-medium text-gray-900">{{ $registration->company_size }}</p>
                </div>
                @endif
                @if($registration->company_website)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600">Website</p>
                    <a href="{{ $registration->company_website }}" target="_blank" class="font-medium text-indigo-600 hover:text-indigo-800">
                        {{ $registration->company_website }}
                    </a>
                </div>
                @endif
                @if($registration->company_address || $registration->city)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600">Address</p>
                    <p class="font-medium text-gray-900">
                        {{ $registration->company_address }}
                        @if($registration->city), {{ $registration->city }}@endif
                        @if($registration->state), {{ $registration->state }}@endif
                        @if($registration->postal_code) {{ $registration->postal_code }}@endif
                        @if($registration->country)<br>{{ $registration->country }}@endif
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Category-Specific Information -->
        @if($registration->professional_student_id || $registration->membership_id || $registration->professional_id_document_path)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Category Requirements</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($registration->membership_id)
                <div>
                    <p class="text-sm text-gray-600">Membership ID</p>
                    <p class="font-medium text-gray-900">{{ $registration->membership_id }}</p>
                    @if($registration->membership_validated)
                        <span class="text-xs text-green-600">✓ Validated</span>
                    @endif
                </div>
                @endif
                @if($registration->professional_student_id)
                <div>
                    <p class="text-sm text-gray-600">Professional/Student ID</p>
                    <p class="font-medium text-gray-900">{{ $registration->professional_student_id }}</p>
                </div>
                @endif
                @if($registration->professional_id_document_path)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600 mb-2">ID Document</p>
                    <a href="{{ asset('storage/' . $registration->professional_id_document_path) }}" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        View Document
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Additional Information -->
        @if($registration->dietary_requirements || $registration->special_needs || $registration->tshirt_size)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($registration->dietary_requirements)
                <div>
                    <p class="text-sm text-gray-600">Dietary Requirements</p>
                    <p class="font-medium text-gray-900">{{ $registration->dietary_requirements }}</p>
                </div>
                @endif
                @if($registration->special_needs)
                <div>
                    <p class="text-sm text-gray-600">Special Needs</p>
                    <p class="font-medium text-gray-900">{{ $registration->special_needs }}</p>
                </div>
                @endif
                @if($registration->tshirt_size)
                <div>
                    <p class="text-sm text-gray-600">T-Shirt Size</p>
                    <p class="font-medium text-gray-900">{{ $registration->tshirt_size }}</p>
                </div>
                @endif
                @if($registration->how_did_you_hear)
                <div>
                    <p class="text-sm text-gray-600">How Did You Hear</p>
                    <p class="font-medium text-gray-900">{{ $registration->how_did_you_hear }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Registration Details -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Registration Details</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Category</p>
                    <p class="font-medium text-gray-900">{{ $registration->registrationCategory->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Type</p>
                    <p class="font-medium text-gray-900">{{ ucfirst($registration->registration_type) }}</p>
                </div>
                @if($registration->exhibitor)
                <div>
                    <p class="text-sm text-gray-600">Exhibitor</p>
                    <p class="font-medium text-gray-900">{{ $registration->exhibitor->company_name }}</p>
                </div>
                @endif
                @if($registration->group)
                <div>
                    <p class="text-sm text-gray-600">Group</p>
                    <p class="font-medium text-gray-900">{{ $registration->group->group_name }}</p>
                </div>
                @endif
                <div>
                    <p class="text-sm text-gray-600">Registered On</p>
                    <p class="font-medium text-gray-900">{{ $registration->created_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Information</h3>
            
            <!-- Price Breakdown -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Price Breakdown</h4>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Base Price:</span>
                        <span class="font-medium text-gray-900">{{ number_format($registration->base_price, 2) }} {{ $registration->currency }}</span>
                    </div>
                    
                    @if($registration->tax_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Tax ({{ $registration->registrationCategory->vat_percentage ?? $registration->event->vat_percentage ?? 0 }}%):
                        </span>
                        <span class="font-medium text-gray-900">{{ number_format($registration->tax_amount, 2) }} {{ $registration->currency }}</span>
                    </div>
                    @endif
                    
                    <div class="border-t border-gray-300 pt-2 mt-2"></div>
                    
                    <div class="flex justify-between text-base font-bold">
                        <span class="text-gray-900">Total Amount:</span>
                        <span class="text-indigo-600">{{ number_format($registration->total_amount, 2) }} {{ $registration->currency }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Payment Status:</span>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $registration->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $registration->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $registration->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $registration->payment_status === 'refunded' ? 'bg-gray-100 text-gray-800' : '' }}">
                        {{ ucfirst($registration->payment_status) }}
                    </span>
                </div>

                @if($registration->payment_method)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Payment Method:</span>
                    <span class="font-medium text-gray-900">{{ $registration->payment_method }}</span>
                </div>
                @endif

                @if($registration->payment_reference)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Payment Reference:</span>
                    <span class="font-medium text-gray-900">{{ $registration->payment_reference }}</span>
                </div>
                @endif

                @if($registration->payment_date)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Payment Date:</span>
                    <span class="font-medium text-gray-900">{{ $registration->payment_date->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Check-in Information -->
        @if($registration->checked_in)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Check-In</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Checked In At</p>
                    <p class="font-medium text-gray-900">{{ $registration->checked_in_at->format('M d, Y g:i A') }}</p>
                </div>
                @if($registration->checked_in_by)
                <div>
                    <p class="text-sm text-gray-600">Checked In By</p>
                    <p class="font-medium text-gray-900">{{ $registration->checked_in_by }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
