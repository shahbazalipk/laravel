@extends('attendee.layout')

@section('title', $registration->full_name)

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('attendee.attendees') }}" 
       class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Attendees
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
            <!-- Profile Picture -->
            <div class="text-center mb-6">
                @if($registration->profile_picture)
                    <img src="{{ asset('storage/' . $registration->profile_picture) }}" 
                         alt="{{ $registration->full_name }}"
                         class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-gray-100">
                @else
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 mx-auto mb-4 flex items-center justify-center border-4 border-gray-100">
                        <span class="text-4xl font-bold text-white">
                            {{ substr($registration->first_name, 0, 1) }}{{ substr($registration->last_name, 0, 1) }}
                        </span>
                    </div>
                @endif
                
                <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $registration->full_name }}</h1>
                
                @if($registration->job_title)
                    <p class="text-sm text-gray-600 mb-1">{{ $registration->job_title }}</p>
                @endif
                
                @if($registration->company_name)
                    <p class="text-sm text-gray-500">{{ $registration->company_name }}</p>
                @endif
            </div>

            <!-- Category Badge -->
            @if($registration->registrationCategory)
                <div class="mb-4 text-center">
                    <span class="px-4 py-2 bg-indigo-100 text-indigo-800 text-sm font-semibold rounded-full">
                        {{ $registration->registrationCategory->name }}
                    </span>
                </div>
            @endif

            <!-- Contact Info -->
            <div class="space-y-3 pt-4 border-t border-gray-200">
                @if($registration->email)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <a href="mailto:{{ $registration->email }}" class="text-gray-700 hover:text-indigo-600 transition break-all">
                            {{ $registration->email }}
                        </a>
                    </div>
                @endif

                @if($registration->phone)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <a href="tel:{{ $registration->phone }}" class="text-gray-700 hover:text-indigo-600 transition">
                            {{ $registration->phone }}
                        </a>
                    </div>
                @endif

                @if($registration->company_website)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        <a href="{{ $registration->company_website }}" target="_blank" class="text-gray-700 hover:text-indigo-600 transition break-all">
                            {{ str_replace(['http://', 'https://'], '', $registration->company_website) }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Professional Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Professional Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($registration->job_title)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Job Title</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->job_title }}</p>
                    </div>
                @endif

                @if($registration->department)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Department</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->department }}</p>
                    </div>
                @endif

                @if($registration->company_name)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Company</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->company_name }}</p>
                    </div>
                @endif

                @if($registration->industry)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Industry</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->industry->name }}</p>
                    </div>
                @endif

                @if($registration->businessActivity)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Business Activity</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->businessActivity->name }}</p>
                    </div>
                @endif

                @if($registration->company_size)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Company Size</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->company_size }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Company Information -->
        @if($registration->company_address || $registration->city || $registration->country)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Company Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($registration->company_address)
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-500">Address</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->company_address }}</p>
                    </div>
                @endif

                @if($registration->city)
                    <div>
                        <label class="text-sm font-medium text-gray-500">City</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->city }}</p>
                    </div>
                @endif

                @if($registration->state)
                    <div>
                        <label class="text-sm font-medium text-gray-500">State/Province</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->state }}</p>
                    </div>
                @endif

                @if($registration->postal_code)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Postal Code</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->postal_code }}</p>
                    </div>
                @endif

                @if($registration->country)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Country</label>
                        <p class="text-base text-gray-900 mt-1">{{ $registration->country }}</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Areas of Interest -->
        @if($registration->areas_of_interest && count($registration->areas_of_interest) > 0)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                Areas of Interest
            </h2>
            <div class="flex flex-wrap gap-2">
                @foreach($registration->areas_of_interest as $interest)
                    <span class="px-3 py-2 bg-indigo-50 text-indigo-700 text-sm rounded-lg">
                        {{ $interest }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Additional Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Additional Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-500">Registration Type</label>
                    <p class="text-base text-gray-900 mt-1">{{ ucfirst($registration->registration_type) }}</p>
                </div>

                @if($registration->checked_in)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Check-in Status</label>
                        <p class="text-base text-green-600 mt-1 font-semibold">
                            ✓ Checked In
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
