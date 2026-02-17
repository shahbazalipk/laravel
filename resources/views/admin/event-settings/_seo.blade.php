<h2 class="text-lg font-semibold text-gray-800 mb-4">SEO Information</h2>

<div class="space-y-6">
        <div>
            <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-2">
                SEO Title
            </label>
            <input type="text" 
                   name="seo_title" 
                   id="seo_title" 
                   value="{{ old('seo_title', $event->seo_title) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('seo_title') border-red-500 @enderror"
                   placeholder="Page title for search engines">
            @error('seo_title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-2">
                SEO Description
            </label>
            <textarea name="seo_description" 
                      id="seo_description" 
                      rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('seo_description') border-red-500 @enderror"
                      placeholder="Meta description for search engines">{{ old('seo_description', $event->seo_description) }}</textarea>
            @error('seo_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="seo_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                SEO Keywords
            </label>
            <input type="text" 
                   name="seo_keywords" 
                   id="seo_keywords" 
                   value="{{ old('seo_keywords', $event->seo_keywords) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('seo_keywords') border-red-500 @enderror"
                   placeholder="keyword1, keyword2, keyword3">
            @error('seo_keywords')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

<div class="mt-8"></div>
