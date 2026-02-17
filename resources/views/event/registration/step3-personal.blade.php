<h2 class="text-2xl font-bold text-gray-900 mb-6">Personal Information</h2>

<div class="space-y-6">
    <p class="text-gray-600">Please provide your personal details.</p>

    <!-- Salutation and Name -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="salutation" class="block text-sm font-medium text-gray-700 mb-2">
                Salutation
            </label>
            <select name="salutation" 
                    id="salutation"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Select</option>
                <option value="Mr." {{ old('salutation', $formData['salutation'] ?? '') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                <option value="Mrs." {{ old('salutation', $formData['salutation'] ?? '') == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                <option value="Ms." {{ old('salutation', $formData['salutation'] ?? '') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                <option value="Dr." {{ old('salutation', $formData['salutation'] ?? '') == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                <option value="Prof." {{ old('salutation', $formData['salutation'] ?? '') == 'Prof.' ? 'selected' : '' }}>Prof.</option>
            </select>
            @error('salutation')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                First Name <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   name="first_name" 
                   id="first_name" 
                   value="{{ old('first_name', $formData['first_name'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="John"
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
                   value="{{ old('last_name', $formData['last_name'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="Doe"
                   required>
            @error('last_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Contact Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address <span class="text-red-500">*</span>
            </label>
            <input type="email" 
                   name="email" 
                   id="email" 
                   value="{{ old('email', $formData['email'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="john.doe@example.com"
                   required>
            <p class="mt-1 text-xs text-gray-500">We'll send your registration confirmation to this email.</p>
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                Phone Number <span class="text-red-500">*</span>
            </label>
            <input type="tel" 
                   name="phone" 
                   id="phone" 
                   value="{{ old('phone', $formData['phone'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="+971 50 123 4567"
                   required>
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Mobile Phone (Optional) -->
    <div>
        <label for="mobile_phone" class="block text-sm font-medium text-gray-700 mb-2">
            Mobile Phone (Optional)
        </label>
        <input type="tel" 
               name="mobile_phone" 
               id="mobile_phone" 
               value="{{ old('mobile_phone', $formData['mobile_phone'] ?? '') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
               placeholder="+971 55 123 4567">
        @error('mobile_phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Professional Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="job_title" class="block text-sm font-medium text-gray-700 mb-2">
                Job Title
            </label>
            <input type="text" 
                   name="job_title" 
                   id="job_title" 
                   value="{{ old('job_title', $formData['job_title'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="e.g., Marketing Manager">
            @error('job_title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                Department
            </label>
            <input type="text" 
                   name="department" 
                   id="department" 
                   value="{{ old('department', $formData['department'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="e.g., Marketing">
            @error('department')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
