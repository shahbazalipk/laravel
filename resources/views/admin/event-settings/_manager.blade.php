<h2 class="text-lg font-semibold text-gray-800 mb-4">Manager Information</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="manager_name" class="block text-sm font-medium text-gray-700 mb-2">
                Manager Name
            </label>
            <input type="text" 
                   name="manager_name" 
                   id="manager_name" 
                   value="{{ old('manager_name', $event->manager_name) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('manager_name') border-red-500 @enderror"
                   placeholder="Event manager name">
            @error('manager_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="manager_email" class="block text-sm font-medium text-gray-700 mb-2">
                Manager Email
            </label>
            <input type="email" 
                   name="manager_email" 
                   id="manager_email" 
                   value="{{ old('manager_email', $event->manager_email) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('manager_email') border-red-500 @enderror"
                   placeholder="manager@example.com">
            @error('manager_email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="manager_phone" class="block text-sm font-medium text-gray-700 mb-2">
                Manager Phone
            </label>
            <input type="text" 
                   name="manager_phone" 
                   id="manager_phone" 
                   value="{{ old('manager_phone', $event->manager_phone) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('manager_phone') border-red-500 @enderror"
                   placeholder="+971 50 123 4567">
            @error('manager_phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

<div class="mt-8"></div>

<h2 class="text-lg font-semibold text-gray-800 mb-4">Address Information</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label for="address_line1" class="block text-sm font-medium text-gray-700 mb-2">
                Address Line 1
            </label>
            <input type="text" 
                   name="address_line1" 
                   id="address_line1" 
                   value="{{ old('address_line1', $event->address_line1) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('address_line1') border-red-500 @enderror"
                   placeholder="Street address">
            @error('address_line1')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="address_line2" class="block text-sm font-medium text-gray-700 mb-2">
                Address Line 2
            </label>
            <input type="text" 
                   name="address_line2" 
                   id="address_line2" 
                   value="{{ old('address_line2', $event->address_line2) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('address_line2') border-red-500 @enderror"
                   placeholder="Building, floor, hall">
            @error('address_line2')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                Country
            </label>
            <input type="text" 
                   name="country" 
                   id="country" 
                   value="{{ old('country', $event->country) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('country') border-red-500 @enderror"
                   placeholder="Country">
            @error('country')
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
                   value="{{ old('state', $event->state) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('state') border-red-500 @enderror"
                   placeholder="State or province">
            @error('state')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                City
            </label>
            <input type="text" 
                   name="city" 
                   id="city" 
                   value="{{ old('city', $event->city) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('city') border-red-500 @enderror"
                   placeholder="City">
            @error('city')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
