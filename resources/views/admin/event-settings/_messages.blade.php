<h2 class="text-lg font-semibold text-gray-800 mb-4">Messages & Content</h2>

<div class="space-y-6">
        <div>
            <label for="closed_message" class="block text-sm font-medium text-gray-700 mb-2">
                Closed Message
            </label>
            <textarea name="closed_message" 
                      id="closed_message" 
                      rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('closed_message') border-red-500 @enderror"
                      placeholder="Message to display when registration is closed">{{ old('closed_message', $event->closed_message) }}</textarea>
            @error('closed_message')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="footer_information" class="block text-sm font-medium text-gray-700 mb-2">
                Footer Information
            </label>
            <textarea name="footer_information" 
                      id="footer_information" 
                      rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('footer_information') border-red-500 @enderror"
                      placeholder="Footer text and information">{{ old('footer_information', $event->footer_information) }}</textarea>
            @error('footer_information')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
