<h2 class="text-lg font-semibold text-gray-800 mb-4">URLs & Links</h2>

<div class="space-y-6">
        <div>
            <label for="website_url" class="block text-sm font-medium text-gray-700 mb-2">
                Website URL (For Header)
            </label>
            <input type="url" 
                   name="website_url" 
                   id="website_url" 
                   value="{{ old('website_url', $event->website_url) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('website_url') border-red-500 @enderror"
                   placeholder="https://www.example.com">
            @error('website_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="terms_url" class="block text-sm font-medium text-gray-700 mb-2">
                Terms & Conditions URL
            </label>
            <input type="url" 
                   name="terms_url" 
                   id="terms_url" 
                   value="{{ old('terms_url', $event->terms_url) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('terms_url') border-red-500 @enderror"
                   placeholder="https://www.example.com/terms">
            @error('terms_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="map_url" class="block text-sm font-medium text-gray-700 mb-2">
                Map URL
            </label>
            <input type="url" 
                   name="map_url" 
                   id="map_url" 
                   value="{{ old('map_url', $event->map_url) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('map_url') border-red-500 @enderror"
                   placeholder="https://maps.google.com/...">
            @error('map_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
