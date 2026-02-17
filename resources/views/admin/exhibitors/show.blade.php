@extends('admin.layout')

@section('title', 'Exhibitor Details')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.exhibitors.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $exhibitor->company_name }}</h1>
                <p class="text-gray-600 mt-1">Exhibitor Details</p>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.exhibitors.edit', $exhibitor) }}" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
            <form action="{{ route('admin.exhibitors.destroy', $exhibitor) }}" 
                  method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this exhibitor?');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Status Badges -->
<div class="mb-6 flex space-x-3">
    @if($exhibitor->is_active)
        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">
            Active
        </span>
    @else
        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
            Inactive
        </span>
    @endif
    
    @if($exhibitor->is_featured)
        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
            Featured
        </span>
    @endif
    
    @if($exhibitor->visible_on_website)
        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
            Visible on Website
        </span>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Main Info -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Company Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Company Information
            </h2>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Company Name</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->company_name }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Exhibitor Type</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->exhibitorType->name ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Industry</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->industry->name ?? 'N/A' }}</p>
                </div>
                
                @if($exhibitor->website_url)
                <div>
                    <p class="text-sm text-gray-500">Website</p>
                    <a href="{{ $exhibitor->website_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">
                        Visit Website →
                    </a>
                </div>
                @endif
                
                @if($exhibitor->year_established)
                <div>
                    <p class="text-sm text-gray-500">Year Established</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->year_established }}</p>
                </div>
                @endif
                
                @if($exhibitor->company_size)
                <div>
                    <p class="text-sm text-gray-500">Company Size</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->company_size }}</p>
                </div>
                @endif
                
                @if($exhibitor->registration_number)
                <div>
                    <p class="text-sm text-gray-500">Registration Number</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->registration_number }}</p>
                </div>
                @endif
            </div>
            
            @if($exhibitor->description)
            <div class="mt-4 pt-4 border-t">
                <p class="text-sm text-gray-500 mb-2">Description</p>
                <p class="text-gray-900">{{ $exhibitor->description }}</p>
            </div>
            @endif
        </div>


        <!-- Booth Details -->
        @if($exhibitor->booth_number || $exhibitor->booth_type_id || $exhibitor->booth_size)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                </svg>
                Booth Details
            </h2>
            
            <div class="grid grid-cols-3 gap-4">
                @if($exhibitor->booth_number)
                <div>
                    <p class="text-sm text-gray-500">Booth Number</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->booth_number }}</p>
                </div>
                @endif
                
                @if($exhibitor->boothType)
                <div>
                    <p class="text-sm text-gray-500">Booth Type</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->boothType->name }}</p>
                </div>
                @endif
                
                @if($exhibitor->booth_size)
                <div>
                    <p class="text-sm text-gray-500">Booth Size</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->booth_size }} sq m</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Business Profile -->
        @if($exhibitor->businessActivities->isNotEmpty() || $exhibitor->productTypes->isNotEmpty() || $exhibitor->tags->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Business Profile
            </h2>
            
            @if($exhibitor->businessActivities->isNotEmpty())
            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-2">Business Activities</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($exhibitor->businessActivities as $activity)
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-sm rounded-full">
                            {{ $activity->name }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
            
            @if($exhibitor->productTypes->isNotEmpty())
            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-2">Product Types</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($exhibitor->productTypes as $type)
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                            {{ $type->name }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
            
            @if($exhibitor->tags->isNotEmpty())
            <div>
                <p class="text-sm text-gray-500 mb-2">Tags</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($exhibitor->tags as $tag)
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Contact Information
            </h2>
            
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-2">Primary Contact</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="text-gray-900 font-medium">{{ $exhibitor->contact_person_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <a href="mailto:{{ $exhibitor->contact_email }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $exhibitor->contact_email }}
                            </a>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <a href="tel:{{ $exhibitor->contact_phone }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $exhibitor->contact_phone }}
                            </a>
                        </div>
                    </div>
                </div>
                
                @if($exhibitor->secondary_contact_name || $exhibitor->secondary_contact_email || $exhibitor->secondary_contact_phone)
                <div class="pt-4 border-t">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Secondary Contact</p>
                    <div class="grid grid-cols-3 gap-4">
                        @if($exhibitor->secondary_contact_name)
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="text-gray-900 font-medium">{{ $exhibitor->secondary_contact_name }}</p>
                        </div>
                        @endif
                        
                        @if($exhibitor->secondary_contact_email)
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <a href="mailto:{{ $exhibitor->secondary_contact_email }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $exhibitor->secondary_contact_email }}
                            </a>
                        </div>
                        @endif
                        
                        @if($exhibitor->secondary_contact_phone)
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <a href="tel:{{ $exhibitor->secondary_contact_phone }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $exhibitor->secondary_contact_phone }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Address -->
        @if($exhibitor->address || $exhibitor->city || $exhibitor->state || $exhibitor->postal_code || $exhibitor->country)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Address
            </h2>
            
            <div class="text-gray-900">
                @if($exhibitor->address)
                    <p>{{ $exhibitor->address }}</p>
                @endif
                <p>
                    {{ $exhibitor->city }}{{ $exhibitor->city && $exhibitor->state ? ', ' : '' }}{{ $exhibitor->state }} {{ $exhibitor->postal_code }}
                </p>
                @if($exhibitor->country)
                    <p>{{ $exhibitor->country }}</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Social Media -->
        @if($exhibitor->linkedin_url || $exhibitor->twitter_url || $exhibitor->facebook_url || $exhibitor->instagram_url)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                Social Media
            </h2>
            
            <div class="flex space-x-4">
                @if($exhibitor->linkedin_url)
                <a href="{{ $exhibitor->linkedin_url }}" target="_blank" class="text-gray-600 hover:text-indigo-600 transition">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                    </svg>
                </a>
                @endif
                
                @if($exhibitor->twitter_url)
                <a href="{{ $exhibitor->twitter_url }}" target="_blank" class="text-gray-600 hover:text-indigo-600 transition">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path>
                    </svg>
                </a>
                @endif
                
                @if($exhibitor->facebook_url)
                <a href="{{ $exhibitor->facebook_url }}" target="_blank" class="text-gray-600 hover:text-indigo-600 transition">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                @endif
                
                @if($exhibitor->instagram_url)
                <a href="{{ $exhibitor->instagram_url }}" target="_blank" class="text-gray-600 hover:text-indigo-600 transition">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
        @endif


        <!-- Event Participation -->
        @if($exhibitor->participation_status || $exhibitor->registration_date || $exhibitor->payment_status || $exhibitor->special_requirements)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                Event Participation
            </h2>
            
            <div class="grid grid-cols-3 gap-4">
                @if($exhibitor->participation_status)
                <div>
                    <p class="text-sm text-gray-500">Participation Status</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->participation_status }}</p>
                </div>
                @endif
                
                @if($exhibitor->registration_date)
                <div>
                    <p class="text-sm text-gray-500">Registration Date</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->registration_date->format('M d, Y') }}</p>
                </div>
                @endif
                
                @if($exhibitor->payment_status)
                <div>
                    <p class="text-sm text-gray-500">Payment Status</p>
                    <p class="text-gray-900 font-medium">{{ $exhibitor->payment_status }}</p>
                </div>
                @endif
            </div>
            
            @if($exhibitor->special_requirements)
            <div class="mt-4 pt-4 border-t">
                <p class="text-sm text-gray-500 mb-2">Special Requirements</p>
                <p class="text-gray-900">{{ $exhibitor->special_requirements }}</p>
            </div>
            @endif
        </div>
        @endif

    </div>

    <!-- Right Column - Media & Settings -->
    <div class="space-y-6">
        
        <!-- Logo -->
        @if($exhibitor->logo_url)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Company Logo</h2>
            <img src="{{ $exhibitor->logo_url }}" alt="{{ $exhibitor->company_name }}" class="w-full rounded-lg border border-gray-200">
        </div>
        @endif

        <!-- Banner Image -->
        @if($exhibitor->banner_image_url)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Banner Image</h2>
            <img src="{{ $exhibitor->banner_image_url }}" alt="{{ $exhibitor->company_name }} Banner" class="w-full rounded-lg border border-gray-200">
        </div>
        @endif

        <!-- Media & Documents -->
        @if($exhibitor->catalog_file_url || $exhibitor->video_url)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Media & Documents
            </h2>
            
            <div class="space-y-3">
                @if($exhibitor->catalog_file_url)
                <a href="{{ $exhibitor->catalog_file_url }}" target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-8 h-8 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Product Catalog</p>
                        <p class="text-xs text-gray-500">PDF Document</p>
                    </div>
                </a>
                @endif
                
                @if($exhibitor->video_url)
                <a href="{{ $exhibitor->video_url }}" target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Company Video</p>
                        <p class="text-xs text-gray-500">External Link</p>
                    </div>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Visibility Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Visibility Settings
            </h2>
            
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">Visible on Website</span>
                    @if($exhibitor->visible_on_website)
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">Visible on App</span>
                    @if($exhibitor->visible_on_app)
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">Visible in Directory</span>
                    @if($exhibitor->visible_in_directory)
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                System Information
            </h2>
            
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Sort Order</span>
                    <span class="text-gray-900 font-medium">{{ $exhibitor->sort_order ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Created</span>
                    <span class="text-gray-900 font-medium">{{ $exhibitor->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Last Updated</span>
                    <span class="text-gray-900 font-medium">{{ $exhibitor->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
