<h2 class="text-lg font-semibold text-gray-800 mb-4">Media & Assets</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="placeholder_type" class="block text-sm font-medium text-gray-700 mb-2">
                Placeholder Type
            </label>
            <select name="placeholder_type" 
                    id="placeholder_type"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('placeholder_type') border-red-500 @enderror">
                <option value="">Select Type</option>
                <option value="YouTube" {{ old('placeholder_type', $event->placeholder_type) == 'YouTube' ? 'selected' : '' }}>YouTube</option>
                <option value="Vimeo" {{ old('placeholder_type', $event->placeholder_type) == 'Vimeo' ? 'selected' : '' }}>Vimeo</option>
                <option value="Image" {{ old('placeholder_type', $event->placeholder_type) == 'Image' ? 'selected' : '' }}>Image</option>
            </select>
            @error('placeholder_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="placeholder_url" class="block text-sm font-medium text-gray-700 mb-2">
                Placeholder URL
            </label>
            <input type="text" 
                   name="placeholder_url" 
                   id="placeholder_url" 
                   value="{{ old('placeholder_url', $event->placeholder_url) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('placeholder_url') border-red-500 @enderror"
                   placeholder="Video ID or image URL">
            @error('placeholder_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                Logo
            </label>
            <input type="text" 
                   name="logo" 
                   id="logo" 
                   value="{{ old('logo', $event->logo) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('logo') border-red-500 @enderror"
                   placeholder="Logo file path or URL">
            @error('logo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="header_image" class="block text-sm font-medium text-gray-700 mb-2">
                Header Image
            </label>
            <input type="text" 
                   name="header_image" 
                   id="header_image" 
                   value="{{ old('header_image', $event->header_image) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('header_image') border-red-500 @enderror"
                   placeholder="Header image path or URL">
            @error('header_image')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="portal_background" class="block text-sm font-medium text-gray-700 mb-2">
                Portal Background
            </label>
            <input type="text" 
                   name="portal_background" 
                   id="portal_background" 
                   value="{{ old('portal_background', $event->portal_background) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('portal_background') border-red-500 @enderror"
                   placeholder="Background image path or URL">
            @error('portal_background')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="main_floor_plan" class="block text-sm font-medium text-gray-700 mb-2">
                Main Floor Plan
            </label>
            <input type="text" 
                   name="main_floor_plan" 
                   id="main_floor_plan" 
                   value="{{ old('main_floor_plan', $event->main_floor_plan) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('main_floor_plan') border-red-500 @enderror"
                   placeholder="Floor plan file path or URL">
            @error('main_floor_plan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
