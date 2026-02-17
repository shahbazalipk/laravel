@extends('admin.layout')

@section('title', 'Create Group')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.groups.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create Group</h1>
            <p class="text-gray-600 mt-1">Add a new group registration</p>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm">
    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button type="button" onclick="switchTab('basic')" id="tab-basic" 
                    class="tab-button active px-6 py-4 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600">
                Basic Information
            </button>
            <button type="button" onclick="switchTab('contact')" id="tab-contact" 
                    class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Contact Details
            </button>
            <button type="button" onclick="switchTab('address')" id="tab-address" 
                    class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Address & Preferences
            </button>
        </nav>
    </div>

    <form action="{{ route('admin.groups.store') }}" method="POST" class="p-6">
        @csrf

        <!-- Tab 1: Basic Information -->
        <div id="content-basic" class="tab-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Group Name -->
                <div>
                    <label for="group_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Group Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="group_name" 
                           id="group_name" 
                           value="{{ old('group_name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('group_name') border-red-500 @enderror"
                           required>
                    @error('group_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Group Type -->
                <div>
                    <label for="group_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Group Type <span class="text-red-500">*</span>
                    </label>
                    <select name="group_type_id" 
                            id="group_type_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('group_type_id') border-red-500 @enderror"
                            required>
                        <option value="">Select a type</option>
                        @foreach($groupTypes as $type)
                            <option value="{{ $type->id }}" {{ old('group_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('group_type_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Organization Name -->
                <div>
                    <label for="organization_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Organization Name
                    </label>
                    <input type="text" 
                           name="organization_name" 
                           id="organization_name" 
                           value="{{ old('organization_name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('organization_name') border-red-500 @enderror">
                    @error('organization_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Industry -->
                <div>
                    <label for="industry_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Industry
                    </label>
                    <select name="industry_id" 
                            id="industry_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('industry_id') border-red-500 @enderror">
                        <option value="">Select an industry</option>
                        @foreach($industries as $industry)
                            <option value="{{ $industry->id }}" {{ old('industry_id') == $industry->id ? 'selected' : '' }}>
                                {{ $industry->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('industry_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Website URL -->
                <div>
                    <label for="website_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Website URL
                    </label>
                    <input type="url" 
                           name="website_url" 
                           id="website_url" 
                           value="{{ old('website_url') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('website_url') border-red-500 @enderror"
                           placeholder="https://example.com">
                    @error('website_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Allowed Attendees -->
                <div>
                    <label for="allowed_attendees" class="block text-sm font-medium text-gray-700 mb-2">
                        Allowed Attendees <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="allowed_attendees" 
                           id="allowed_attendees" 
                           value="{{ old('allowed_attendees', 1) }}"
                           min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('allowed_attendees') border-red-500 @enderror"
                           required>
                    @error('allowed_attendees')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Invoice Number -->
                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Invoice Number
                    </label>
                    <input type="text" 
                           name="invoice_number" 
                           id="invoice_number" 
                           value="{{ old('invoice_number') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('invoice_number') border-red-500 @enderror">
                    @error('invoice_number')
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
                           value="{{ old('sort_order', 0) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-500 @enderror">
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
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tags -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tags
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($tags as $tag)
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="tags[]" 
                                   value="{{ $tag->id }}"
                                   {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Status Checkboxes -->
            <div class="mt-6 space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_vip" 
                           value="1"
                           {{ old('is_vip') ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">VIP Group</span>
                </label>
            </div>
        </div>

        <!-- Tab 2: Contact Details -->
        <div id="content-contact" class="tab-content hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Primary Contact</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Primary Contact Name -->
                <div>
                    <label for="primary_contact_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="primary_contact_name" 
                           id="primary_contact_name" 
                           value="{{ old('primary_contact_name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('primary_contact_name') border-red-500 @enderror"
                           required>
                    @error('primary_contact_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Primary Contact Email -->
                <div>
                    <label for="primary_contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           name="primary_contact_email" 
                           id="primary_contact_email" 
                           value="{{ old('primary_contact_email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('primary_contact_email') border-red-500 @enderror"
                           required>
                    @error('primary_contact_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Primary Contact Phone -->
                <div>
                    <label for="primary_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           name="primary_contact_phone" 
                           id="primary_contact_phone" 
                           value="{{ old('primary_contact_phone') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('primary_contact_phone') border-red-500 @enderror"
                           required>
                    @error('primary_contact_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-800 mb-4 mt-8">Secondary Contact (Optional)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Secondary Contact Name -->
                <div>
                    <label for="secondary_contact_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Name
                    </label>
                    <input type="text" 
                           name="secondary_contact_name" 
                           id="secondary_contact_name" 
                           value="{{ old('secondary_contact_name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('secondary_contact_name') border-red-500 @enderror">
                    @error('secondary_contact_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Secondary Contact Email -->
                <div>
                    <label for="secondary_contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input type="email" 
                           name="secondary_contact_email" 
                           id="secondary_contact_email" 
                           value="{{ old('secondary_contact_email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('secondary_contact_email') border-red-500 @enderror">
                    @error('secondary_contact_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Secondary Contact Phone -->
                <div>
                    <label for="secondary_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone
                    </label>
                    <input type="tel" 
                           name="secondary_contact_phone" 
                           id="secondary_contact_phone" 
                           value="{{ old('secondary_contact_phone') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('secondary_contact_phone') border-red-500 @enderror">
                    @error('secondary_contact_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tab 3: Address & Preferences -->
        <div id="content-address" class="tab-content hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Address</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Street Address
                    </label>
                    <input type="text" 
                           name="address" 
                           id="address" 
                           value="{{ old('address') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('address') border-red-500 @enderror">
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                        City
                    </label>
                    <input type="text" 
                           name="city" 
                           id="city" 
                           value="{{ old('city') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('city') border-red-500 @enderror">
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- State -->
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-2">
                        State/Province
                    </label>
                    <input type="text" 
                           name="state" 
                           id="state" 
                           value="{{ old('state') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('state') border-red-500 @enderror">
                    @error('state')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Postal Code -->
                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Postal Code
                    </label>
                    <input type="text" 
                           name="postal_code" 
                           id="postal_code" 
                           value="{{ old('postal_code') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('postal_code') border-red-500 @enderror">
                    @error('postal_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Country -->
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                        Country
                    </label>
                    <input type="text" 
                           name="country" 
                           id="country" 
                           value="{{ old('country') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('country') border-red-500 @enderror">
                    @error('country')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-800 mb-4 mt-8">Special Requirements</h3>
            <div>
                <textarea name="special_requirements" 
                          id="special_requirements" 
                          rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('special_requirements') border-red-500 @enderror"
                          placeholder="Dietary restrictions, accessibility needs, etc.">{{ old('special_requirements') }}</textarea>
                @error('special_requirements')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex justify-end space-x-3 border-t pt-6">
            <a href="{{ route('admin.groups.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Create Group
            </button>
        </div>
    </form>
</div>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('active', 'border-indigo-600', 'text-indigo-600');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
}
</script>
@endsection
