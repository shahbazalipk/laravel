<div class="space-y-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-900 mb-1">AI-Powered Features</h3>
                <p class="text-sm text-blue-800">
                    Connect an LLM (Large Language Model) to enable AI-powered landing page generation, content analysis, and automated improvements.
                </p>
            </div>
        </div>
    </div>

    <!-- Enable LLM -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div>
            <label for="llm_enabled" class="block text-sm font-medium text-gray-900">
                Enable AI Features
            </label>
            <p class="text-sm text-gray-600 mt-1">
                Turn on AI-powered features for this event
            </p>
        </div>
        <div>
            <input type="checkbox" 
                   name="llm_enabled" 
                   id="llm_enabled" 
                   value="1"
                   {{ old('llm_enabled', $event->llm_enabled) ? 'checked' : '' }}
                   class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
        </div>
    </div>

    <!-- LLM Provider -->
    <div>
        <label for="llm_provider" class="block text-sm font-medium text-gray-700 mb-2">
            AI Provider
        </label>
        <select name="llm_provider" 
                id="llm_provider" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <option value="">Select Provider</option>
            <option value="openai" {{ old('llm_provider', $event->llm_provider) == 'openai' ? 'selected' : '' }}>
                OpenAI (GPT-4, GPT-3.5)
            </option>
            <option value="anthropic" {{ old('llm_provider', $event->llm_provider) == 'anthropic' ? 'selected' : '' }}>
                Anthropic (Claude 3)
            </option>
            <option value="google" {{ old('llm_provider', $event->llm_provider) == 'google' ? 'selected' : '' }}>
                Google (Gemini Pro)
            </option>
            <option value="azure" {{ old('llm_provider', $event->llm_provider) == 'azure' ? 'selected' : '' }}>
                Azure OpenAI
            </option>
        </select>
        @error('llm_provider')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- LLM Model -->
    <div>
        <label for="llm_model" class="block text-sm font-medium text-gray-700 mb-2">
            Model
        </label>
        <select name="llm_model" 
                id="llm_model" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <option value="">Select Model</option>
            <!-- OpenAI Models -->
            <optgroup label="OpenAI" id="openai-models" style="display: none;">
                <option value="gpt-4o" {{ old('llm_model', $event->llm_model) == 'gpt-4o' ? 'selected' : '' }}>GPT-4o (Latest)</option>
                <option value="gpt-4o-mini" {{ old('llm_model', $event->llm_model) == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Fast & Affordable)</option>
                <option value="gpt-4-turbo-preview" {{ old('llm_model', $event->llm_model) == 'gpt-4-turbo-preview' ? 'selected' : '' }}>GPT-4 Turbo</option>
                <option value="gpt-4" {{ old('llm_model', $event->llm_model) == 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                <option value="gpt-3.5-turbo" {{ old('llm_model', $event->llm_model) == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
            </optgroup>
            <!-- Anthropic Models -->
            <optgroup label="Anthropic" id="anthropic-models" style="display: none;">
                <option value="claude-3-opus-20240229" {{ old('llm_model', $event->llm_model) == 'claude-3-opus-20240229' ? 'selected' : '' }}>Claude 3 Opus</option>
                <option value="claude-3-sonnet-20240229" {{ old('llm_model', $event->llm_model) == 'claude-3-sonnet-20240229' ? 'selected' : '' }}>Claude 3 Sonnet</option>
                <option value="claude-3-haiku-20240307" {{ old('llm_model', $event->llm_model) == 'claude-3-haiku-20240307' ? 'selected' : '' }}>Claude 3 Haiku</option>
            </optgroup>
            <!-- Google Models -->
            <optgroup label="Google" id="google-models" style="display: none;">
                <option value="gemini-pro" {{ old('llm_model', $event->llm_model) == 'gemini-pro' ? 'selected' : '' }}>Gemini Pro</option>
                <option value="gemini-pro-vision" {{ old('llm_model', $event->llm_model) == 'gemini-pro-vision' ? 'selected' : '' }}>Gemini Pro Vision</option>
            </optgroup>
            <!-- Azure Models -->
            <optgroup label="Azure OpenAI" id="azure-models" style="display: none;">
                <option value="gpt-4" {{ old('llm_model', $event->llm_model) == 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                <option value="gpt-35-turbo" {{ old('llm_model', $event->llm_model) == 'gpt-35-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
            </optgroup>
        </select>
        @error('llm_model')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- API Key -->
    <div>
        <label for="llm_api_key" class="block text-sm font-medium text-gray-700 mb-2">
            API Key
        </label>
        <input type="password" 
               name="llm_api_key" 
               id="llm_api_key" 
               value="{{ old('llm_api_key', $event->llm_api_key ? '••••••••••••••••' : '') }}"
               placeholder="sk-..."
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm">
        <p class="mt-1 text-sm text-gray-500">
            Your API key is encrypted and stored securely. Leave blank to keep existing key.
        </p>
        @error('llm_api_key')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Advanced Settings -->
    <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Advanced Settings</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Temperature -->
            <div>
                <label for="llm_temperature" class="block text-sm font-medium text-gray-700 mb-2">
                    Temperature (Creativity)
                </label>
                <input type="number" 
                       name="llm_settings[temperature]" 
                       id="llm_temperature" 
                       value="{{ old('llm_settings.temperature', $event->llm_settings['temperature'] ?? 0.7) }}"
                       min="0" 
                       max="2" 
                       step="0.1"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <p class="mt-1 text-sm text-gray-500">0 = Focused, 2 = Creative (Default: 0.7)</p>
            </div>

            <!-- Max Tokens -->
            <div>
                <label for="llm_max_tokens" class="block text-sm font-medium text-gray-700 mb-2">
                    Max Tokens (Response Length)
                </label>
                <input type="number" 
                       name="llm_settings[max_tokens]" 
                       id="llm_max_tokens" 
                       value="{{ old('llm_settings.max_tokens', $event->llm_settings['max_tokens'] ?? 4000) }}"
                       min="100" 
                       max="8000" 
                       step="100"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <p class="mt-1 text-sm text-gray-500">Maximum response length (Default: 4000)</p>
            </div>
        </div>
    </div>

    <!-- Test Connection -->
    <div class="border-t border-gray-200 pt-6">
        <button type="button" 
                id="test-llm-connection"
                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            Test Connection
        </button>
        <div id="test-result" class="mt-4 hidden"></div>
    </div>

    <!-- Features Info -->
    <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Available AI Features</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-900">Landing Page Generation</p>
                    <p class="text-sm text-gray-600">Generate complete landing pages from prompts</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-900">Content Improvement</p>
                    <p class="text-sm text-gray-600">Enhance existing templates with AI suggestions</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-900">SEO Optimization</p>
                    <p class="text-sm text-gray-600">Generate SEO-friendly content and meta tags</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-900">Content Analysis</p>
                    <p class="text-sm text-gray-600">Analyze and improve event descriptions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide models based on provider selection
document.getElementById('llm_provider').addEventListener('change', function() {
    const provider = this.value;
    const allOptgroups = document.querySelectorAll('#llm_model optgroup');
    
    // Hide all optgroups
    allOptgroups.forEach(group => {
        group.style.display = 'none';
    });
    
    // Show selected provider's optgroup
    if (provider) {
        const selectedGroup = document.getElementById(provider + '-models');
        if (selectedGroup) {
            selectedGroup.style.display = 'block';
        }
    }
    
    // Reset model selection
    document.getElementById('llm_model').value = '';
});

// Trigger on page load to show correct models
document.addEventListener('DOMContentLoaded', function() {
    const provider = document.getElementById('llm_provider').value;
    if (provider) {
        const selectedGroup = document.getElementById(provider + '-models');
        if (selectedGroup) {
            selectedGroup.style.display = 'block';
        }
    }
});

// Test connection
document.getElementById('test-llm-connection').addEventListener('click', function() {
    const button = this;
    const resultDiv = document.getElementById('test-result');
    
    console.log('=== LLM Test Connection Started ===');
    
    button.disabled = true;
    button.textContent = 'Testing...';
    
    const provider = document.getElementById('llm_provider').value;
    const model = document.getElementById('llm_model').value;
    let apiKey = document.getElementById('llm_api_key').value;
    
    console.log('Form values:', { 
        provider, 
        model, 
        apiKeyLength: apiKey ? apiKey.length : 0,
        isPlaceholder: apiKey === '••••••••••••••••'
    });
    
    // Don't send placeholder
    if (apiKey === '••••••••••••••••') {
        console.error('API key is placeholder');
        resultDiv.className = 'mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800';
        resultDiv.textContent = 'Please enter a new API key to test. The existing key cannot be tested for security reasons.';
        resultDiv.classList.remove('hidden');
        button.disabled = false;
        button.textContent = 'Test Connection';
        return;
    }
    
    if (!provider || !model || !apiKey) {
        console.error('Missing required fields:', { provider: !!provider, model: !!model, apiKey: !!apiKey });
        resultDiv.className = 'mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800';
        resultDiv.textContent = 'Please fill in Provider, Model, and API Key first.';
        resultDiv.classList.remove('hidden');
        button.disabled = false;
        button.textContent = 'Test Connection';
        return;
    }
    
    // Check for CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token meta tag not found');
        resultDiv.className = 'mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800';
        resultDiv.textContent = 'Error: CSRF token not found. Please refresh the page.';
        resultDiv.classList.remove('hidden');
        button.disabled = false;
        button.textContent = 'Test Connection';
        return;
    }
    
    console.log('CSRF token found:', csrfToken.content.substring(0, 10) + '...');
    console.log('Sending request to:', '/admin/llm/test-connection');
    
    const requestBody = { provider, model, api_key: apiKey };
    console.log('Request body:', { ...requestBody, api_key: '[REDACTED]' });
    
    fetch('/admin/llm/test-connection', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestBody)
    })
    .then(response => {
        console.log('Response received:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok,
            headers: Object.fromEntries([...response.headers.entries()])
        });
        
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response body:', text);
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800';
            resultDiv.textContent = '✓ Connection successful! Model: ' + data.model;
        } else {
            resultDiv.className = 'mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800';
            resultDiv.textContent = '✗ Connection failed: ' + data.message;
        }
        resultDiv.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Fetch error:', error);
        console.error('Error stack:', error.stack);
        resultDiv.className = 'mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800';
        resultDiv.textContent = '✗ Error: ' + error.message + '. Check browser console for details.';
        resultDiv.classList.remove('hidden');
    })
    .finally(() => {
        console.log('=== LLM Test Connection Finished ===');
        button.disabled = false;
        button.textContent = 'Test Connection';
    });
});
</script>
