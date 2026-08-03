@php
    $isEdit = isset($membership);
    $selectedIdentifier = old('identifier_type', $isEdit ? $membership->identifierType()->value : 'membership_id');
    $selectedVerification = old('verification_type', $isEdit ? $membership->verification_type : 'upload_file');
    $sampleRequest = old('api_sample_request', $isEdit ? ($membership->api_sample_request ?: $defaultSampleRequest) : $defaultSampleRequest);
    $sampleResponse = old('api_sample_response', $isEdit ? ($membership->api_sample_response ?: $defaultSampleResponse) : $defaultSampleResponse);
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="mb-2 block text-sm font-medium text-gray-700">
            List name <span class="text-red-500">*</span>
        </label>
        <input type="text"
               name="name"
               id="name"
               value="{{ old('name', $isEdit ? $membership->name : '') }}"
               required
               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
               placeholder="e.g. IEEE Members, Student roster"
               data-testid="membership-name">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="slug" class="mb-2 block text-sm font-medium text-gray-700">
            Slug <span class="text-xs text-gray-400">(auto-generated if empty)</span>
        </label>
        <input type="text"
               name="slug"
               id="slug"
               value="{{ old('slug', $isEdit ? $membership->slug : '') }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('slug') border-red-500 @enderror"
               placeholder="e.g. ieee-members">
        @error('slug')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="identifier_type" class="mb-2 block text-sm font-medium text-gray-700">
            Identifier type <span class="text-red-500">*</span>
        </label>
        <select name="identifier_type"
                id="identifier_type"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('identifier_type') border-red-500 @enderror"
                data-testid="membership-identifier-type">
            @foreach($identifierTypes as $type)
                <option value="{{ $type->value }}" {{ $selectedIdentifier === $type->value ? 'selected' : '' }}>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">What kind of values this list will verify during registration.</p>
        @error('identifier_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="verification_type" class="mb-2 block text-sm font-medium text-gray-700">
            Source <span class="text-red-500">*</span>
        </label>
        <select name="verification_type"
                id="verification_type"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('verification_type') border-red-500 @enderror"
                data-testid="membership-verification-type">
            <option value="upload_file" {{ $selectedVerification === 'upload_file' ? 'selected' : '' }}>CSV / TXT upload list</option>
            <option value="third_party_api" {{ $selectedVerification === 'third_party_api' ? 'selected' : '' }}>Third-party API</option>
        </select>
        <p class="mt-1 text-xs text-gray-500">Upload a list now, or configure an external API for live checks.</p>
        @error('verification_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div id="file-upload-section" class="mt-6 {{ $selectedVerification === 'upload_file' ? '' : 'hidden' }}">
    <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6">
        <label for="membership_file" class="mb-2 block text-sm font-medium text-gray-700">
            Upload CSV / TXT
            @if(! $isEdit)
                <span class="text-red-500">*</span>
            @endif
        </label>
        @if($isEdit && $membership->file_path)
            <div class="mb-3 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800">
                Current file: {{ basename($membership->file_path) }}
            </div>
        @endif
        <input type="file"
               name="membership_file"
               id="membership_file"
               accept=".txt,.csv,text/csv,text/plain"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('membership_file') border-red-500 @enderror"
               data-testid="membership-file">
        <p class="mt-2 text-xs text-gray-500">
            One identifier per line, or CSV with a header such as <code>membership_id</code>, <code>email</code>, or <code>student_id</code>.
            Uploaded values are imported into the searchable list automatically.
        </p>
        <div class="mt-3 flex flex-wrap gap-3 text-sm">
            <a href="{{ asset('samples/membership-ids-sample.csv') }}" download class="text-indigo-600 hover:text-indigo-800">Sample Membership IDs CSV</a>
            <a href="{{ asset('samples/email-ids-sample.csv') }}" download class="text-indigo-600 hover:text-indigo-800">Sample Email IDs CSV</a>
            <a href="{{ asset('samples/student-ids-sample.csv') }}" download class="text-indigo-600 hover:text-indigo-800">Sample Student IDs CSV</a>
        </div>
        @error('membership_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div id="api-section" class="mt-6 {{ $selectedVerification === 'third_party_api' ? '' : 'hidden' }}">
    <div class="space-y-6 rounded-xl border border-indigo-200 bg-indigo-50 p-6" data-testid="membership-api-section">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="api_endpoint" class="mb-2 block text-sm font-medium text-gray-700">
                    API endpoint <span class="text-red-500">*</span>
                </label>
                <input type="url"
                       name="api_endpoint"
                       id="api_endpoint"
                       value="{{ old('api_endpoint', $isEdit ? $membership->api_endpoint : '') }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('api_endpoint') border-red-500 @enderror"
                       placeholder="https://api.example.com/verify"
                       data-testid="membership-api-endpoint">
                @error('api_endpoint')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="api_method" class="mb-2 block text-sm font-medium text-gray-700">HTTP method</label>
                <select name="api_method"
                        id="api_method"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                        data-testid="membership-api-method">
                    @php $method = old('api_method', $isEdit ? ($membership->api_method ?: 'GET') : 'GET'); @endphp
                    <option value="GET" {{ $method === 'GET' ? 'selected' : '' }}>GET</option>
                    <option value="POST" {{ $method === 'POST' ? 'selected' : '' }}>POST</option>
                </select>
            </div>

            <div>
                <label for="api_key" class="mb-2 block text-sm font-medium text-gray-700">API key / Bearer token</label>
                <input type="text"
                       name="api_key"
                       id="api_key"
                       value="{{ old('api_key', $isEdit ? $membership->api_key : '') }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2 font-mono text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                       placeholder="optional-secret">
                <p class="mt-1 text-xs text-gray-500">Sent as <code>Authorization: Bearer …</code> when provided.</p>
            </div>
        </div>

        <div>
            <label for="api_sample_request" class="mb-2 block text-sm font-medium text-gray-700">
                Sample request body / payload
            </label>
            <textarea name="api_sample_request"
                      id="api_sample_request"
                      rows="8"
                      class="w-full rounded-lg border border-gray-300 px-4 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                      data-testid="membership-api-sample-request"
                      placeholder='{"identifier":"@{{identifier}}"}'>{{ $sampleRequest }}</textarea>
            <p class="mt-1 text-xs text-gray-500">
                Use <code>@{{identifier}}</code> and <code>@{{identifier_type}}</code> placeholders. For GET, fields become query parameters; for POST, they become the JSON body.
            </p>
            @error('api_sample_request')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="api_sample_response" class="mb-2 block text-sm font-medium text-gray-700">
                Sample success response
            </label>
            <textarea name="api_sample_response"
                      id="api_sample_response"
                      rows="8"
                      class="w-full rounded-lg border border-gray-300 px-4 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                      data-testid="membership-api-sample-response">{{ $sampleResponse }}</textarea>
            <p class="mt-1 text-xs text-gray-500">
                Document the expected API response for future registration integration. A boolean <code>valid</code> field is preferred.
            </p>
            @error('api_sample_response')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="color" class="mb-2 block text-sm font-medium text-gray-700">Color</label>
        <div class="flex items-center space-x-3">
            <input type="color"
                   name="color"
                   id="color"
                   value="{{ old('color', $isEdit ? $membership->color : '#6366f1') }}"
                   class="h-10 w-20 cursor-pointer rounded border border-gray-300">
            <input type="text"
                   id="color-text"
                   value="{{ old('color', $isEdit ? $membership->color : '#6366f1') }}"
                   class="flex-1 rounded-lg border border-gray-300 px-4 py-2 font-mono text-sm"
                   readonly>
        </div>
    </div>
    <div>
        <label for="sort_order" class="mb-2 block text-sm font-medium text-gray-700">Sort order</label>
        <input type="number"
               name="sort_order"
               id="sort_order"
               value="{{ old('sort_order', $isEdit ? $membership->sort_order : 0) }}"
               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500">
    </div>
</div>

<div class="mt-6">
    <label for="description" class="mb-2 block text-sm font-medium text-gray-700">Description</label>
    <textarea name="description"
              id="description"
              rows="3"
              class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
              placeholder="Optional notes for admins">{{ old('description', $isEdit ? $membership->description : '') }}</textarea>
</div>

<div class="mt-6">
    <label class="flex items-center">
        <input type="checkbox"
               name="is_active"
               value="1"
               {{ old('is_active', $isEdit ? $membership->is_active : true) ? 'checked' : '' }}
               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <span class="ml-2 text-sm text-gray-700">Active (usable in future registration flows)</span>
    </label>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const colorPicker = document.getElementById('color');
        const colorText = document.getElementById('color-text');
        colorPicker?.addEventListener('input', () => { colorText.value = colorPicker.value; });

        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        nameInput?.addEventListener('input', () => {
            if (!slugInput.value || slugInput.dataset.autoGenerated) {
                slugInput.value = nameInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                slugInput.dataset.autoGenerated = 'true';
            }
        });
        slugInput?.addEventListener('input', () => {
            if (slugInput.value) delete slugInput.dataset.autoGenerated;
        });

        const verificationType = document.getElementById('verification_type');
        const fileSection = document.getElementById('file-upload-section');
        const apiSection = document.getElementById('api-section');
        const fileInput = document.getElementById('membership_file');
        const apiEndpoint = document.getElementById('api_endpoint');
        const isEdit = {{ $isEdit ? 'true' : 'false' }};

        const toggleSections = () => {
            const type = verificationType.value;
            const isUpload = type === 'upload_file';
            const isApi = type === 'third_party_api';
            fileSection.classList.toggle('hidden', !isUpload);
            apiSection.classList.toggle('hidden', !isApi);
            if (fileInput) fileInput.required = isUpload && !isEdit;
            if (apiEndpoint) apiEndpoint.required = isApi;
        };

        verificationType?.addEventListener('change', toggleSections);
        toggleSections();
    });
</script>
@endpush
