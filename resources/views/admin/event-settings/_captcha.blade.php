<h2 class="text-lg font-semibold text-gray-800 mb-4">Captcha Keys</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="recaptcha_site_key" class="block text-sm font-medium text-gray-700 mb-2">
                reCAPTCHA Site Key
            </label>
            <input type="text" 
                   name="recaptcha_site_key" 
                   id="recaptcha_site_key" 
                   value="{{ old('recaptcha_site_key', $event->recaptcha_site_key) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('recaptcha_site_key') border-red-500 @enderror"
                   placeholder="6Lc...">
            @error('recaptcha_site_key')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="recaptcha_secret_key" class="block text-sm font-medium text-gray-700 mb-2">
                reCAPTCHA Secret Key
            </label>
            <input type="password" 
                   name="recaptcha_secret_key" 
                   id="recaptcha_secret_key" 
                   value="{{ old('recaptcha_secret_key', $event->recaptcha_secret_key) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('recaptcha_secret_key') border-red-500 @enderror"
                   placeholder="6Lc...">
            @error('recaptcha_secret_key')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
