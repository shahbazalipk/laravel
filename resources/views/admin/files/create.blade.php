@extends('admin.layout')

@section('title', 'Upload File')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.files.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Upload File</h1>
            <p class="text-gray-600 mt-1">Upload a new file to your event</p>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.files.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- File Upload -->
        <div class="mb-6">
            <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                Files <span class="text-red-500">*</span>
            </label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 transition">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                        <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                            <span>Upload files</span>
                            <input id="files" name="files[]" type="file" class="sr-only" multiple required onchange="displayFileNames(this)">
                        </label>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">
                        Any files up to 50MB each (multiple files allowed)
                    </p>
                    <div id="file-list" class="mt-3 text-sm text-indigo-600 font-medium space-y-1"></div>
                </div>
            </div>
            @error('files')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('files.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Name -->
        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Display Name Prefix
            </label>
            <input type="text" 
                   name="name" 
                   id="name" 
                   value="{{ old('name') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                   placeholder="Leave empty to use original filenames">
            <p class="mt-1 text-xs text-gray-500">Optional: Prefix for all uploaded files (e.g., "Event-" will create "Event-file1.pdf", "Event-file2.jpg")</p>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Category -->
        <div class="mb-6">
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                Category
            </label>
            <select name="category" 
                    id="category"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('category') border-red-500 @enderror">
                <option value="">Auto-detect from file type</option>
                <option value="document" {{ old('category') === 'document' ? 'selected' : '' }}>Document</option>
                <option value="image" {{ old('category') === 'image' ? 'selected' : '' }}>Image</option>
                <option value="video" {{ old('category') === 'video' ? 'selected' : '' }}>Video</option>
                <option value="audio" {{ old('category') === 'audio' ? 'selected' : '' }}>Audio</option>
                <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('category')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>
            <textarea name="description" 
                      id="description" 
                      rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
                      placeholder="Optional description of the file">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Is Public -->
        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="is_public" 
                       value="1"
                       {{ old('is_public') ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Make this file publicly accessible</span>
            </label>
            <p class="mt-1 ml-6 text-xs text-gray-500">Public files can be accessed without authentication</p>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.files.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Upload Files
            </button>
        </div>
    </form>
</div>

<script>
function displayFileNames(input) {
    const fileListDisplay = document.getElementById('file-list');
    fileListDisplay.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        const fileCount = input.files.length;
        const summary = document.createElement('div');
        summary.className = 'font-semibold text-indigo-700';
        summary.textContent = `${fileCount} file${fileCount > 1 ? 's' : ''} selected`;
        fileListDisplay.appendChild(summary);
        
        Array.from(input.files).forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'text-xs text-gray-600';
            fileItem.textContent = `${index + 1}. ${file.name} (${formatFileSize(file.size)})`;
            fileListDisplay.appendChild(fileItem);
        });
    }
}

function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else {
        return bytes + ' bytes';
    }
}
</script>
@endsection
