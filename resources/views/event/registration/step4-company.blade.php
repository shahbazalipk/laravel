<h2 class="text-2xl font-bold text-gray-900 mb-6">Company Information</h2>

<div class="space-y-6">
    <p class="text-gray-600">Please provide your company or organization details.</p>

    <!-- Company Name -->
    <div>
        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
            Company Name <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               name="company_name" 
               id="company_name" 
               value="{{ old('company_name', $formData['company_name'] ?? '') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
               placeholder="Your Company Name"
               required>
        @error('company_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Industry and Business Activity -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="industry_id" class="block text-sm font-medium text-gray-700 mb-2">
                Industry <span class="text-red-500">*</span>
            </label>
            <select name="industry_id" 
                    id="industry_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    required>
                <option value="">Select Industry</option>
                @foreach($industries as $industry)
                    <option value="{{ $industry->id }}" 
                            {{ old('industry_id', $formData['industry_id'] ?? '') == $industry->id ? 'selected' : '' }}>
                        {{ $industry->name }}
                    </option>
                @endforeach
            </select>
            @error('industry_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="business_activity_id" class="block text-sm font-medium text-gray-700 mb-2">
                Business Activity
            </label>
            <select name="business_activity_id" 
                    id="business_activity_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Select Business Activity</option>
                @foreach($businessActivities as $activity)
                    <option value="{{ $activity->id }}" 
                            {{ old('business_activity_id', $formData['business_activity_id'] ?? '') == $activity->id ? 'selected' : '' }}>
                        {{ $activity->name }}
                    </option>
                @endforeach
            </select>
            @error('business_activity_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Company Size and Website -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="company_size" class="block text-sm font-medium text-gray-700 mb-2">
                Company Size
            </label>
            <select name="company_size" 
                    id="company_size"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Select Size</option>
                <option value="1-10" {{ old('company_size', $formData['company_size'] ?? '') == '1-10' ? 'selected' : '' }}>1-10 employees</option>
                <option value="11-50" {{ old('company_size', $formData['company_size'] ?? '') == '11-50' ? 'selected' : '' }}>11-50 employees</option>
                <option value="51-200" {{ old('company_size', $formData['company_size'] ?? '') == '51-200' ? 'selected' : '' }}>51-200 employees</option>
                <option value="201-500" {{ old('company_size', $formData['company_size'] ?? '') == '201-500' ? 'selected' : '' }}>201-500 employees</option>
                <option value="501-1000" {{ old('company_size', $formData['company_size'] ?? '') == '501-1000' ? 'selected' : '' }}>501-1000 employees</option>
                <option value="1000+" {{ old('company_size', $formData['company_size'] ?? '') == '1000+' ? 'selected' : '' }}>1000+ employees</option>
            </select>
            @error('company_size')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="company_website" class="block text-sm font-medium text-gray-700 mb-2">
                Company Website
            </label>
            <input type="url" 
                   name="company_website" 
                   id="company_website" 
                   value="{{ old('company_website', $formData['company_website'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="https://www.example.com">
            @error('company_website')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Company Address -->
    <div>
        <label for="company_address" class="block text-sm font-medium text-gray-700 mb-2">
            Company Address
        </label>
        <textarea name="company_address" 
                  id="company_address" 
                  rows="2"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="Street address">{{ old('company_address', $formData['company_address'] ?? '') }}</textarea>
        @error('company_address')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- City, State, Postal Code -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                City
            </label>
            <input type="text" 
                   name="city" 
                   id="city" 
                   value="{{ old('city', $formData['city'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="Dubai">
            @error('city')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="state" class="block text-sm font-medium text-gray-700 mb-2">
                State/Province
            </label>
            <input type="text" 
                   name="state" 
                   id="state" 
                   value="{{ old('state', $formData['state'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="Dubai">
            @error('state')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                Postal Code
            </label>
            <input type="text" 
                   name="postal_code" 
                   id="postal_code" 
                   value="{{ old('postal_code', $formData['postal_code'] ?? '') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="12345">
            @error('postal_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Country -->
    <div>
        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
            Country
        </label>
        <select name="country" 
                id="country"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <option value="">Select Country</option>
            <option value="United Arab Emirates" {{ old('country', $formData['country'] ?? '') == 'United Arab Emirates' ? 'selected' : '' }}>United Arab Emirates</option>
            <option value="Saudi Arabia" {{ old('country', $formData['country'] ?? '') == 'Saudi Arabia' ? 'selected' : '' }}>Saudi Arabia</option>
            <option value="Qatar" {{ old('country', $formData['country'] ?? '') == 'Qatar' ? 'selected' : '' }}>Qatar</option>
            <option value="Kuwait" {{ old('country', $formData['country'] ?? '') == 'Kuwait' ? 'selected' : '' }}>Kuwait</option>
            <option value="Bahrain" {{ old('country', $formData['country'] ?? '') == 'Bahrain' ? 'selected' : '' }}>Bahrain</option>
            <option value="Oman" {{ old('country', $formData['country'] ?? '') == 'Oman' ? 'selected' : '' }}>Oman</option>
            <option value="Egypt" {{ old('country', $formData['country'] ?? '') == 'Egypt' ? 'selected' : '' }}>Egypt</option>
            <option value="Jordan" {{ old('country', $formData['country'] ?? '') == 'Jordan' ? 'selected' : '' }}>Jordan</option>
            <option value="Lebanon" {{ old('country', $formData['country'] ?? '') == 'Lebanon' ? 'selected' : '' }}>Lebanon</option>
            <option value="Other" {{ old('country', $formData['country'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
        </select>
        @error('country')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Tax Registration Number (conditional) -->
    <div id="tax_registration_number_field" class="hidden">
        <label for="tax_registration_number" class="block text-sm font-medium text-gray-700 mb-2">
            Tax Registration Number (TRN) <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               name="tax_registration_number" 
               id="tax_registration_number" 
               value="{{ old('tax_registration_number', $formData['tax_registration_number'] ?? '') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
               placeholder="Enter TRN">
        <p class="mt-1 text-xs text-gray-500">Required for this registration category.</p>
        @error('tax_registration_number')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
