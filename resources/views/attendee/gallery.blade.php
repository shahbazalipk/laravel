@extends('attendee.layout')

@section('title', 'Event Gallery')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Event Gallery</h1>
    <p class="text-gray-600">Browse and download photos from the event</p>
</div>

<!-- Album Cover (if album selected and has cover) -->
@if($currentAlbum && $currentAlbum->cover_photo && $currentAlbum->show_cover_at_top)
<div class="mb-6 rounded-xl overflow-hidden shadow-lg">
    <img src="{{ storage_public_url($currentAlbum->cover_photo) }}" 
         alt="{{ $currentAlbum->name }}" 
         class="w-full h-64 object-cover">
    <div class="bg-white p-4">
        <h2 class="text-xl font-bold text-gray-900">{{ $currentAlbum->name }}</h2>
        @if($currentAlbum->description)
            <p class="text-gray-600 mt-1">{{ $currentAlbum->description }}</p>
        @endif
    </div>
</div>
@endif

<!-- Album Filter -->
@if($albums->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Browse by Album</h3>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('attendee.gallery') }}" 
           class="px-4 py-2 text-sm {{ !$selectedAlbum ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition">
            All Albums
        </a>
        @foreach($albums as $album)
            <a href="{{ route('attendee.gallery', ['album' => $album->id]) }}" 
               class="px-4 py-2 text-sm {{ $selectedAlbum == $album->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition">
                {{ $album->name }} ({{ $album->photos_count }})
            </a>
        @endforeach
    </div>
</div>
@endif

<!-- Filter Options -->
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <div class="flex flex-wrap items-center gap-4">
        <a href="{{ route('attendee.gallery', array_filter(['album' => $selectedAlbum])) }}" 
           class="px-4 py-2 text-sm {{ !request('featured') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition">
            All Photos ({{ $photos->total() }})
        </a>
        @if($featuredCount > 0)
            <a href="{{ route('attendee.gallery', array_filter(['album' => $selectedAlbum, 'featured' => 1])) }}" 
               class="px-4 py-2 text-sm {{ request('featured') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition">
                Featured ({{ $featuredCount }})
            </a>
        @endif
        
        <div class="ml-auto flex gap-2">
            @if($currentAlbum)
                @if($currentAlbum->enable_download)
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                        Downloads Enabled
                    </span>
                @else
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">
                        Downloads Disabled
                    </span>
                @endif
                
                @if($currentAlbum->enable_form && $currentAlbum->galleryForm)
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                        Form: {{ $currentAlbum->galleryForm->name }}
                    </span>
                @endif
            @endif
        </div>
    </div>
</div>

