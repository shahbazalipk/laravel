@extends('admin.layout')

@section('title', 'Group Details')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.groups.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $group->group_name }}</h1>
                <p class="text-gray-600 mt-1">Group Details</p>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.groups.edit', $group) }}" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
            <form action="{{ route('admin.groups.destroy', $group) }}" 
                  method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this group?');"
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
    @if($group->is_active)
        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">
            Active
        </span>
    @else
        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
            Inactive
        </span>
    @endif
    
    @if($group->is_vip)
        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
            VIP
        </span>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Main Info -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Group Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Group Information
            </h2>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Group Name</p>
                    <p class="text-gray-900 font-medium">{{ $group->group_name }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Group Type</p>
                    <p class="text-gray-900 font-medium">{{ $group->groupType->name ?? 'N/A' }}</p>
                </div>
                
                @if($group->organization_name)
                <div>
                    <p class="text-sm text-gray-500">Organization</p>
                    <p class="text-gray-900 font-medium">{{ $group->organization_name }}</p>
                </div>
                @endif
                
                @if($group->industry)
                <div>
                    <p class="text-sm text-gray-500">Industry</p>
                    <p class="text-gray-900 font-medium">{{ $group->industry->name }}</p>
                </div>
                @endif
                
                <div>
                    <p class="text-sm text-gray-500">Allowed Attendees</p>
                    <p class="text-gray-900 font-medium">{{ $group->allowed_attendees }}</p>
                </div>
                
                @if($group->invoice_number)
                <div>
                    <p class="text-sm text-gray-500">Invoice Number</p>
                    <p class="text-gray-900 font-medium">{{ $group->invoice_number }}</p>
                </div>
                @endif
                
                @if($group->website_url)
                <div>
                    <p class="text-sm text-gray-500">Website</p>
                    <a href="{{ $group->website_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">
                        Visit Website →
                    </a>
                </div>
                @endif
            </div>
            
            @if($group->description)
            <div class="mt-4 pt-4 border-t">
                <p class="text-sm text-gray-500 mb-2">Description</p>
                <p class="text-gray-900">{{ $group->description }}</p>
            </div>
            @endif
        </div>

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
                            <p class="text-gray-900 font-medium">{{ $group->primary_contact_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <a href="mailto:{{ $group->primary_contact_email }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $group->primary_contact_email }}
                            </a>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <a href="tel:{{ $group->primary_contact_phone }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $group->primary_contact_phone }}
                            </a>
                        </div>
                    </div>
                </div>
                
                @if($group->secondary_contact_name || $group->secondary_contact_email || $group->secondary_contact_phone)
                <div class="pt-4 border-t">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Secondary Contact</p>
                    <div class="grid grid-cols-3 gap-4">
                        @if($group->secondary_contact_name)
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="text-gray-900 font-medium">{{ $group->secondary_contact_name }}</p>
                        </div>
                        @endif
                        
                        @if($group->secondary_contact_email)
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <a href="mailto:{{ $group->secondary_contact_email }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $group->secondary_contact_email }}
                            </a>
                        </div>
                        @endif
                        
                        @if($group->secondary_contact_phone)
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <a href="tel:{{ $group->secondary_contact_phone }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $group->secondary_contact_phone }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Address -->
        @if($group->address || $group->city || $group->state || $group->postal_code || $group->country)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Address
            </h2>
            
            <div class="text-gray-900">
                @if($group->address)
                    <p>{{ $group->address }}</p>
                @endif
                <p>
                    {{ $group->city }}{{ $group->city && $group->state ? ', ' : '' }}{{ $group->state }} {{ $group->postal_code }}
                </p>
                @if($group->country)
                    <p>{{ $group->country }}</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Special Requirements -->
        @if($group->special_requirements)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                Special Requirements
            </h2>
            
            <p class="text-gray-900">{{ $group->special_requirements }}</p>
        </div>
        @endif

    </div>

    <!-- Right Column - Settings -->
    <div class="space-y-6">
        
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
                    <span class="text-gray-900 font-medium">{{ $group->sort_order ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Created</span>
                    <span class="text-gray-900 font-medium">{{ $group->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Last Updated</span>
                    <span class="text-gray-900 font-medium">{{ $group->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
