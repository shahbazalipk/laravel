<h2 class="text-lg font-semibold text-gray-800 mb-4">Email Settings</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-2">
                SMTP Host
            </label>
            <input type="text" 
                   name="smtp_host" 
                   id="smtp_host" 
                   value="{{ old('smtp_host', $event->smtp_host) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('smtp_host') border-red-500 @enderror"
                   placeholder="smtp.example.com">
            @error('smtp_host')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-2">
                SMTP Port
            </label>
            <input type="number" 
                   name="smtp_port" 
                   id="smtp_port" 
                   value="{{ old('smtp_port', $event->smtp_port) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('smtp_port') border-red-500 @enderror"
                   placeholder="587">
            @error('smtp_port')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="smtp_username" class="block text-sm font-medium text-gray-700 mb-2">
                SMTP Username
            </label>
            <input type="text" 
                   name="smtp_username" 
                   id="smtp_username" 
                   value="{{ old('smtp_username', $event->smtp_username) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('smtp_username') border-red-500 @enderror"
                   placeholder="username">
            @error('smtp_username')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="smtp_password" class="block text-sm font-medium text-gray-700 mb-2">
                SMTP Password
            </label>
            <input type="password" 
                   name="smtp_password" 
                   id="smtp_password" 
                   value="{{ old('smtp_password', $event->smtp_password) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('smtp_password') border-red-500 @enderror"
                   placeholder="••••••••">
            @error('smtp_password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="smtp_encryption" class="block text-sm font-medium text-gray-700 mb-2">
                SMTP Encryption
            </label>
            <select name="smtp_encryption" 
                    id="smtp_encryption"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('smtp_encryption') border-red-500 @enderror">
                <option value="">None</option>
                <option value="tls" {{ old('smtp_encryption', $event->smtp_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                <option value="ssl" {{ old('smtp_encryption', $event->smtp_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
            </select>
            @error('smtp_encryption')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="from_email" class="block text-sm font-medium text-gray-700 mb-2">
                From Email
            </label>
            <input type="email" 
                   name="from_email" 
                   id="from_email" 
                   value="{{ old('from_email', $event->from_email) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('from_email') border-red-500 @enderror"
                   placeholder="noreply@example.com">
            @error('from_email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="from_name" class="block text-sm font-medium text-gray-700 mb-2">
                From Name
            </label>
            <input type="text" 
                   name="from_name" 
                   id="from_name" 
                   value="{{ old('from_name', $event->from_name) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('from_name') border-red-500 @enderror"
                   placeholder="Event Name">
            @error('from_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
