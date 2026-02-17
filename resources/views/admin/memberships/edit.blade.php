@extends('admin.layout')

@section('title', 'Edit Membership')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.memberships.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Membership</h1>
            <p class="text-gray-600 mt-1">Update membership information</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.memberships.update', $membership) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Membership Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $membership->name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                       placeholder="e.g., IEEE Member, ACM Member"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                    Slug
                </label>
                <input type="text" 
                       name="slug" 
                       id="slug" 
                       value="{{ old('slug', $membership->slug) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('slug') border-red-500 @enderror"
                       placeholder="e.g., ieee-member">
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Verification Type -->
            <div class="md:col-span-2">
                <label for="verification_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Verification Type <span class="text-red-500">*</span>
                </label>
                <select name="verification_type" 
                        id="verification_type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('verification_type') border-red-500 @enderror"
                        required>
                    <option value="">Select verification method</option>
                    <option value="upload_file" {{ old('verification_type', $membership->verification_type) === 'upload_file' ? 'selected' : '' }}>Upload File (CSV/TXT)</option>
                    <option value="third_party_api" {{ old('verification_type', $membership->verification_type) === 'third_party_api' ? 'selected' : '' }}>Third Party API</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Choose how membership numbers will be verified</p>
                @error('verification_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- File Upload Section (shown when upload_file is selected) -->
        <div id="file-upload-section" class="mt-6 hidden">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-50">
                <label for="membership_file" class="block text-sm font-medium text-gray-700 mb-2">
                    Membership File
                </label>
                @if($membership->file_path)
                    <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center text-sm text-blue-800">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Current file: {{ basename($membership->file_path) }}
                        </div>
                    </div>
                @endif
                <input type="file" 
                       name="membership_file" 
                       id="membership_file" 
                       accept=".txt,.csv"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('membership_file') border-red-500 @enderror">
                <p class="mt-2 text-xs text-gray-500">Upload a new file to replace the current one (one membership number per line)</p>
                <a href="{{ asset('samples/membership-codes-sample.txt') }}" 
                   download
                   class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Sample File
                </a>
                <p class="mt-2 text-xs text-gray-500">Upload a new TXT or CSV file to replace the existing one (one membership number per line)</p>
                @error('membership_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- API Section (shown when third_party_api is selected) -->
        <div id="api-section" class="mt-6 hidden">
            <div class="border-2 border-indigo-200 rounded-lg p-6 bg-indigo-50">
                <div class="grid grid-cols-1 gap-6">
                    <!-- API Endpoint -->
                    <div>
                        <label for="api_endpoint" class="block text-sm font-medium text-gray-700 mb-2">
                            API Endpoint <span class="text-red-500">*</span>
                        </label>
                        <input type="url" 
                               name="api_endpoint" 
                               id="api_endpoint" 
                               value="{{ old('api_endpoint', $membership->api_endpoint) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('api_endpoint') border-red-500 @enderror"
                               placeholder="https://api.example.com/verify-membership">
                        <p class="mt-1 text-xs text-gray-500">Full URL to the membership verification API</p>
                        @error('api_endpoint')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- API Key -->
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">
                            API Key / Bearer Token
                        </label>
                        <input type="text" 
                               name="api_key" 
                               id="api_key" 
                               value="{{ old('api_key', $membership->api_key) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm @error('api_key') border-red-500 @enderror"
                               placeholder="your-api-key-here">
                        <p class="mt-1 text-xs text-gray-500">Optional: Will be sent as Authorization: Bearer {token}</p>
                        @error('api_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Color -->
            <div>
                <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                    Color
                </label>
                <div class="flex items-center space-x-3">
                    <input type="color" 
                           name="color" 
                           id="color" 
                           value="{{ old('color', $membership->color) }}"
                           class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                    <input type="text" 
                           id="color-text"
                           value="{{ old('color', $membership->color) }}"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                           readonly>
                </div>
                @error('color')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                    Sort Order
                </label>
                <input type="number" 
                       name="sort_order" 
                       id="sort_order" 
                       value="{{ old('sort_order', $membership->sort_order) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-500 @enderror"
                       placeholder="0">
                @error('sort_order')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Description -->
        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>
            <textarea name="description" 
                      id="description" 
                      rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
                      placeholder="Optional description for this membership type">{{ old('description', $membership->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Is Active -->
        <div class="mt-6">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', $membership->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Active (visible and usable)</span>
            </label>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.memberships.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Update Membership
            </button>
        </div>
    </form>
</div>

<script>
    // Sync color picker with text input
    const colorPicker = document.getElementById('color');
    const colorText = document.getElementById('color-text');
    
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
    });

    // Show/hide sections based on verification type
    const verificationTypeSelect = document.getElementById('verification_type');
    const fileUploadSection = document.getElementById('file-upload-section');
    const apiSection = document.getElementById('api-section');
    const membershipFileInput = document.getElementById('membership_file');
    const apiEndpointInput = document.getElementById('api_endpoint');

    function toggleSections() {
        const selectedType = verificationTypeSelect.value;
        
        if (selectedType === 'upload_file') {
            fileUploadSection.classList.remove('hidden');
            apiSection.classList.add('hidden');
            apiEndpointInput.required = false;
        } else if (selectedType === 'third_party_api') {
            fileUploadSection.classList.add('hidden');
            apiSection.classList.remove('hidden');
            apiEndpointInput.required = true;
        } else {
            fileUploadSection.classList.add('hidden');
            apiSection.classList.add('hidden');
            apiEndpointInput.required = false;
        }
    }

    verificationTypeSelect.addEventListener('change', toggleSections);
    
    // Initialize on page load
    toggleSections();
</script>
@endsection
