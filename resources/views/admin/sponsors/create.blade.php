@extends('admin.layout')

@section('title', 'Add Sponsor')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.sponsors.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Add Sponsor</h1>
            <p class="text-gray-600 mt-1">Create a new event sponsor</p>
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

    <form action="{{ route('admin.sponsors.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Visibility Settings -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Visibility Settings</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="visible_on_ebadge" value="1" {{ old('visible_on_ebadge') ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Visible on e-badge</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="visible_on_exhibitor_portal" value="1" {{ old('visible_on_exhibitor_portal') ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Visible on Exhibitor Portal</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="visible_on_group_portal" value="1" {{ old('visible_on_group_portal') ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Visible on Group Portal</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="visible_online" value="1" {{ old('visible_online', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Visible Online</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="visible_onsite" value="1" {{ old('visible_onsite', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Visible Onsite</span>
                </label>
            </div>
        </div>

        <hr class="my-6">

        <!-- Basic Information -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sponsorship Label -->
            <div>
                <label for="sponsorship_label" class="block text-sm font-medium text-gray-700 mb-2">
                    Sponsorship Label <span class="text-red-500">*</span>
                </label>
                <input type="text" name="sponsorship_label" id="sponsorship_label" value="{{ old('sponsorship_label') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sponsorship_label') border-red-500 @enderror"
                       placeholder="e.g., Platinum Sponsor"
                       required>
                @error('sponsorship_label')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                    Type <span class="text-red-500">*</span>
                </label>
                <input type="text" name="type" id="type" value="{{ old('type') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('type') border-red-500 @enderror"
                       placeholder="e.g., Platinum, Gold, Silver"
                       required>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                    Sort Order
                </label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-500 @enderror">
                @error('sort_order')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>
            <textarea name="description" id="description" rows="4"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
                      placeholder="Brief description of the sponsor">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <hr class="my-6">

        <!-- Logo Uploads -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Logo Images</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Logo Thumbnail -->
            <div>
                <label for="logo_thumbnail" class="block text-sm font-medium text-gray-700 mb-2">
                    Logo Thumbnail
                </label>
                <input type="file" name="logo_thumbnail" id="logo_thumbnail" accept="image/jpeg,image/png"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('logo_thumbnail') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Format: JPG or PNG | Min dimension: 400x400 (width x height)</p>
                @error('logo_thumbnail')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Logo Defined Size -->
            <div>
                <label for="logo_defined_size" class="block text-sm font-medium text-gray-700 mb-2">
                    Logo Defined Size
                </label>
                <input type="file" name="logo_defined_size" id="logo_defined_size" accept="image/jpeg,image/png"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('logo_defined_size') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Format: JPG or PNG | Min dimension: 400x400 (width x height)</p>
                @error('logo_defined_size')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <hr class="my-6">

        <!-- Contact Information -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Contact Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Website URL -->
            <div>
                <label for="website_url" class="block text-sm font-medium text-gray-700 mb-2">
                    Website URL
                </label>
                <input type="url" name="website_url" id="website_url" value="{{ old('website_url') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('website_url') border-red-500 @enderror"
                       placeholder="https://example.com">
                @error('website_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contact Email -->
            <div>
                <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                    Contact Email
                </label>
                <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_email') border-red-500 @enderror"
                       placeholder="contact@example.com">
                @error('contact_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contact Phone -->
            <div>
                <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Contact Phone
                </label>
                <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_phone') border-red-500 @enderror"
                       placeholder="+1 234 567 8900">
                @error('contact_phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.sponsors.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Create Sponsor
            </button>
        </div>
    </form>
</div>
@endsection
