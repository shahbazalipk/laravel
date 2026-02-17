@extends('admin.layout')

@section('title', 'Edit Email Provider')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.email-campaigns.provider-configs.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Email Provider</h1>
            <p class="text-gray-600 mt-1">Update {{ ucfirst($providerConfig->provider_name) }} configuration</p>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.email-campaigns.provider-configs.update', $providerConfig) }}" method="POST" id="provider-form">
        @csrf
        @method('PUT')
        
        <!-- Provider Selection -->
        <div class="mb-6">
            <label for="provider_name" class="block text-sm font-medium text-gray-700 mb-2">
                Email Provider <span class="text-red-500">*</span>
            </label>
            <select name="provider_name" 
                    id="provider_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('provider_name') border-red-500 @enderror"
                    required>
                <option value="">Select a provider</option>
                @foreach($availableProviders as $key => $name)
                    <option value="{{ $key }}" {{ old('provider_name', $providerConfig->provider_name) == $key ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            @error('provider_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Choose your email service provider</p>
        </div>

        <!-- Infobip Credentials -->
        <div id="infobip-fields" class="provider-fields {{ old('provider_name', $providerConfig->provider_name) === 'infobip' ? '' : 'hidden' }}">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium">Infobip Configuration</p>
                        <p class="mt-1">API-based provider with tracking support. Get your credentials from the Infobip dashboard.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="infobip_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                        API Key <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="api_key" 
                           id="infobip_api_key" 
                           value="{{ old('api_key', $providerConfig->credentials['api_key'] ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('api_key') border-red-500 @enderror"
                           placeholder="Your Infobip API key">
                    @error('api_key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="infobip_base_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Base URL <span class="text-red-500">*</span>
                    </label>
                    <input type="url" 
                           name="base_url" 
                           id="infobip_base_url" 
                           value="{{ old('base_url', $providerConfig->credentials['base_url'] ?? 'https://api.infobip.com') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('base_url') border-red-500 @enderror"
                           placeholder="https://api.infobip.com">
                    @error('base_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Mailchimp Credentials -->
        <div id="mailchimp-fields" class="provider-fields {{ old('provider_name', $providerConfig->provider_name) === 'mailchimp' ? '' : 'hidden' }}">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <p class="font-medium">Mailchimp Transactional Configuration</p>
                        <p class="mt-1">Uses Mailchimp Transactional API (formerly Mandrill) with advanced analytics.</p>
                    </div>
                </div>
            </div>

            <div>
                <label for="mailchimp_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                    API Key <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="api_key" 
                       id="mailchimp_api_key" 
                       value="{{ old('api_key', $providerConfig->credentials['api_key'] ?? '') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('api_key') border-red-500 @enderror"
                       placeholder="Your Mailchimp Transactional API key">
                @error('api_key')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- SMTP Credentials -->
        <div id="smtp-fields" class="provider-fields {{ old('provider_name', $providerConfig->provider_name) === 'smtp' ? '' : 'hidden' }}">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-gray-800">
                        <p class="font-medium">SMTP Configuration</p>
                        <p class="mt-1">Standard SMTP server with basic tracking. Works as a fallback option.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-2">
                        SMTP Host <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="host" 
                           id="smtp_host" 
                           value="{{ old('host', $providerConfig->credentials['host'] ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('host') border-red-500 @enderror"
                           placeholder="smtp.example.com">
                    @error('host')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-2">
                        Port <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="port" 
                           id="smtp_port" 
                           value="{{ old('port', $providerConfig->credentials['port'] ?? '587') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('port') border-red-500 @enderror"
                           placeholder="587">
                    @error('port')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="smtp_username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="username" 
                           id="smtp_username" 
                           value="{{ old('username', $providerConfig->credentials['username'] ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('username') border-red-500 @enderror"
                           placeholder="your-username">
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="smtp_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           id="smtp_password" 
                           value="{{ old('password', $providerConfig->credentials['password'] ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-500 @enderror"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="smtp_encryption" class="block text-sm font-medium text-gray-700 mb-2">
                        Encryption <span class="text-red-500">*</span>
                    </label>
                    <select name="encryption" 
                            id="smtp_encryption"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('encryption') border-red-500 @enderror">
                        <option value="tls" {{ old('encryption', $providerConfig->credentials['encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('encryption', $providerConfig->credentials['encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ old('encryption', $providerConfig->credentials['encryption'] ?? '') == 'none' ? 'selected' : '' }}>None</option>
                    </select>
                    @error('encryption')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Common Settings -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Settings</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="rate_limit" class="block text-sm font-medium text-gray-700 mb-2">
                        Rate Limit (emails per minute)
                    </label>
                    <input type="number" 
                           name="rate_limit" 
                           id="rate_limit" 
                           value="{{ old('rate_limit', $providerConfig->settings['rate_limit'] ?? 100) }}"
                           min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('rate_limit') border-red-500 @enderror"
                           placeholder="100">
                    @error('rate_limit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Maximum emails to send per minute</p>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_default" 
                               value="1"
                               {{ old('is_default', $providerConfig->is_default) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Set as default provider</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.email-campaigns.provider-configs.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Test & Update Provider
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // Show/hide provider-specific fields based on selection
    const providerSelect = document.getElementById('provider_name');
    const providerFields = document.querySelectorAll('.provider-fields');
    
    function updateProviderFields() {
        const selectedProvider = providerSelect.value;
        
        // Hide all provider fields
        providerFields.forEach(field => {
            field.classList.add('hidden');
            // Disable inputs in hidden fields
            field.querySelectorAll('input, select').forEach(input => {
                input.removeAttribute('required');
            });
        });
        
        // Show selected provider fields
        if (selectedProvider) {
            const targetField = document.getElementById(`${selectedProvider}-fields`);
            if (targetField) {
                targetField.classList.remove('hidden');
                // Enable required inputs in visible fields
                targetField.querySelectorAll('input[data-required], select[data-required]').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            }
        }
    }
    
    // Initialize on page load
    updateProviderFields();
    
    // Update when provider changes
    providerSelect.addEventListener('change', updateProviderFields);
</script>
@endsection
