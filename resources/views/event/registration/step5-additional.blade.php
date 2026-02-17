<h2 class="text-2xl font-bold text-gray-900 mb-6">Additional Information</h2>

<div class="space-y-6">
    <p class="text-gray-600">Help us personalize your event experience.</p>

    <!-- Dietary Requirements -->
    <div>
        <label for="dietary_requirements" class="block text-sm font-medium text-gray-700 mb-2">
            Dietary Requirements
        </label>
        <textarea name="dietary_requirements" 
                  id="dietary_requirements" 
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="Please specify any dietary restrictions or preferences (e.g., vegetarian, vegan, gluten-free, allergies)">{{ old('dietary_requirements', $formData['dietary_requirements'] ?? '') }}</textarea>
        @error('dietary_requirements')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Special Needs -->
    <div>
        <label for="special_needs" class="block text-sm font-medium text-gray-700 mb-2">
            Special Needs / Accessibility Requirements
        </label>
        <textarea name="special_needs" 
                  id="special_needs" 
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="Please let us know if you require any special accommodations (e.g., wheelchair access, sign language interpreter)">{{ old('special_needs', $formData['special_needs'] ?? '') }}</textarea>
        @error('special_needs')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- T-Shirt Size and How Did You Hear -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="tshirt_size" class="block text-sm font-medium text-gray-700 mb-2">
                T-Shirt Size
            </label>
            <select name="tshirt_size" 
                    id="tshirt_size"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Select Size</option>
                <option value="XS" {{ old('tshirt_size', $formData['tshirt_size'] ?? '') == 'XS' ? 'selected' : '' }}>XS</option>
                <option value="S" {{ old('tshirt_size', $formData['tshirt_size'] ?? '') == 'S' ? 'selected' : '' }}>S</option>
                <option value="M" {{ old('tshirt_size', $formData['tshirt_size'] ?? '') == 'M' ? 'selected' : '' }}>M</option>
                <option value="L" {{ old('tshirt_size', $formData['tshirt_size'] ?? '') == 'L' ? 'selected' : '' }}>L</option>
                <option value="XL" {{ old('tshirt_size', $formData['tshirt_size'] ?? '') == 'XL' ? 'selected' : '' }}>XL</option>
                <option value="XXL" {{ old('tshirt_size', $formData['tshirt_size'] ?? '') == 'XXL' ? 'selected' : '' }}>XXL</option>
                <option value="XXXL" {{ old('tshirt_size', $formData['tshirt_size'] ?? '') == 'XXXL' ? 'selected' : '' }}>XXXL</option>
            </select>
            @error('tshirt_size')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="how_did_you_hear" class="block text-sm font-medium text-gray-700 mb-2">
                How Did You Hear About Us?
            </label>
            <select name="how_did_you_hear" 
                    id="how_did_you_hear"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Select Option</option>
                <option value="Social Media" {{ old('how_did_you_hear', $formData['how_did_you_hear'] ?? '') == 'Social Media' ? 'selected' : '' }}>Social Media</option>
                <option value="Email" {{ old('how_did_you_hear', $formData['how_did_you_hear'] ?? '') == 'Email' ? 'selected' : '' }}>Email</option>
                <option value="Website" {{ old('how_did_you_hear', $formData['how_did_you_hear'] ?? '') == 'Website' ? 'selected' : '' }}>Website</option>
                <option value="Friend/Colleague" {{ old('how_did_you_hear', $formData['how_did_you_hear'] ?? '') == 'Friend/Colleague' ? 'selected' : '' }}>Friend/Colleague</option>
                <option value="Advertisement" {{ old('how_did_you_hear', $formData['how_did_you_hear'] ?? '') == 'Advertisement' ? 'selected' : '' }}>Advertisement</option>
                <option value="Search Engine" {{ old('how_did_you_hear', $formData['how_did_you_hear'] ?? '') == 'Search Engine' ? 'selected' : '' }}>Search Engine</option>
                <option value="Other" {{ old('how_did_you_hear', $formData['how_did_you_hear'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('how_did_you_hear')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Areas of Interest -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">
            Areas of Interest
        </label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="areas_of_interest[]" 
                       value="Networking"
                       {{ in_array('Networking', old('areas_of_interest', $formData['areas_of_interest'] ?? [])) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Networking</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" 
                       name="areas_of_interest[]" 
                       value="Workshops"
                       {{ in_array('Workshops', old('areas_of_interest', $formData['areas_of_interest'] ?? [])) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Workshops</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" 
                       name="areas_of_interest[]" 
                       value="Keynote Speeches"
                       {{ in_array('Keynote Speeches', old('areas_of_interest', $formData['areas_of_interest'] ?? [])) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Keynote Speeches</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" 
                       name="areas_of_interest[]" 
                       value="Exhibitions"
                       {{ in_array('Exhibitions', old('areas_of_interest', $formData['areas_of_interest'] ?? [])) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Exhibitions</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" 
                       name="areas_of_interest[]" 
                       value="Panel Discussions"
                       {{ in_array('Panel Discussions', old('areas_of_interest', $formData['areas_of_interest'] ?? [])) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Panel Discussions</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" 
                       name="areas_of_interest[]" 
                       value="Product Demos"
                       {{ in_array('Product Demos', old('areas_of_interest', $formData['areas_of_interest'] ?? [])) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Product Demos</span>
            </label>
        </div>
        @error('areas_of_interest')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Marketing Consent -->
    <div class="bg-gray-50 rounded-lg p-4">
        <label class="flex items-start">
            <input type="checkbox" 
                   name="marketing_consent" 
                   value="1"
                   {{ old('marketing_consent', $formData['marketing_consent'] ?? false) ? 'checked' : '' }}
                   class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span class="ml-3 text-sm text-gray-700">
                I would like to receive updates, newsletters, and promotional materials about future events and services.
            </span>
        </label>
    </div>

    <!-- Terms and Conditions -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
        <label class="flex items-start">
            <input type="checkbox" 
                   name="terms_accepted" 
                   value="1"
                   {{ old('terms_accepted', $formData['terms_accepted'] ?? false) ? 'checked' : '' }}
                   class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                   required>
            <span class="ml-3 text-sm text-gray-700">
                <span class="text-red-500">*</span> I have read and agree to the 
                @if($event->terms_url)
                    <a href="{{ $event->terms_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">Terms and Conditions</a>
                @else
                    Terms and Conditions
                @endif
                of this event.
            </span>
        </label>
        @error('terms_accepted')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
