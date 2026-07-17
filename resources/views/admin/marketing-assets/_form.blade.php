<!-- Basic Information -->
<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
    
    <div class="mb-4">
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
        <input type="text" 
               name="title" 
               id="title" 
               value="{{ old('title', $asset->title ?? '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
               required>
        @error('title')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-4">
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
        <textarea name="description" 
                  id="description" 
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $asset->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Asset Type *</label>
            <select name="type" 
                    id="type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                <option value="poster" {{ old('type', $asset->type ?? '') == 'poster' ? 'selected' : '' }}>Poster</option>
                <option value="social_post" {{ old('type', $asset->type ?? '') == 'social_post' ? 'selected' : '' }}>Social Media Post</option>
                <option value="hashtag" {{ old('type', $asset->type ?? '') == 'hashtag' ? 'selected' : '' }}>Hashtag Collection</option>
                <option value="caption" {{ old('type', $asset->type ?? '') == 'caption' ? 'selected' : '' }}>Caption/Text</option>
                <option value="story" {{ old('type', $asset->type ?? '') == 'story' ? 'selected' : '' }}>Story Template</option>
                <option value="banner" {{ old('type', $asset->type ?? '') == 'banner' ? 'selected' : '' }}>Banner</option>
                <option value="email_signature" {{ old('type', $asset->type ?? '') == 'email_signature' ? 'selected' : '' }}>Email Signature</option>
            </select>
        </div>

        <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category" 
                    id="category"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Category</option>
                <option value="pre-event" {{ old('category', $asset->category ?? '') == 'pre-event' ? 'selected' : '' }}>Pre-Event</option>
                <option value="during-event" {{ old('category', $asset->category ?? '') == 'during-event' ? 'selected' : '' }}>During Event</option>
                <option value="post-event" {{ old('category', $asset->category ?? '') == 'post-event' ? 'selected' : '' }}>Post-Event</option>
            </select>
        </div>
    </div>
</div>

<!-- Image Upload -->
<div class="mb-6 border-t pt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Image/Visual</h3>
    
    @if(isset($asset) && $asset->image_path)
        <div class="mb-4">
            <img src="{{ storage_public_url($asset->image_path) }}" 
                 alt="Current image" 
                 class="w-64 h-auto rounded-lg border">
            <p class="text-xs text-gray-500 mt-1">Current image</p>
        </div>
    @endif
    
    <div class="mb-4">
        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
            {{ isset($asset) && $asset->image_path ? 'Replace Image' : 'Upload Image' }}
        </label>
        <input type="file" 
               name="image" 
               id="image" 
               accept="image/*"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <p class="mt-1 text-xs text-gray-500">Supported: JPEG, PNG, JPG, GIF (Max: 10MB)</p>
        @error('image')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<!-- Content -->
<div class="mb-6 border-t pt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Content</h3>
    
    <div class="mb-4">
        <label for="caption_text" class="block text-sm font-medium text-gray-700 mb-2">Caption/Text Content</label>
        <textarea name="caption_text" 
                  id="caption_text" 
                  rows="5"
                  placeholder="Enter pre-written captions, promotional text, or content that attendees can copy and use..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('caption_text', $asset->caption_text ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">This text will be easy to copy for attendees</p>
    </div>

    <div class="mb-4">
        <label for="hashtags" class="block text-sm font-medium text-gray-700 mb-2">Hashtags</label>
        <input type="text" 
               name="hashtags" 
               id="hashtags" 
               value="{{ old('hashtags', isset($asset) && $asset->hashtags ? implode(', ', $asset->hashtags) : '') }}"
               placeholder="EventName, Conference2024, Networking"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <p class="mt-1 text-xs text-gray-500">Separate hashtags with commas (without # symbol)</p>
    </div>
</div>

<!-- Social Platforms -->
<div class="mb-6 border-t pt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Social Media Platforms</h3>
    <p class="text-sm text-gray-600 mb-3">Select which platforms this asset is optimized for:</p>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @php
            $platforms = ['Facebook', 'Twitter', 'LinkedIn', 'Instagram', 'TikTok', 'YouTube', 'Pinterest', 'WhatsApp'];
            $selectedPlatforms = old('social_platforms', isset($asset) && $asset->social_platforms ? $asset->social_platforms : []);
        @endphp
        
        @foreach($platforms as $platform)
            <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="social_platforms[]" 
                       value="{{ $platform }}"
                       {{ in_array($platform, $selectedPlatforms) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">{{ $platform }}</span>
            </label>
        @endforeach
    </div>
</div>

<!-- Settings -->
<div class="mb-6 border-t pt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings</h3>
    
    <div class="mb-4">
        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
        <input type="number" 
               name="order" 
               id="order" 
               value="{{ old('order', $asset->order ?? 0) }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
    </div>

    <div class="space-y-2">
        <label class="flex items-center">
            <input type="checkbox" 
                   name="is_featured" 
                   value="1"
                   {{ old('is_featured', $asset->is_featured ?? false) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="ml-2 text-sm text-gray-700">Featured (Show prominently to attendees)</span>
        </label>

        <label class="flex items-center">
            <input type="checkbox" 
                   name="is_active" 
                   value="1"
                   {{ old('is_active', $asset->is_active ?? true) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="ml-2 text-sm text-gray-700">Active (Visible to attendees)</span>
        </label>
    </div>
</div>
