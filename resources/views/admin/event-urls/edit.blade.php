@extends('admin.layout')

@section('title', 'Edit Event URL')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.event-urls.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Event URL</h1>
            <p class="text-gray-600 mt-1">Update registration URL settings</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.event-urls.update', $eventUrl) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $eventUrl->name) }}"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                    Slug <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="slug" 
                       id="slug" 
                       value="{{ old('slug', $eventUrl->slug) }}"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                       required>
                <p class="mt-1 text-xs text-gray-500">URL-friendly identifier (lowercase, hyphens only)</p>
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                    URL Type <span class="text-red-500">*</span>
                </label>
                <select name="type" 
                        id="type" 
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ old('type', $eventUrl->type) === $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Status
                </label>
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           id="is_active" 
                           value="1"
                           {{ old('is_active', $eventUrl->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">
                        Active (URL is accessible)
                    </label>
                </div>
            </div>
        </div>

        @php
            $selectedFormat = old(
                'registration_format',
                $eventUrl->registration_format?->value ?? \App\Registration\Enums\RegistrationFormat::MultiStep->value
            );
        @endphp
        <div id="registration-format-options"
             class="mt-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg"
             style="display: {{ old('type', $eventUrl->type) === 'online' ? 'block' : 'none' }};"
             data-testid="registration-format-options">
            <label for="registration_format" class="block text-sm font-semibold text-gray-800 mb-2">
                Registration form format
            </label>
            <p class="text-xs text-gray-600 mb-3">Choose how registrants complete online registration for this URL.</p>
            <select name="registration_format"
                    id="registration_format"
                    class="w-full md:w-1/2 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    data-testid="registration-format-select">
                @foreach(\App\Registration\Enums\RegistrationFormat::options() as $format)
                    <option value="{{ $format->value }}" {{ $selectedFormat === $format->value ? 'selected' : '' }}>
                        {{ $format->label() }}
                    </option>
                @endforeach
            </select>
            <p id="registration-format-help" class="mt-2 text-xs text-gray-600"></p>
            @error('registration_format')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>
            <textarea name="description" 
                      id="description" 
                      rows="3"
                      class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $eventUrl->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <section class="mt-8 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-5 sm:p-6" data-testid="event-url-page-content">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Online registration page</p>
                <h2 class="mt-1 text-lg font-semibold text-gray-900">Sponsors, partners and custom content</h2>
                <p class="mt-1 text-sm text-gray-600">Choose the organizations and extra content shown beneath the registration wizard.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-800">Sponsors</label>
                    <p class="mb-3 mt-1 text-xs text-gray-500">Only active sponsors marked visible online are available.</p>
                    <div class="max-h-64 space-y-2 overflow-y-auto rounded-xl border border-gray-200 bg-white p-3" data-testid="event-url-sponsors">
                        @forelse($sponsors as $sponsor)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-transparent p-2 transition hover:border-indigo-100 hover:bg-indigo-50">
                                <input type="checkbox"
                                       name="sponsor_ids[]"
                                       value="{{ $sponsor->id }}"
                                       {{ in_array($sponsor->id, old('sponsor_ids', $eventUrl->sponsors->modelKeys())) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                @if($sponsor->logo_thumbnail_url)
                                    <img src="{{ $sponsor->logo_thumbnail_url }}" alt="" class="h-9 w-12 rounded bg-white object-contain">
                                @endif
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-gray-800">{{ $sponsor->name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $sponsor->sponsorship_label ?: ucfirst((string) $sponsor->type) }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="p-3 text-sm text-gray-500">No online sponsors are available.</p>
                        @endforelse
                    </div>
                    @error('sponsor_ids.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-800">Partners</label>
                    <p class="mb-3 mt-1 text-xs text-gray-500">Only active partners marked visible online are available.</p>
                    <div class="max-h-64 space-y-2 overflow-y-auto rounded-xl border border-gray-200 bg-white p-3" data-testid="event-url-partners">
                        @forelse($partners as $partner)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-transparent p-2 transition hover:border-indigo-100 hover:bg-indigo-50">
                                <input type="checkbox"
                                       name="partner_ids[]"
                                       value="{{ $partner->id }}"
                                       {{ in_array($partner->id, old('partner_ids', $eventUrl->partners->modelKeys())) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                @if($partner->logo_thumbnail_url)
                                    <img src="{{ $partner->logo_thumbnail_url }}" alt="" class="h-9 w-12 rounded bg-white object-contain">
                                @endif
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-gray-800">{{ $partner->name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $partner->sponsorship_label ?: ucfirst((string) $partner->type) }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="p-3 text-sm text-gray-500">No online partners are available.</p>
                        @endforelse
                    </div>
                    @error('partner_ids.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="custom_html" class="block text-sm font-semibold text-gray-800">Extra custom HTML</label>
                <p class="mb-2 mt-1 text-xs text-gray-500">
                    Add headings, paragraphs, links, lists or images. Scripts, event handlers and unsafe markup are removed automatically.
                </p>
                <textarea name="custom_html"
                          id="custom_html"
                          rows="9"
                          maxlength="20000"
                          spellcheck="false"
                          data-testid="event-url-custom-html"
                          placeholder="<h2>Before you register</h2>&#10;<p>Add useful information here.</p>"
                          class="w-full rounded-xl border-gray-300 bg-white font-mono text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('custom_html', $eventUrl->custom_html) }}</textarea>
                @error('custom_html')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <!-- Badge Printing Options (only for badge type) -->
        <div id="badge-options" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg" style="display: {{ old('type', $eventUrl->type) === 'badge' ? 'block' : 'none' }};">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Badge Printing Options</h3>
            
            <div class="space-y-3">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="allow_reprint" 
                           id="allow_reprint" 
                           value="1"
                           {{ old('allow_reprint', $eventUrl->allow_reprint) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="allow_reprint" class="ml-2 text-sm text-gray-700">
                        Allow Badge Reprint
                        <span class="text-xs text-gray-500 block">Users can print their badge multiple times</span>
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" 
                           name="allow_print_from_photo" 
                           id="allow_print_from_photo" 
                           value="1"
                           {{ old('allow_print_from_photo', $eventUrl->allow_print_from_photo) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="allow_print_from_photo" class="ml-2 text-sm text-gray-700">
                        Allow Print Badge from Photo
                        <span class="text-xs text-gray-500 block">Users can capture/upload photo during badge printing</span>
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" 
                           name="enable_barcode_scanner" 
                           id="enable_barcode_scanner" 
                           value="1"
                           {{ old('enable_barcode_scanner', $eventUrl->enable_barcode_scanner ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="enable_barcode_scanner" class="ml-2 text-sm text-gray-700">
                        Enable QR/Barcode Scanner
                        <span class="text-xs text-gray-500 block">Show QR/Barcode scanner tab for quick scanning</span>
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" 
                           name="enable_manual_input" 
                           id="enable_manual_input" 
                           value="1"
                           {{ old('enable_manual_input', $eventUrl->enable_manual_input ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="enable_manual_input" class="ml-2 text-sm text-gray-700">
                        Enable Manual Text Input
                        <span class="text-xs text-gray-500 block">Show manual search field as fallback option</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Enabled Categories -->
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Enabled Registration Categories
            </label>
            <p class="text-xs text-gray-500 mb-3">Select which categories are available for this URL. Leave empty to allow all categories.</p>
            
            @if($categories->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">No active registration categories found.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($categories as $category)
                        <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <input type="checkbox" 
                                   name="enabled_categories[]" 
                                   id="category_{{ $category->id }}" 
                                   value="{{ $category->id }}"
                                   {{ in_array($category->id, old('enabled_categories', $eventUrl->enabled_categories ?? [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="category_{{ $category->id }}" class="ml-2 text-sm text-gray-700 flex items-center">
                                @if($category->color)
                                    <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $category->color }}"></span>
                                @endif
                                {{ $category->name }}
                                <span class="ml-2 text-xs text-gray-500">({{ $category->currency }} {{ number_format($category->price, 2) }})</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif
            @error('enabled_categories')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- URL Preview -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-2">URL Preview</label>
            <div class="font-mono text-sm text-gray-600">
                <span id="url-preview">/<span id="preview-type">{{ $eventUrl->type }}</span>/<span id="preview-slug">{{ $eventUrl->slug }}</span></span>
            </div>
            <p class="text-xs text-gray-500 mt-2">URL format changes based on selected type.</p>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.event-urls.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                Update URL
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slugInput = document.getElementById('slug');
    const typeSelect = document.getElementById('type');
    const previewSlug = document.getElementById('preview-slug');
    const previewType = document.getElementById('preview-type');

    // Update slug preview
    slugInput.addEventListener('input', function() {
        previewSlug.textContent = this.value || 'your-slug';
    });

    const formatOptions = document.getElementById('registration-format-options');
    const formatSelect = document.getElementById('registration_format');
    const formatHelp = document.getElementById('registration-format-help');
    const formatDescriptions = {
        multi_step: @json(\App\Registration\Enums\RegistrationFormat::MultiStep->description()),
        single_page: @json(\App\Registration\Enums\RegistrationFormat::SinglePage->description()),
    };

    function syncTypeDependentFields() {
        const type = typeSelect.value || 'online';
        document.getElementById('badge-options').style.display = type === 'badge' ? 'block' : 'none';
        formatOptions.style.display = type === 'online' ? 'block' : 'none';
        previewType.textContent = type;
        if (formatHelp && formatSelect) {
            formatHelp.textContent = formatDescriptions[formatSelect.value] || '';
        }
    }

    typeSelect.addEventListener('change', syncTypeDependentFields);
    formatSelect?.addEventListener('change', syncTypeDependentFields);
    syncTypeDependentFields();
});
</script>
@endsection
