<h2 class="text-lg font-semibold text-gray-800 mb-4">Media & Assets</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Placeholder Type -->
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

    <!-- Placeholder URL -->
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

    <!-- Logo -->
    <div>
        <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
            Logo
        </label>
        @if($event->logo)
            <div class="mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <img src="{{ asset('storage/' . $event->logo) }}?v={{ time() }}" alt="Current Logo" class="h-20 object-contain">
                <p class="text-xs text-gray-500 mt-2">Current logo</p>
                <p class="text-xs text-gray-400">Path: {{ $event->logo }}</p>
            </div>
        @endif
        <input type="file" 
               name="logo_file" 
               id="logo_file" 
               accept="image/*"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('logo_file') border-red-500 @enderror"
               onchange="previewImage(this, 'logo-preview')">
        <p class="mt-1 text-xs text-gray-500">Upload a new logo (PNG, JPG, SVG - Max 2MB)</p>
        <div id="logo-preview" class="mt-2 hidden">
            <img src="" alt="Preview" class="h-20 object-contain border border-gray-200 rounded p-2">
            <p class="text-xs text-green-600 mt-1">New logo ready to upload</p>
        </div>
        @error('logo_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Header Image -->
    <div>
        <label for="header_image" class="block text-sm font-medium text-gray-700 mb-2">
            Header Image
        </label>
        @if($event->header_image)
            <div class="mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <img src="{{ asset('storage/' . $event->header_image) }}?v={{ time() }}" alt="Current Header" class="h-20 object-cover rounded">
                <p class="text-xs text-gray-500 mt-2">Current header image</p>
                <p class="text-xs text-gray-400">Path: {{ $event->header_image }}</p>
            </div>
        @endif
        <input type="file" 
               name="header_image_file" 
               id="header_image_file" 
               accept="image/*"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('header_image_file') border-red-500 @enderror"
               onchange="previewImage(this, 'header-preview')">
        <p class="mt-1 text-xs text-gray-500">Upload a new header image (PNG, JPG - Max 5MB)</p>
        <div id="header-preview" class="mt-2 hidden">
            <img src="" alt="Preview" class="h-20 object-cover border border-gray-200 rounded">
            <p class="text-xs text-green-600 mt-1">New header ready to upload</p>
        </div>
        @error('header_image_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Portal Background -->
    <div>
        <label for="portal_background" class="block text-sm font-medium text-gray-700 mb-2">
            Portal Background
        </label>
        @if($event->portal_background)
            <div class="mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <img src="{{ asset('storage/' . $event->portal_background) }}?v={{ time() }}" alt="Current Background" class="h-20 object-cover rounded">
                <p class="text-xs text-gray-500 mt-2">Current background</p>
                <p class="text-xs text-gray-400">Path: {{ $event->portal_background }}</p>
            </div>
        @endif
        <input type="file" 
               name="portal_background_file" 
               id="portal_background_file" 
               accept="image/*"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('portal_background_file') border-red-500 @enderror"
               onchange="previewImage(this, 'background-preview')">
        <p class="mt-1 text-xs text-gray-500">Upload a new background (PNG, JPG - Max 5MB)</p>
        <div id="background-preview" class="mt-2 hidden">
            <img src="" alt="Preview" class="h-20 object-cover border border-gray-200 rounded">
            <p class="text-xs text-green-600 mt-1">New background ready to upload</p>
        </div>
        @error('portal_background_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Main Floor Plan -->
    <div>
        <label for="main_floor_plan" class="block text-sm font-medium text-gray-700 mb-2">
            Main Floor Plan
        </label>
        @if($event->main_floor_plan)
            <div class="mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                @if(Str::endsWith($event->main_floor_plan, '.pdf'))
                    <div class="flex items-center gap-2">
                        <svg class="w-12 h-12 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium">PDF Floor Plan</p>
                            <a href="{{ asset('storage/' . $event->main_floor_plan) }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800">View PDF</a>
                        </div>
                    </div>
                @else
                    <img src="{{ asset('storage/' . $event->main_floor_plan) }}?v={{ time() }}" alt="Current Floor Plan" class="h-20 object-contain">
                @endif
                <p class="text-xs text-gray-500 mt-2">Current floor plan</p>
                <p class="text-xs text-gray-400">Path: {{ $event->main_floor_plan }}</p>
            </div>
        @endif
        <input type="file" 
               name="main_floor_plan_file" 
               id="main_floor_plan_file" 
               accept="image/*,application/pdf"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('main_floor_plan_file') border-red-500 @enderror"
               onchange="previewImage(this, 'floorplan-preview')">
        <p class="mt-1 text-xs text-gray-500">Upload a new floor plan (PNG, JPG, PDF - Max 10MB)</p>
        <div id="floorplan-preview" class="mt-2 hidden">
            <img src="" alt="Preview" class="h-20 object-contain border border-gray-200 rounded p-2">
            <p class="text-xs text-green-600 mt-1">New floor plan ready to upload</p>
        </div>
        @error('main_floor_plan_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Social Media Share Banner -->
    <div>
        <label for="social_media_share_banner" class="block text-sm font-medium text-gray-700 mb-2">
            Social Media Share Banner
        </label>
        @if($event->social_media_share_banner)
            <div class="mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <img src="{{ asset('storage/' . $event->social_media_share_banner) }}?v={{ time() }}" alt="Current Share Banner" class="h-20 object-cover rounded">
                <p class="text-xs text-gray-500 mt-2">Current share banner</p>
                <p class="text-xs text-gray-400">Path: {{ $event->social_media_share_banner }}</p>
            </div>
        @endif
        <input type="file" 
               name="social_media_share_banner_file" 
               id="social_media_share_banner_file" 
               accept="image/*"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('social_media_share_banner_file') border-red-500 @enderror"
               onchange="previewImage(this, 'banner-preview')">
        <p class="mt-1 text-xs text-gray-500">Upload share banner (1200x630px recommended - Max 2MB)</p>
        <div id="banner-preview" class="mt-2 hidden">
            <img src="" alt="Preview" class="h-20 object-cover border border-gray-200 rounded">
            <p class="text-xs text-green-600 mt-1">New banner ready to upload</p>
        </div>
        @error('social_media_share_banner_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const img = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