@if($photos->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Photos Yet</h3>
        <p class="text-gray-600">Check back later for event photos</p>
    </div>
@else
    <!-- Photo Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($photos as $photo)
            <div class="group relative aspect-square bg-gray-100 rounded-lg overflow-hidden hover:shadow-lg transition">
                <!-- Photo with applied edits -->
                @php
                    $filterStyle = '';
                    if ($photo->edit_settings) {
                        $settings = is_string($photo->edit_settings) ? json_decode($photo->edit_settings, true) : $photo->edit_settings;
                        $filters = [];
                        
                        if (isset($settings['exposure']) && $settings['exposure'] != 0) {
                            $filters[] = 'brightness(' . (100 + $settings['exposure']) . '%)';
                        }
                        if (isset($settings['contrast']) && $settings['contrast'] != 0) {
                            $filters[] = 'contrast(' . (100 + $settings['contrast']) . '%)';
                        }
                        if (isset($settings['saturation']) && $settings['saturation'] != 0) {
                            $filters[] = 'saturate(' . (100 + $settings['saturation']) . '%)';
                        }
                        if (isset($settings['vibrance']) && $settings['vibrance'] != 0) {
                            $filters[] = 'saturate(' . (100 + $settings['vibrance'] * 0.5) . '%)';
                        }
                        if (isset($settings['temperature']) && $settings['temperature'] != 0) {
                            $filters[] = 'hue-rotate(' . ($settings['temperature'] * 0.5) . 'deg)';
                        }
                        if (isset($settings['sharpness']) && $settings['sharpness'] != 0) {
                            $filters[] = 'contrast(' . (100 + $settings['sharpness'] * 0.3) . '%)';
                        }
                        
                        if (!empty($filters)) {
                            $filterStyle = 'filter: ' . implode(' ', $filters) . ';';
                        }
                    }
                @endphp
                
                <img src="{{ storage_public_url($photo->image_path) }}" 
                     alt="{{ $photo->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                     style="{{ $filterStyle }}">
                
                <!-- Watermark (if enabled) -->
                @if($photo->apply_watermark)
                    @php
                        $watermarkPos = match($photo->watermark_position) {
                            'top-left' => 'top-2 left-2',
                            'top-right' => 'top-2 right-2',
                            'bottom-left' => 'bottom-2 left-2',
                            'bottom-right' => 'bottom-2 right-2',
                            'center' => 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2',
                            default => 'bottom-2 right-2',
                        };
                    @endphp
                    <div class="absolute {{ $watermarkPos }} text-white font-bold text-xs px-2 py-1 bg-black bg-opacity-30 rounded"
                         style="opacity: {{ ($photo->watermark_opacity ?? 50) / 100 }}">
                        {{ $registration->event->name }}
                    </div>
                @endif

                <!-- Frame (if applied) -->
                @if($photo->frame_style)
                    @if($photo->frame_style == 'classic')
                        <div class="absolute inset-0 border-8 border-white pointer-events-none"></div>
                        <div class="absolute bottom-0 left-0 right-0 bg-white text-center py-2 text-xs font-semibold text-gray-800 pointer-events-none">
                            {{ $registration->event->name }}
                        </div>
                    @elseif($photo->frame_style == 'modern')
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none"></div>
                        <div class="absolute bottom-4 left-4 text-white font-bold text-sm pointer-events-none">
                            {{ $registration->event->name }}
                        </div>
                    @elseif($photo->frame_style == 'elegant')
                        <div class="absolute inset-0 border-2 border-yellow-500 pointer-events-none"></div>
                        <div class="absolute top-2 right-2 text-yellow-500 text-xs font-serif pointer-events-none">
                            {{ $registration->event->name }}
                        </div>
                    @elseif($photo->frame_style == 'bold')
                        <div class="absolute inset-0 border-[12px] border-indigo-600 pointer-events-none"></div>
                        <div class="absolute top-3 left-3 bg-indigo-600 text-white px-2 py-1 text-xs font-bold pointer-events-none">
                            {{ $registration->event->name }}
                        </div>
                    @elseif($photo->frame_style == 'minimal')
                        <div class="absolute top-2 right-2 w-8 h-8 border-t-2 border-r-2 border-gray-800 pointer-events-none"></div>
                        <div class="absolute bottom-2 left-2 w-8 h-8 border-b-2 border-l-2 border-gray-800 pointer-events-none"></div>
                    @endif
                @endif
                
                <!-- Hover Overlay with Actions -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition">
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                        <h3 class="text-white font-semibold text-sm truncate mb-2">{{ $photo->title }}</h3>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('attendee.gallery.photo', $photo) }}" 
                               class="flex-1 px-3 py-2 bg-white/90 hover:bg-white text-gray-900 text-xs font-semibold rounded-lg text-center transition">
                                View
                            </a>
                            
                            @if($photo->album && $photo->album->enable_download)
                                <button onclick="downloadPhoto({{ $photo->id }}, {{ $photo->album->id }})" 
                                        class="flex-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                                    Download
                                </button>
                            @elseif(!$photo->album && $currentAlbum && $currentAlbum->enable_download)
                                <button onclick="downloadPhoto({{ $photo->id }}, {{ $currentAlbum->id }})" 
                                        class="flex-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                                    Download
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Featured Badge -->
                @if($photo->is_featured)
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-1 bg-yellow-500 text-white text-xs font-semibold rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </span>
                    </div>
                @endif
                
                <!-- Edit Indicators -->
                <div class="absolute top-2 left-2 flex flex-col gap-1">
                    @if($photo->edit_settings)
                        <span class="px-2 py-1 bg-purple-500 text-white text-xs font-semibold rounded-full">
                            Edited
                        </span>
                    @endif
                    @if($photo->apply_watermark)
                        <span class="px-2 py-1 bg-blue-500 text-white text-xs font-semibold rounded-full">
                            Watermark
                        </span>
                    @endif
                    @if($photo->frame_style)
                        <span class="px-2 py-1 bg-indigo-500 text-white text-xs font-semibold rounded-full">
                            Frame
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $photos->appends(request()->query())->links() }}
    </div>
