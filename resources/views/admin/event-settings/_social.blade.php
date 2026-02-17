<h2 class="text-lg font-semibold text-gray-800 mb-4">Social Media Settings</h2>

<div class="space-y-6">
        <div>
            <label for="twitter_mention" class="block text-sm font-medium text-gray-700 mb-2">
                Twitter Mention
            </label>
            <input type="text" 
                   name="twitter_mention" 
                   id="twitter_mention" 
                   value="{{ old('twitter_mention', $event->twitter_mention) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('twitter_mention') border-red-500 @enderror"
                   placeholder="@yourevent">
            @error('twitter_mention')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="social_media_description" class="block text-sm font-medium text-gray-700 mb-2">
                Social Media Description
            </label>
            <textarea name="social_media_description" 
                      id="social_media_description" 
                      rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('social_media_description') border-red-500 @enderror"
                      placeholder="Description for social media sharing">{{ old('social_media_description', $event->social_media_description) }}</textarea>
            @error('social_media_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="social_media_share_banner" class="block text-sm font-medium text-gray-700 mb-2">
                Social Media Share Banner
            </label>
            <input type="text" 
                   name="social_media_share_banner" 
                   id="social_media_share_banner" 
                   value="{{ old('social_media_share_banner', $event->social_media_share_banner) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('social_media_share_banner') border-red-500 @enderror"
                   placeholder="Banner image URL for social sharing">
            @error('social_media_share_banner')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="social_media_share_font_color" class="block text-sm font-medium text-gray-700 mb-2">
                Social Media Share Font Color
            </label>
            <div class="flex items-center space-x-3">
                <input type="color" 
                       name="social_media_share_font_color" 
                       id="social_media_share_font_color" 
                       value="{{ old('social_media_share_font_color', $event->social_media_share_font_color ?? '#000000') }}"
                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                <input type="text" 
                       id="social_media_share_font_color_text"
                       value="{{ old('social_media_share_font_color', $event->social_media_share_font_color ?? '#000000') }}"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                       readonly>
            </div>
            @error('social_media_share_font_color')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

<script>
    const colorPicker = document.getElementById('social_media_share_font_color');
    const colorText = document.getElementById('social_media_share_font_color_text');
    if (colorPicker && colorText) {
        colorPicker.addEventListener('input', function() {
            colorText.value = this.value;
        });
    }
</script>
