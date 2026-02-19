@extends('admin.layout')

@section('title', 'Batch Photo Editor')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Batch Photo Editor</h1>
        <p class="text-gray-600 mt-1">Edit multiple photos at once</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.gallery.index') }}" 
           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            ← Back to Gallery
        </a>
        <button onclick="applyEdits()" 
                id="apply-btn"
                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition disabled:opacity-50"
                disabled>
            Apply to Selected (<span id="selected-count">0</span>)
        </button>
    </div>
</div>

<!-- Album Filter -->
@if($albums->isNotEmpty())
<div class="mb-6 bg-white rounded-lg shadow-sm p-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Album</label>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.gallery.batch-editor') }}" 
           class="px-4 py-2 rounded-lg {{ !$selectedAlbum ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition">
            All Photos
        </a>
        @foreach($albums as $album)
            <a href="{{ route('admin.gallery.batch-editor', ['album' => $album->id]) }}" 
               class="px-4 py-2 rounded-lg {{ $selectedAlbum == $album->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition">
                {{ $album->name }} ({{ $album->photos_count }})
            </a>
        @endforeach
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Editor Controls -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Photo Editor</h2>
            
            <!-- Watermark Section -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h3 class="text-md font-semibold text-gray-900 mb-4">Watermark</h3>
                
                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               id="apply_watermark"
                               onchange="toggleWatermark(this)"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Apply Event Logo Watermark</span>
                    </label>
                </div>

                <div id="watermark-settings" class="space-y-3 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                        <select id="watermark_position" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                onchange="applyPreview()">
                            <option value="top-left">Top Left</option>
                            <option value="top-right">Top Right</option>
                            <option value="bottom-left">Bottom Left</option>
                            <option value="bottom-right" selected>Bottom Right</option>
                            <option value="center">Center</option>
                        </select>
                    </div>

                    <div>
                        <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                            <span>Opacity</span>
                            <span id="watermark-opacity-value" class="text-indigo-600">50%</span>
                        </label>
                        <input type="range" 
                               id="watermark_opacity" 
                               min="0" 
                               max="100" 
                               value="50" 
                               class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                               oninput="updateWatermarkOpacity(this.value)">
                    </div>
                </div>
            </div>

            <!-- Frame Section -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h3 class="text-md font-semibold text-gray-900 mb-4">Event Branding Frame</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Frame Style</label>
                    <select id="frame_style" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                            onchange="applyPreview()">
                        <option value="">None</option>
                        <option value="classic">Classic - White border with event name</option>
                        <option value="modern">Modern - Gradient overlay with logo</option>
                        <option value="elegant">Elegant - Thin gold border</option>
                        <option value="bold">Bold - Thick branded border</option>
                        <option value="minimal">Minimal - Subtle corner branding</option>
                    </select>
                </div>

                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Frames use your event logo and colors from branding settings
                    </p>
                </div>
            </div>

            <!-- Color & Exposure Adjustments -->
            <div class="mb-6">
                <h3 class="text-md font-semibold text-gray-900 mb-4">Color & Exposure Adjustments</h3>
                
                <div class="space-y-4">
                <!-- Temperature -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Temperature</span>
                        <span id="temperature-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="temperature" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gradient-to-r from-blue-500 via-gray-300 to-orange-500 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('temperature', this.value)">
                </div>

                <!-- Tint -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Tint</span>
                        <span id="tint-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="tint" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gradient-to-r from-green-500 via-gray-300 to-pink-500 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('tint', this.value)">
                </div>

                <!-- Vibrance -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Vibrance</span>
                        <span id="vibrance-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="vibrance" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('vibrance', this.value)">
                </div>

                <!-- Saturation -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Saturation</span>
                        <span id="saturation-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="saturation" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('saturation', this.value)">
                </div>

                <!-- Exposure -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Exposure</span>
                        <span id="exposure-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="exposure" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('exposure', this.value)">
                </div>

                <!-- Contrast -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Contrast</span>
                        <span id="contrast-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="contrast" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('contrast', this.value)">
                </div>

                <!-- Highlights -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Highlights</span>
                        <span id="highlights-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="highlights" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('highlights', this.value)">
                </div>

                <!-- Shadows -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Shadows</span>
                        <span id="shadows-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="shadows" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('shadows', this.value)">
                </div>

                <!-- Whites -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Whites</span>
                        <span id="whites-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="whites" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('whites', this.value)">
                </div>

                <!-- Blacks -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Blacks</span>
                        <span id="blacks-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="blacks" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('blacks', this.value)">
                </div>

                <!-- Clarity -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Clarity</span>
                        <span id="clarity-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="clarity" 
                           min="-100" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('clarity', this.value)">
                </div>

                <!-- Sharpness -->
                <div>
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                        <span>Sharpness</span>
                        <span id="sharpness-value" class="text-indigo-600">0</span>
                    </label>
                    <input type="range" 
                           id="sharpness" 
                           min="0" 
                           max="100" 
                           value="0" 
                           class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
                           oninput="updateValue('sharpness', this.value)">
                </div>
                </div>
            </div>

            <!-- Reset Button -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <button onclick="resetAll()" 
                        class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    Reset All
                </button>
            </div>
        </div>
    </div>

    <!-- Photo Grid -->
    <div class="lg:col-span-3">
        @if($photos->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <p class="text-gray-500">No photos found in this album</p>
            </div>
        @else
            <div class="mb-4 flex items-center justify-between bg-white rounded-lg shadow-sm p-4">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" 
                           id="select-all" 
                           onchange="toggleSelectAll(this)"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Select All ({{ $photos->count() }} photos)</span>
                </label>
                <div class="text-sm text-gray-600">
                    Preview updates in real-time
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($photos as $photo)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden group relative photo-item" data-photo-id="{{ $photo->id }}">
                    <div class="aspect-square overflow-hidden bg-gray-100 relative">
                        <img src="{{ asset('storage/' . $photo->image_path) }}" 
                             alt="{{ $photo->title }}"
                             class="w-full h-full object-cover photo-preview"
                             id="photo-{{ $photo->id }}">
                        
                        <!-- Selection Checkbox -->
                        <div class="absolute top-2 left-2">
                            <input type="checkbox" 
                                   class="photo-checkbox w-5 h-5 rounded border-2 border-white shadow-lg text-indigo-600 focus:ring-indigo-500"
                                   value="{{ $photo->id }}"
                                   onchange="updateSelectedCount()">
                        </div>

                        <!-- Selected Overlay -->
                        <div class="selected-overlay absolute inset-0 bg-indigo-600 bg-opacity-20 border-4 border-indigo-600 hidden"></div>
                    </div>
                    
                    <div class="p-3">
                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $photo->title }}</h3>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