@endif

<!-- Form Modal -->
@if($currentAlbum && $currentAlbum->enable_form && $currentAlbum->galleryForm)
<div id="form-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $currentAlbum->galleryForm->name }}</h3>
                    @if($currentAlbum->galleryForm->description)
                        <p class="text-sm text-gray-600 mt-1">{{ $currentAlbum->galleryForm->description }}</p>
                    @endif
                </div>
                @if($currentAlbum->form_requirement == 'optional')
                    <button onclick="closeFormModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>

            <form id="gallery-form" class="space-y-4">
                @foreach($currentAlbum->galleryForm->fields as $index => $field)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $field['label'] }}
                            @if($field['required'] ?? false)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        
                        @if($field['type'] == 'textarea')
                            <textarea name="field_{{ $index }}" 
                                      {{ ($field['required'] ?? false) ? 'required' : '' }}
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        @elseif($field['type'] == 'select')
                            <select name="field_{{ $index }}" 
                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select...</option>
                            </select>
                        @elseif($field['type'] == 'checkbox')
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="field_{{ $index }}"
                                       {{ ($field['required'] ?? false) ? 'required' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">I agree</span>
                            </label>
                        @else
                            <input type="{{ $field['type'] }}" 
                                   name="field_{{ $index }}" 
                                   {{ ($field['required'] ?? false) ? 'required' : '' }}
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @endif
                    </div>
                @endforeach

                <div class="flex gap-3 pt-4">
                    @if($currentAlbum->form_requirement == 'optional')
                        <button type="button" 
                                onclick="closeFormModal()" 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Skip
                        </button>
                    @endif
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                        Submit & Download
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif



<script>
let currentPhotoId = null;
let currentAlbumId = null;

@if($currentAlbum && $currentAlbum->enable_form && $currentAlbum->galleryForm)
    // Handle form display based on trigger timing
    @if($currentAlbum->form_trigger == 'on_open')
        // Show form immediately when page loads
        window.addEventListener('DOMContentLoaded', function() {
            document.getElementById('form-modal').classList.remove('hidden');
        });
    @elseif($currentAlbum->form_trigger == 'delayed')
        // Show form after delay
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('form-modal').classList.remove('hidden');
            }, {{ $currentAlbum->form_delay_seconds ?? 5 }} * 1000);
        });
    @endif
@endif

function downloadPhoto(photoId, albumId) {
    currentPhotoId = photoId;
    currentAlbumId = albumId;
    
    @if($currentAlbum && $currentAlbum->enable_form && $currentAlbum->galleryForm && $currentAlbum->form_trigger == 'on_download')
        // Show form modal only on download
        document.getElementById('form-modal').classList.remove('hidden');
    @else
        // Direct download (no form or form already shown)
        proceedWithDownload(photoId, null);
    @endif
}

function closeFormModal() {
    document.getElementById('form-modal').classList.add('hidden');
    currentPhotoId = null;
    currentAlbumId = null;
}

@if($currentAlbum && $currentAlbum->enable_form && $currentAlbum->galleryForm)
document.getElementById('gallery-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    if (currentPhotoId) {
        // Download specific photo
        proceedWithDownload(currentPhotoId, data);
    } else {
        // Just close modal (form was shown on open/delayed)
        closeFormModal();
    }
});
@endif

function proceedWithDownload(photoId, formData) {
    fetch(`/attendee/gallery/${photoId}/download`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ form_data: formData })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal if open
            const modal = document.getElementById('form-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
            
            // Trigger download
            window.location.href = data.download_url;
            
            // Reset current photo
            currentPhotoId = null;
            currentAlbumId = null;
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to download photo');
    });
}
</script>
@endsection
