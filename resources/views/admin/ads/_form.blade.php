<div class="space-y-6">
    <!-- Title -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
        <input type="text" 
               name="title" 
               value="{{ old('title', $ad->title ?? '') }}"
               class="w-full border-gray-300 rounded-lg"
               required>
        @error('title')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Type and Placement -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
            <select name="type" class="w-full border-gray-300 rounded-lg" required>
                <option value="">Select Type</option>
                <option value="banner" {{ old('type', $ad->type ?? '') === 'banner' ? 'selected' : '' }}>Banner</option>
                <option value="sidebar" {{ old('type', $ad->type ?? '') === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                <option value="popup" {{ old('type', $ad->type ?? '') === 'popup' ? 'selected' : '' }}>Popup</option>
                <option value="footer" {{ old('type', $ad->type ?? '') === 'footer' ? 'selected' : '' }}>Footer</option>
            </select>
            @error('type')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Placement *</label>
            <select name="placement" class="w-full border-gray-300 rounded-lg" required>
                <option value="">Select Placement</option>
                <option value="home" {{ old('placement', $ad->placement ?? '') === 'home' ? 'selected' : '' }}>Home</option>
                <option value="exhibitors" {{ old('placement', $ad->placement ?? '') === 'exhibitors' ? 'selected' : '' }}>Exhibitors</option>
                <option value="sessions" {{ old('placement', $ad->placement ?? '') === 'sessions' ? 'selected' : '' }}>Sessions</option>
                <option value="speakers" {{ old('placement', $ad->placement ?? '') === 'speakers' ? 'selected' : '' }}>Speakers</option>
                <option value="agenda" {{ old('placement', $ad->placement ?? '') === 'agenda' ? 'selected' : '' }}>Agenda</option>
                <option value="all" {{ old('placement', $ad->placement ?? '') === 'all' ? 'selected' : '' }}>All Pages</option>
            </select>
            @error('placement')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Image Upload -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
        @if(isset($ad) && $ad->image)
            <div class="mb-3">
                <img src="{{ asset('storage/' . $ad->image) }}" 
                     alt="{{ $ad->title }}"
                     class="w-64 h-auto rounded border border-gray-300">
            </div>
        @endif
        <input type="file" 
               name="image" 
               accept="image/*"
               class="w-full border-gray-300 rounded-lg"
               onchange="previewImage(event)">
        <p class="text-sm text-gray-500 mt-1">Recommended: JPG, PNG, or GIF. Max 2MB</p>
        <div id="imagePreview" class="mt-3 hidden">
            <img src="" alt="Preview" class="w-64 h-auto rounded border border-gray-300">
        </div>
        @error('image')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- HTML Content -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">HTML Content (Optional)</label>
        <textarea name="content" 
                  rows="4" 
                  class="w-full border-gray-300 rounded-lg font-mono text-sm"
                  placeholder="<div>Your HTML content here</div>">{{ old('content', $ad->content ?? '') }}</textarea>
        <p class="text-sm text-gray-500 mt-1">For text-based ads or custom HTML</p>
        @error('content')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Link URL and Text -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Link URL</label>
            <input type="url" 
                   name="link_url" 
                   value="{{ old('link_url', $ad->link_url ?? '') }}"
                   class="w-full border-gray-300 rounded-lg"
                   placeholder="https://example.com">
            @error('link_url')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Link Text</label>
            <input type="text" 
                   name="link_text" 
                   value="{{ old('link_text', $ad->link_text ?? '') }}"
                   class="w-full border-gray-300 rounded-lg"
                   placeholder="Learn More">
            @error('link_text')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Display Order and Open in New Tab -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
            <input type="number" 
                   name="display_order" 
                   value="{{ old('display_order', $ad->display_order ?? 0) }}"
                   min="0"
                   class="w-full border-gray-300 rounded-lg">
            <p class="text-sm text-gray-500 mt-1">Lower numbers appear first</p>
            @error('display_order')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center pt-7">
            <input type="checkbox" 
                   name="open_new_tab" 
                   id="open_new_tab"
                   {{ old('open_new_tab', $ad->open_new_tab ?? true) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-indigo-600">
            <label for="open_new_tab" class="ml-2 text-sm text-gray-700">Open link in new tab</label>
        </div>
    </div>

    <!-- Date Range -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" 
                   name="start_date" 
                   value="{{ old('start_date', $ad->start_date ? $ad->start_date->format('Y-m-d') : '') }}"
                   class="w-full border-gray-300 rounded-lg">
            @error('start_date')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" 
                   name="end_date" 
                   value="{{ old('end_date', $ad->end_date ? $ad->end_date->format('Y-m-d') : '') }}"
                   class="w-full border-gray-300 rounded-lg">
            @error('end_date')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Max Impressions -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Max Impressions</label>
        <input type="number" 
               name="max_impressions" 
               value="{{ old('max_impressions', $ad->max_impressions ?? '') }}"
               min="1"
               class="w-full border-gray-300 rounded-lg"
               placeholder="Leave empty for unlimited">
        <p class="text-sm text-gray-500 mt-1">Ad will stop showing after reaching this number</p>
        @error('max_impressions')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Active Status -->
    <div class="flex items-center">
        <input type="checkbox" 
               name="is_active" 
               id="is_active"
               {{ old('is_active', $ad->is_active ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300 text-indigo-600">
        <label for="is_active" class="ml-2 text-sm text-gray-700">Active (show this ad)</label>
    </div>
</div>

<script>
function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    const img = preview.querySelector('img');
    const file = event.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
}
</script>
