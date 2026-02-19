@extends('admin.layout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Edit Gallery Album</h1>
            <a href="{{ route('admin.gallery-albums.index') }}" class="text-gray-600 hover:text-gray-800">
                ← Back to Albums
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('admin.gallery-albums.update', $galleryAlbum) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                    
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Album Name *</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $galleryAlbum->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $galleryAlbum->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Display Order *</label>
                        <input type="number" 
                               name="order" 
                               id="order" 
                               value="{{ old('order', $galleryAlbum->order) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                        @error('order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Cover Photo Settings -->
                <div class="mb-6 border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Cover Photo</h2>
                    
                    @if($galleryAlbum->cover_photo)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $galleryAlbum->cover_photo) }}" 
                                 alt="Current cover" 
                                 class="w-48 h-32 object-cover rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Current cover photo</p>
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <label for="cover_photo" class="block text-sm font-medium text-gray-700 mb-2">Upload New Cover Photo</label>
                        <input type="file" 
                               name="cover_photo" 
                               id="cover_photo" 
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Leave empty to keep current cover photo</p>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="show_cover_at_top" 
                                   value="1"
                                   {{ old('show_cover_at_top', $galleryAlbum->show_cover_at_top) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Show cover photo at the top of gallery</span>
                        </label>
                    </div>
                </div>

                <!-- Photo Upload Settings -->
                <div class="mb-6 border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Photo Upload Settings</h2>
                    
                    <div class="mb-4">
                        <label for="default_photo_status" class="block text-sm font-medium text-gray-700 mb-2">Default Status for Uploaded Photos</label>
                        <select name="default_photo_status" 
                                id="default_photo_status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="published" {{ old('default_photo_status', $galleryAlbum->default_photo_status) == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="unpublished" {{ old('default_photo_status', $galleryAlbum->default_photo_status) == 'unpublished' ? 'selected' : '' }}>Unpublished</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="upload_size" class="block text-sm font-medium text-gray-700 mb-2">Upload Size</label>
                            <select name="upload_size" 
                                    id="upload_size"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="high_resolution" {{ old('upload_size', $galleryAlbum->upload_size) == 'high_resolution' ? 'selected' : '' }}>High Resolution</option>
                                <option value="web_size" {{ old('upload_size', $galleryAlbum->upload_size) == 'web_size' ? 'selected' : '' }}>Web Size</option>
                            </select>
                        </div>

                        <div>
                            <label for="download_size" class="block text-sm font-medium text-gray-700 mb-2">Download Size</label>
                            <select name="download_size" 
                                    id="download_size"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="high_resolution" {{ old('download_size', $galleryAlbum->download_size) == 'high_resolution' ? 'selected' : '' }}>High Resolution</option>
                                <option value="web_size" {{ old('download_size', $galleryAlbum->download_size) == 'web_size' ? 'selected' : '' }}>Web Size</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="preset" class="block text-sm font-medium text-gray-700 mb-2">Preset</label>
                        <input type="text" 
                               name="preset" 
                               id="preset" 
                               value="{{ old('preset', $galleryAlbum->preset) }}"
                               placeholder="e.g., Event Default"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Default preset for newly created albums</p>
                    </div>
                </div>

                <!-- Download & Form Settings -->
                <div class="mb-6 border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Download & Form Settings</h2>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="enable_download" 
                                   value="1"
                                   {{ old('enable_download', $galleryAlbum->enable_download) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Enable photo downloads</span>
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="enable_form" 
                                   value="1"
                                   {{ old('enable_form', $galleryAlbum->enable_form) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   onchange="toggleFormSettings(this)">
                            <span class="ml-2 text-sm text-gray-700">Enable form for online gallery</span>
                        </label>
                    </div>

                    <div id="form-settings" class="ml-6 space-y-4 {{ old('enable_form', $galleryAlbum->enable_form) ? '' : 'hidden' }}">
                        <div>
                            <label for="gallery_form_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Select Form
                                <a href="{{ route('admin.gallery-settings.forms') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-xs ml-2">
                                    (Manage Forms)
                                </a>
                            </label>
                            <select name="gallery_form_id" 
                                    id="gallery_form_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">No form (use default settings)</option>
                                @php
                                    $forms = \App\Models\GalleryForm::where('event_id', config('event.event_id'))
                                        ->where('org_id', config('event.org_id'))
                                        ->where('is_active', true)
                                        ->get();
                                @endphp
                                @foreach($forms as $form)
                                    <option value="{{ $form->id }}" {{ old('gallery_form_id', $galleryAlbum->gallery_form_id) == $form->id ? 'selected' : '' }}>
                                        {{ $form->name }}{{ $form->is_default ? ' (Default)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Select a form to collect visitor information. Leave empty to use default settings.
                            </p>
                        </div>

                        <div>
                            <label for="form_trigger" class="block text-sm font-medium text-gray-700 mb-2">When to display the form</label>
                            <select name="form_trigger" 
                                    id="form_trigger"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    onchange="toggleDelayField(this)">
                                <option value="on_open" {{ old('form_trigger', $galleryAlbum->form_trigger) == 'on_open' ? 'selected' : '' }}>When user opens the online gallery</option>
                                <option value="delayed" {{ old('form_trigger', $galleryAlbum->form_trigger) == 'delayed' ? 'selected' : '' }}>Seconds after user opens the gallery</option>
                                <option value="on_download" {{ old('form_trigger', $galleryAlbum->form_trigger) == 'on_download' ? 'selected' : '' }}>When user selects download button</option>
                            </select>
                        </div>

                        <div id="delay-field" class="{{ old('form_trigger', $galleryAlbum->form_trigger) == 'delayed' ? '' : 'hidden' }}">
                            <label for="form_delay_seconds" class="block text-sm font-medium text-gray-700 mb-2">Delay (seconds)</label>
                            <input type="number" 
                                   name="form_delay_seconds" 
                                   id="form_delay_seconds" 
                                   value="{{ old('form_delay_seconds', $galleryAlbum->form_delay_seconds) }}"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="form_requirement" class="block text-sm font-medium text-gray-700 mb-2">Form requirement</label>
                            <select name="form_requirement" 
                                    id="form_requirement"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="optional" {{ old('form_requirement', $galleryAlbum->form_requirement) == 'optional' ? 'selected' : '' }}>Optional - User can dismiss form</option>
                                <option value="mandatory" {{ old('form_requirement', $galleryAlbum->form_requirement) == 'mandatory' ? 'selected' : '' }}>Mandatory - User must submit to proceed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-6 border-t pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $galleryAlbum->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.gallery-albums.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                        Update Album
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFormSettings(checkbox) {
    const formSettings = document.getElementById('form-settings');
    if (checkbox.checked) {
        formSettings.classList.remove('hidden');
    } else {
        formSettings.classList.add('hidden');
    }
}

function toggleDelayField(select) {
    const delayField = document.getElementById('delay-field');
    if (select.value === 'delayed') {
        delayField.classList.remove('hidden');
    } else {
        delayField.classList.add('hidden');
    }
}
</script>
@endsection