let currentSettings = {
    temperature: 0,
    tint: 0,
    vibrance: 0,
    saturation: 0,
    exposure: 0,
    contrast: 0,
    highlights: 0,
    shadows: 0,
    whites: 0,
    blacks: 0,
    clarity: 0,
    sharpness: 0
};

let watermarkSettings = {
    apply: false,
    position: 'bottom-right',
    opacity: 50
};

let frameStyle = '';

function updateValue(setting, value) {
    currentSettings[setting] = parseFloat(value);
    document.getElementById(setting + '-value').textContent = value;
    applyPreview();
}

function toggleWatermark(checkbox) {
    watermarkSettings.apply = checkbox.checked;
    const settings = document.getElementById('watermark-settings');
    if (checkbox.checked) {
        settings.classList.remove('hidden');
    } else {
        settings.classList.add('hidden');
    }
    applyPreview();
}

function updateWatermarkOpacity(value) {
    watermarkSettings.opacity = parseInt(value);
    document.getElementById('watermark-opacity-value').textContent = value + '%';
    applyPreview();
}

function applyPreview() {
    const checkboxes = document.querySelectorAll('.photo-checkbox:checked');
    watermarkSettings.position = document.getElementById('watermark_position').value;
    frameStyle = document.getElementById('frame_style').value;
    
    checkboxes.forEach(checkbox => {
        const photoId = checkbox.value;
        const photoItem = document.getElementById('photo-' + photoId).closest('.photo-item');
        const img = document.getElementById('photo-' + photoId);
        
        // Apply CSS filters
        img.style.filter = generateCSSFilter();
        
        // Remove existing overlays
        photoItem.querySelectorAll('.watermark-overlay, .frame-overlay').forEach(el => el.remove());
        
        // Apply watermark
        if (watermarkSettings.apply) {
            applyWatermarkPreview(photoItem, photoId);
        }
        
        // Apply frame
        if (frameStyle) {
            applyFramePreview(photoItem, photoId);
        }
    });
}

function applyWatermarkPreview(photoItem, photoId) {
    const container = photoItem.querySelector('.aspect-square');
    const watermark = document.createElement('div');
    watermark.className = 'watermark-overlay absolute text-white font-bold text-xs px-2 py-1 bg-black bg-opacity-30 rounded';
    watermark.style.opacity = watermarkSettings.opacity / 100;
    watermark.textContent = '{{ $photos->first()->event->name ?? "EVENT LOGO" }}';
    
    // Position watermark
    switch(watermarkSettings.position) {
        case 'top-left':
            watermark.style.top = '8px';
            watermark.style.left = '8px';
            break;
        case 'top-right':
            watermark.style.top = '8px';
            watermark.style.right = '8px';
            break;
        case 'bottom-left':
            watermark.style.bottom = '8px';
            watermark.style.left = '8px';
            break;
        case 'bottom-right':
            watermark.style.bottom = '8px';
            watermark.style.right = '8px';
            break;
        case 'center':
            watermark.style.top = '50%';
            watermark.style.left = '50%';
            watermark.style.transform = 'translate(-50%, -50%)';
            break;
    }
    
    container.appendChild(watermark);
}

