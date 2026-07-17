@extends('admin.layout')

@section('title', 'Edit Exhibitor')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.exhibitors.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Exhibitor</h1>
            <p class="text-gray-600 mt-1">Update exhibitor profile</p>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
            <div class="flex">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-semibold">Please correct the following errors:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.exhibitors.update', $exhibitor) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button type="button" onclick="showTab('basic')" id="tab-basic" class="tab-button border-b-2 border-indigo-500 py-4 px-1 text-sm font-medium text-indigo-600">
                    Basic Information
                </button>
                <button type="button" onclick="showTab('booth')" id="tab-booth" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Booth Details
                </button>
                <button type="button" onclick="showTab('business')" id="tab-business" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Business Profile
                </button>
                <button type="button" onclick="showTab('contact')" id="tab-contact" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Contact & Address
                </button>
                <button type="button" onclick="showTab('media')" id="tab-media" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Media & Documents
                </button>
                <button type="button" onclick="showTab('settings')" id="tab-settings" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Settings
                </button>
                @if(isset($customForms) && $customForms->isNotEmpty())
                    <button type="button" onclick="showTab('custom')" id="tab-custom" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-testid="exhibitor-custom-questions-tab">
                        Custom Questions
                    </button>
                @endif
            </nav>
        </div>

        <!-- Tab 1: Basic Information -->
        <div id="content-basic" class="tab-content">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Company Name -->
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Company Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $exhibitor->company_name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('company_name') border-red-500 @enderror"
                           required>
                </div>

                <!-- Exhibitor Type -->
                <div>
                    <label for="exhibitor_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Exhibitor Type <span class="text-red-500">*</span>
                    </label>
                    <select name="exhibitor_type_id" id="exhibitor_type_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('exhibitor_type_id') border-red-500 @enderror"
                            required>
                        <option value="">Select type</option>
                        @foreach($exhibitorTypes as $type)
                            <option value="{{ $type->id }}" {{ old('exhibitor_type_id', $exhibitor->exhibitor_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Industry -->
                <div>
                    <label for="industry_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Industry <span class="text-red-500">*</span>
                    </label>
                    <select name="industry_id" id="industry_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('industry_id') border-red-500 @enderror"
                            required>
                        <option value="">Select industry</option>
                        @foreach($industries as $industry)
                            <option value="{{ $industry->id }}" {{ old('industry_id', $exhibitor->industry_id) == $industry->id ? 'selected' : '' }}>
                                {{ $industry->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Contact Person Name -->
                <div>
                    <label for="contact_person_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Contact Person <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="contact_person_name" id="contact_person_name" value="{{ old('contact_person_name', $exhibitor->contact_person_name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_person_name') border-red-500 @enderror"
                           required>
                </div>

                <!-- Contact Email -->
                <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Contact Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $exhibitor->contact_email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_email') border-red-500 @enderror"
                           required>
                </div>

                <!-- Contact Phone -->
                <div>
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Contact Phone <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $exhibitor->contact_phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_phone') border-red-500 @enderror"
                           required>
                </div>

                <!-- Website URL -->
                <div>
                    <label for="website_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Website URL
                    </label>
                    <input type="url" name="website_url" id="website_url" value="{{ old('website_url', $exhibitor->website_url) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('website_url') border-red-500 @enderror"
                           placeholder="https://example.com">
                </div>

                <!-- Year Established -->
                <div>
                    <label for="year_established" class="block text-sm font-medium text-gray-700 mb-2">
                        Year Established
                    </label>
                    <input type="number" name="year_established" id="year_established" value="{{ old('year_established', $exhibitor->year_established) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('year_established') border-red-500 @enderror"
                           min="1800" max="{{ date('Y') }}">
                </div>

                <!-- Company Size -->
                <div>
                    <label for="company_size" class="block text-sm font-medium text-gray-700 mb-2">
                        Company Size
                    </label>
                    <input type="text" name="company_size" id="company_size" value="{{ old('company_size', $exhibitor->company_size) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('company_size') border-red-500 @enderror"
                           placeholder="e.g., 50-100 employees">
                </div>

                <!-- Registration Number -->
                <div>
                    <label for="registration_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Registration Number
                    </label>
                    <input type="text" name="registration_number" id="registration_number" value="{{ old('registration_number', $exhibitor->registration_number) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('registration_number') border-red-500 @enderror">
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
                          placeholder="Brief description of the company">{{ old('description', $exhibitor->description) }}</textarea>
            </div>

            <!-- Logo -->
            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                    Company Logo
                </label>
                @if($exhibitor->logo_url)
                    <div class="mb-2">
                        <img src="{{ $exhibitor->logo_url }}" alt="Current logo" class="h-20 w-20 object-cover rounded">
                        <p class="text-xs text-gray-500 mt-1">Current logo</p>
                    </div>
                @endif
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('logo') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Format: JPG or PNG | Max size: 2MB | Leave empty to keep current</p>
            </div>
        </div>

        <!-- Tab 2: Booth Details -->
        <div id="content-booth" class="tab-content hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Booth Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Booth Type -->
                <div>
                    <label for="booth_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Booth Type
                    </label>
                    <select name="booth_type_id" id="booth_type_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('booth_type_id') border-red-500 @enderror">
                        <option value="">Select booth type</option>
                        @foreach($boothTypes as $type)
                            <option value="{{ $type->id }}" {{ old('booth_type_id', $exhibitor->booth_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Booth Number -->
                <div>
                    <label for="booth_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Booth Number
                    </label>
                    <input type="text" name="booth_number" id="booth_number" value="{{ old('booth_number', $exhibitor->booth_number) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('booth_number') border-red-500 @enderror"
                           placeholder="e.g., A-101">
                </div>

                <!-- Booth Size -->
                <div>
                    <label for="booth_size" class="block text-sm font-medium text-gray-700 mb-2">
                        Booth Size (sq m)
                    </label>
                    <input type="number" name="booth_size" id="booth_size" value="{{ old('booth_size', $exhibitor->booth_size) }}" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('booth_size') border-red-500 @enderror"
                           placeholder="e.g., 9.00">
                </div>
            </div>
        </div>

        <!-- Tab 3: Business Profile -->
        <div id="content-business" class="tab-content hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Business Profile</h3>
            
            <!-- Business Activities -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Business Activities
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($businessActivities as $activity)
                        <label class="flex items-center">
                            <input type="checkbox" name="business_activities[]" value="{{ $activity->id }}"
                                   {{ in_array($activity->id, old('business_activities', $exhibitor->businessActivities->pluck('id')->toArray())) ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $activity->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Product Types -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Product Types
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($productTypes as $type)
                        <label class="flex items-center">
                            <input type="checkbox" name="product_types[]" value="{{ $type->id }}"
                                   {{ in_array($type->id, old('product_types', $exhibitor->productTypes->pluck('id')->toArray())) ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $type->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Tags -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tags
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($tags as $tag)
                        <label class="flex items-center">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   {{ in_array($tag->id, old('tags', $exhibitor->tags->pluck('id')->toArray())) ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tab 4: Contact & Address -->
        <div id="content-contact" class="tab-content hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Secondary Contact</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Secondary Contact Name -->
                <div>
                    <label for="secondary_contact_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Name
                    </label>
                    <input type="text" name="secondary_contact_name" id="secondary_contact_name" value="{{ old('secondary_contact_name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('secondary_contact_name') border-red-500 @enderror">
                </div>

                <!-- Secondary Contact Email -->
                <div>
                    <label for="secondary_contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input type="email" name="secondary_contact_email" id="secondary_contact_email" value="{{ old('secondary_contact_email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('secondary_contact_email') border-red-500 @enderror">
                </div>

                <!-- Secondary Contact Phone -->
                <div>
                    <label for="secondary_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone
                    </label>
                    <input type="tel" name="secondary_contact_phone" id="secondary_contact_phone" value="{{ old('secondary_contact_phone') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('secondary_contact_phone') border-red-500 @enderror">
                </div>
            </div>

            <hr class="my-6">

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Address</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Street Address
                    </label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('address') border-red-500 @enderror">
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                        City
                    </label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('city') border-red-500 @enderror">
                </div>

                <!-- State -->
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-2">
                        State/Province
                    </label>
                    <input type="text" name="state" id="state" value="{{ old('state') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('state') border-red-500 @enderror">
                </div>

                <!-- Postal Code -->
                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Postal Code
                    </label>
                    <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('postal_code') border-red-500 @enderror">
                </div>

                <!-- Country -->
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                        Country
                    </label>
                    <input type="text" name="country" id="country" value="{{ old('country') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('country') border-red-500 @enderror">
                </div>
            </div>

            <hr class="my-6">

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Social Media</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- LinkedIn -->
                <div>
                    <label for="linkedin_url" class="block text-sm font-medium text-gray-700 mb-2">
                        LinkedIn URL
                    </label>
                    <input type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('linkedin_url') border-red-500 @enderror"
                           placeholder="https://linkedin.com/company/...">
                </div>

                <!-- Twitter -->
                <div>
                    <label for="twitter_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Twitter URL
                    </label>
                    <input type="url" name="twitter_url" id="twitter_url" value="{{ old('twitter_url') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('twitter_url') border-red-500 @enderror"
                           placeholder="https://twitter.com/...">
                </div>

                <!-- Facebook -->
                <div>
                    <label for="facebook_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Facebook URL
                    </label>
                    <input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('facebook_url') border-red-500 @enderror"
                           placeholder="https://facebook.com/...">
                </div>

                <!-- Instagram -->
                <div>
                    <label for="instagram_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Instagram URL
                    </label>
                    <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('instagram_url') border-red-500 @enderror"
                           placeholder="https://instagram.com/...">
                </div>
            </div>
        </div>

        <!-- Tab 5: Media & Documents -->
        <div id="content-media" class="tab-content hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Media & Documents</h3>
            
            <div class="space-y-6">
                <!-- Banner Image -->
                <div>
                    <label for="banner_image" class="block text-sm font-medium text-gray-700 mb-2">
                        Banner Image
                    </label>
                    <input type="file" name="banner_image" id="banner_image" accept="image/jpeg,image/png"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('banner_image') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Format: JPG or PNG | Max size: 4MB</p>
                </div>

                <!-- Catalog File -->
                <div>
                    <label for="catalog_file" class="block text-sm font-medium text-gray-700 mb-2">
                        Product Catalog (PDF)
                    </label>
                    <input type="file" name="catalog_file" id="catalog_file" accept="application/pdf"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('catalog_file') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Format: PDF | Max size: 10MB</p>
                </div>

                <!-- Video URL -->
                <div>
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Video URL
                    </label>
                    <input type="url" name="video_url" id="video_url" value="{{ old('video_url') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('video_url') border-red-500 @enderror"
                           placeholder="https://youtube.com/watch?v=...">
                    <p class="mt-1 text-xs text-gray-500">YouTube, Vimeo, or other video platform URL</p>
                </div>
            </div>
        </div>

        <!-- Tab 6: Settings -->
        <div id="content-settings" class="tab-content hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Event Participation</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Participation Status -->
                <div>
                    <label for="participation_status" class="block text-sm font-medium text-gray-700 mb-2">
                        Participation Status
                    </label>
                    <input type="text" name="participation_status" id="participation_status" value="{{ old('participation_status') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('participation_status') border-red-500 @enderror"
                           placeholder="e.g., Confirmed, Pending">
                </div>

                <!-- Registration Date -->
                <div>
                    <label for="registration_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Registration Date
                    </label>
                    <input type="date" name="registration_date" id="registration_date" value="{{ old('registration_date') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('registration_date') border-red-500 @enderror">
                </div>

                <!-- Payment Status -->
                <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Status
                    </label>
                    <input type="text" name="payment_status" id="payment_status" value="{{ old('payment_status') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('payment_status') border-red-500 @enderror"
                           placeholder="e.g., Paid, Pending">
                </div>
            </div>

            <!-- Special Requirements -->
            <div class="mb-6">
                <label for="special_requirements" class="block text-sm font-medium text-gray-700 mb-2">
                    Special Requirements
                </label>
                <textarea name="special_requirements" id="special_requirements" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('special_requirements') border-red-500 @enderror"
                          placeholder="Any special requirements or notes">{{ old('special_requirements') }}</textarea>
            </div>

            <hr class="my-6">

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Visibility Settings</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="visible_on_website" value="1" {{ old('visible_on_website', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Visible on Website</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="visible_on_app" value="1" {{ old('visible_on_app', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Visible on App</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="visible_in_directory" value="1" {{ old('visible_in_directory', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Visible in Directory</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Featured Exhibitor</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <hr class="my-6">

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Display Order</h3>
            
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                    Sort Order
                </label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
            </div>
        </div>

        @if(isset($customForms) && $customForms->isNotEmpty())
            <div id="content-custom" class="tab-content hidden" data-testid="admin-exhibitor-custom-questions">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Custom Questions</h3>
                <div class="space-y-5">
                    @include('partials.custom-forms-fields')
                </div>
            </div>
        @endif

        <!-- Form Actions -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.exhibitors.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Update Exhibitor
            </button>
        </div>
    </form>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active styling from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-indigo-500', 'text-indigo-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active styling to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-indigo-500', 'text-indigo-600');
}
</script>
@endsection
