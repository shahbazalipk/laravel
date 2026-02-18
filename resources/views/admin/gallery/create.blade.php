@extends('admin.layout')

@section('title', 'Upload Photos')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.gallery.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Upload Photos</h1>
            <p class="text-gray-600 mt-1">Select multiple photos to upload</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
        @csrf
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Select Photos <span class="text-red-500">*</span>
            </label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-500 transition">
                <input type="file" 
                       name="photos[]" 
                       id="photos" 
                       multiple 
                       accept="image/*"
                       class="hidden"
                       onchange="previewImages(event)">
                <label for="photos" class="cursor-pointer">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-lg text-gray-700 mb-2">Click to select photos or drag and drop</p>
                    <p class="text-sm text-gray-500">JPEG, PNG, JPG, GIF up to 5MB each</p>
                </label>
            </div>
            @error('photos')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Preview Grid -->
        <div id="preview-container" class="hidden mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Selected Photos (<span id="photo-count">0</span>)</h3>
            <div id="preview-grid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4"></div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.gallery.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    id="upload-btn"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition disabled:opacity-50"
                    disabled>
                Upload Photos
            </button>
        </div>
    </form>
</div>

<script>
function previewImages(event) {
    const files = event.target.files;
    const previewContainer = document.getElementById('preview-container');
    const previewGrid = document.getElementById('preview-grid');
    const photoCount = document.getElementById('photo-count');
    const uploadBtn = document.getElementById('upload-btn');
    
    if (files.length === 0) {
        previewContainer.classList.add('hidden');
        uploadBtn.disabled = true;
        return;
    }
    
    previewContainer.classList.remove('hidden');
    previewGrid.innerHTML = '';
    photoCount.textContent = files.length;
    uploadBtn.disabled = false;
    
    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative aspect-square bg-gray-100 rounded-lg overflow-hidden';
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-full object-cover">
                <div class="absolute top-2 right-2">
                    <button type="button" 
                            onclick="removeImage(${index})"
                            class="p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            previewGrid.appendChild(div);
        };
        
        reader.readAsDataURL(file);
    });
}

function removeImage(index) {
    const input = document.getElementById('photos');
    const dt = new DataTransfer();
    const files = input.files;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            dt.items.add(files[i]);
        }
    }
    
    input.files = dt.files;
    previewImages({ target: input });
}

// Drag and drop
const dropZone = document.querySelector('[for="photos"]').parentElement;

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
    
    const input = document.getElementById('photos');
    input.files = e.dataTransfer.files;
    previewImages({ target: input });
});
</script>
@endsection