function applyFramePreview(photoItem, photoId) {
    const container = photoItem.querySelector('.aspect-square');
    const frame = document.createElement('div');
    frame.className = 'frame-overlay absolute inset-0 pointer-events-none';
    
    switch(frameStyle) {
        case 'classic':
            frame.innerHTML = `
                <div class="absolute inset-0 border-8 border-white"></div>
                <div class="absolute bottom-0 left-0 right-0 bg-white text-center py-2 text-xs font-semibold text-gray-800">
                    {{ $photos->first()->event->name ?? "EVENT NAME" }}
                </div>
            `;
            break;
        case 'modern':
            frame.innerHTML = `
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="absolute bottom-4 left-4 text-white font-bold text-sm">
                    {{ $photos->first()->event->name ?? "EVENT NAME" }}
                </div>
            `;
            break;
        case 'elegant':
            frame.innerHTML = `
                <div class="absolute inset-0 border-2 border-yellow-500"></div>
                <div class="absolute top-2 right-2 text-yellow-500 text-xs font-serif">
                    {{ $photos->first()->event->name ?? "EVENT" }}
                </div>
            `;
            break;
        case 'bold':
            frame.innerHTML = `
                <div class="absolute inset-0 border-[12px] border-indigo-600"></div>
                <div class="absolute top-3 left-3 bg-indigo-600 text-white px-2 py-1 text-xs font-bold">
                    {{ $photos->first()->event->name ?? "EVENT" }}
                </div>
            `;
            break;
        case 'minimal':
            frame.innerHTML = `
                <div class="absolute top-2 right-2 w-8 h-8 border-t-2 border-r-2 border-gray-800"></div>
                <div class="absolute bottom-2 left-2 w-8 h-8 border-b-2 border-l-2 border-gray-800"></div>
            `;
            break;
    }
    
    container.appendChild(frame);
}

function generateCSSFilter() {
    let filters = [];
    
    // Brightness (exposure)
    if (currentSettings.exposure !== 0) {
        filters.push(`brightness(${100 + currentSettings.exposure}%)`);
    }
    
    // Contrast
    if (currentSettings.contrast !== 0) {
        filters.push(`contrast(${100 + currentSettings.contrast}%)`);
    }
    
    // Saturation
    if (currentSettings.saturation !== 0) {
        filters.push(`saturate(${100 + currentSettings.saturation}%)`);
    }
    
    // Hue (temperature approximation)
    if (currentSettings.temperature !== 0) {
        filters.push(`hue-rotate(${currentSettings.temperature * 0.5}deg)`);
    }
    
    return filters.join(' ');
}

function toggleSelectAll(checkbox) {
    const photoCheckboxes = document.querySelectorAll('.photo-checkbox');
    photoCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        const overlay = cb.closest('.photo-item').querySelector('.selected-overlay');
        if (checkbox.checked) {
            overlay.classList.remove('hidden');
        } else {
            overlay.classList.add('hidden');
        }
    });
    updateSelectedCount();
    applyPreview();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.photo-checkbox:checked');
    document.getElementById('selected-count').textContent = checked.length;
    document.getElementById('apply-btn').disabled = checked.length === 0;
    
    // Update overlays
    document.querySelectorAll('.photo-checkbox').forEach(cb => {
        const overlay = cb.closest('.photo-item').querySelector('.selected-overlay');
        if (cb.checked) {
            overlay.classList.remove('hidden');
        } else {
            overlay.classList.add('hidden');
        }
    });
    
    applyPreview();
}

function resetAll() {
    Object.keys(currentSettings).forEach(key => {
        currentSettings[key] = 0;
        document.getElementById(key).value = 0;
        document.getElementById(key + '-value').textContent = '0';
    });
    
    // Reset watermark
    document.getElementById('apply_watermark').checked = false;
    watermarkSettings.apply = false;
    document.getElementById('watermark-settings').classList.add('hidden');
    
    // Reset frame
    document.getElementById('frame_style').value = '';
    frameStyle = '';
    
    applyPreview();
}

function applyEdits() {
    const checked = document.querySelectorAll('.photo-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one photo');
        return;
    }
    
    const photoIds = Array.from(checked).map(cb => cb.value);
    const btn = document.getElementById('apply-btn');
    btn.disabled = true;
    btn.textContent = 'Applying...';
    
    fetch('{{ route('admin.gallery.batch-update') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            photo_ids: photoIds,
            settings: currentSettings,
            apply_watermark: watermarkSettings.apply,
            watermark_position: watermarkSettings.position,
            watermark_opacity: watermarkSettings.opacity,
            frame_style: frameStyle
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            btn.textContent = 'Apply to Selected (' + checked.length + ')';
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to apply edits');
        btn.disabled = false;
        btn.textContent = 'Apply to Selected (' + checked.length + ')';
    });
}
</script>
@endsection
