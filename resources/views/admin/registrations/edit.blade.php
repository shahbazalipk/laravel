@extends('admin.layout')

@section('title', 'Edit Registration')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.registrations.show', $registration) }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Registration</h1>
            <p class="text-gray-600 mt-1">{{ $registration->registration_number }}</p>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.registrations.update', $registration) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Category & Type -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="registration_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <select name="registration_category_id" 
                        id="registration_category_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('registration_category_id') border-red-500 @enderror"
                        required>
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" 
                                data-needs-password="{{ $category->needs_password ? 'true' : 'false' }}"
                                data-need-membership="{{ $category->need_membership_id ? 'true' : 'false' }}"
                                data-need-professional="{{ $category->need_professional_student_id ? 'true' : 'false' }}"
                                {{ old('registration_category_id', $registration->registration_category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }} ({{ $category->price }} {{ $category->currency }})
                        </option>
                    @endforeach
                </select>
                @error('registration_category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="registration_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Registration Type <span class="text-red-500">*</span>
                </label>
                <select name="registration_type" 
                        id="registration_type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('registration_type') border-red-500 @enderror"
                        required>
                    <option value="individual" {{ old('registration_type') == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="exhibitor" {{ old('registration_type') == 'exhibitor' ? 'selected' : '' }}>Exhibitor</option>
                    <option value="group" {{ old('registration_type') == 'group' ? 'selected' : '' }}>Group</option>
                </select>
                @error('registration_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Category-Specific Fields -->
        <div id="category_specific_fields" class="mb-6" style="display: none;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Category Requirements</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Category Password -->
                <div id="category_password_field" style="display: none;">
                    <label for="category_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Category Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           name="category_password" 
                           id="category_password" 
                           value="{{ old('category_password') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('category_password') border-red-500 @enderror">
                    @error('category_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">This category requires a password to register</p>
                </div>

                <!-- Membership ID -->
                <div id="membership_id_field" style="display: none;">
                    <label for="membership_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Membership ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="membership_id" 
                           id="membership_id" 
                           value="{{ old('membership_id') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('membership_id') border-red-500 @enderror">
                    @error('membership_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Valid membership ID is required for this category</p>
                </div>

                <!-- Professional/Student ID -->
                <div id="professional_id_field" style="display: none;">
                    <label for="professional_student_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Professional/Student ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="professional_student_id" 
                           id="professional_student_id" 
                           value="{{ old('professional_student_id') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('professional_student_id') border-red-500 @enderror">
                    @error('professional_student_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Professional or student ID is required for this category</p>
                </div>

                <!-- Professional/Student ID Document Upload -->
                <div id="professional_id_document_field" style="display: none;">
                    <label for="professional_id_document" class="block text-sm font-medium text-gray-700 mb-2">
                        ID Document Upload
                    </label>
                    <input type="file" 
                           name="professional_id_document" 
                           id="professional_id_document" 
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('professional_id_document') border-red-500 @enderror">
                    @error('professional_id_document')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Upload a copy of your professional/student ID (PDF, JPG, PNG)</p>
                </div>
            </div>
        </div>

        <!-- Exhibitor/Group Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div id="exhibitor_field" style="display: none;">
                <label for="exhibitor_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Exhibitor
                </label>
                <select name="exhibitor_id" 
                        id="exhibitor_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Select exhibitor</option>
                    @foreach($exhibitors as $exhibitor)
                        <option value="{{ $exhibitor->id }}" {{ old('exhibitor_id') == $exhibitor->id ? 'selected' : '' }}>
                            {{ $exhibitor->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="group_field" style="display: none;">
                <label for="group_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Group
                </label>
                <select name="group_id" 
                        id="group_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Select group</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->group_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Personal Information -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4 mt-8">Personal Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label for="salutation" class="block text-sm font-medium text-gray-700 mb-2">
                    Salutation
                </label>
                <select name="salutation" 
                        id="salutation"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Select</option>
                    <option value="Mr" {{ old('salutation') == 'Mr' ? 'selected' : '' }}>Mr</option>
                    <option value="Mrs" {{ old('salutation') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                    <option value="Ms" {{ old('salutation') == 'Ms' ? 'selected' : '' }}>Ms</option>
                    <option value="Dr" {{ old('salutation') == 'Dr' ? 'selected' : '' }}>Dr</option>
                    <option value="Prof" {{ old('salutation') == 'Prof' ? 'selected' : '' }}>Prof</option>
                </select>
            </div>

            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="first_name" 
                       id="first_name" 
                       value="{{ old('first_name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('first_name') border-red-500 @enderror"
                       required>
                @error('first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="last_name" 
                       id="last_name" 
                       value="{{ old('last_name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('last_name') border-red-500 @enderror"
                       required>
                @error('last_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-500 @enderror"
                       required>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Phone <span class="text-red-500">*</span>
                </label>
                <input type="tel" 
                       name="phone" 
                       id="phone" 
                       value="{{ old('phone') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                       required>
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="job_title" class="block text-sm font-medium text-gray-700 mb-2">
                    Job Title
                </label>
                <input type="text" 
                       name="job_title" 
                       id="job_title" 
                       value="{{ old('job_title') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                    Department
                </label>
                <input type="text" 
                       name="department" 
                       id="department" 
                       value="{{ old('department') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
        </div>

        <!-- Company Information -->
        <h3 class="text-lg font-semibold text-gray-800 mb-4 mt-8">Company Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Company Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="company_name" 
                       id="company_name" 
                       value="{{ old('company_name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('company_name') border-red-500 @enderror"
                       required>
                @error('company_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="industry_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Industry
                </label>
                <select name="industry_id" 
                        id="industry_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Select industry</option>
                    @foreach($industries as $industry)
                        <option value="{{ $industry->id }}" {{ old('industry_id') == $industry->id ? 'selected' : '' }}>
                            {{ $industry->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Notes -->
        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                Internal Notes
            </label>
            <textarea name="notes" 
                      id="notes" 
                      rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                      placeholder="Add any internal notes about this registration">{{ old('notes') }}</textarea>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.registrations.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Create Registration
            </button>
        </div>
    </form>
</div>

<script>
    // Show/hide exhibitor and group fields based on registration type
    document.getElementById('registration_type').addEventListener('change', function() {
        const exhibitorField = document.getElementById('exhibitor_field');
        const groupField = document.getElementById('group_field');
        
        exhibitorField.style.display = this.value === 'exhibitor' ? 'block' : 'none';
        groupField.style.display = this.value === 'group' ? 'block' : 'none';
    });

    // Show/hide category-specific fields based on selected category
    document.getElementById('registration_category_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const needsPassword = selectedOption.getAttribute('data-needs-password') === 'true';
        const needMembership = selectedOption.getAttribute('data-need-membership') === 'true';
        const needProfessional = selectedOption.getAttribute('data-need-professional') === 'true';
        
        const categorySpecificFields = document.getElementById('category_specific_fields');
        const passwordField = document.getElementById('category_password_field');
        const membershipField = document.getElementById('membership_id_field');
        const professionalField = document.getElementById('professional_id_field');
        const professionalDocField = document.getElementById('professional_id_document_field');
        
        // Show category specific section if any field is required
        if (needsPassword || needMembership || needProfessional) {
            categorySpecificFields.style.display = 'block';
        } else {
            categorySpecificFields.style.display = 'none';
        }
        
        // Show/hide individual fields
        passwordField.style.display = needsPassword ? 'block' : 'none';
        membershipField.style.display = needMembership ? 'block' : 'none';
        professionalField.style.display = needProfessional ? 'block' : 'none';
        professionalDocField.style.display = needProfessional ? 'block' : 'none';
        
        // Set required attribute
        document.getElementById('category_password').required = needsPassword;
        document.getElementById('membership_id').required = needMembership;
        document.getElementById('professional_student_id').required = needProfessional;
    });

    // Trigger on page load if old value exists
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('registration_type');
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }
        
        const categorySelect = document.getElementById('registration_category_id');
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
