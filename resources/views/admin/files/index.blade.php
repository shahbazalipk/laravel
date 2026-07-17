@extends('admin.layout')

@section('title', 'File Manager')

@section('content')
<!-- Header Section -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">File Manager</h1>
        <p class="text-gray-600 mt-1">Upload and manage files for your event</p>
    </div>
    <a href="{{ route('admin.files.create') }}" 
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition text-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Upload Files
    </a>
</div>

<!-- Category Filter -->
<div class="mb-6 flex space-x-2">
    <a href="{{ route('admin.files.index') }}" 
       class="px-4 py-2 rounded-lg transition {{ !request('category') ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
        All Files
    </a>
    <a href="{{ route('admin.files.index', ['category' => 'document']) }}" 
       class="px-4 py-2 rounded-lg transition {{ request('category') === 'document' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
        Documents
    </a>
    <a href="{{ route('admin.files.index', ['category' => 'image']) }}" 
       class="px-4 py-2 rounded-lg transition {{ request('category') === 'image' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
        Images
    </a>
    <a href="{{ route('admin.files.index', ['category' => 'video']) }}" 
       class="px-4 py-2 rounded-lg transition {{ request('category') === 'video' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
        Videos
    </a>
    <a href="{{ route('admin.files.index', ['category' => 'audio']) }}" 
       class="px-4 py-2 rounded-lg transition {{ request('category') === 'audio' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
        Audio
    </a>
    <a href="{{ route('admin.files.index', ['category' => 'other']) }}" 
       class="px-4 py-2 rounded-lg transition {{ request('category') === 'other' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
        Other
    </a>
</div>

@if($files->isEmpty())
    <!-- Empty State -->
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Files Found</h3>
        <p class="text-gray-600 mb-4">Get started by uploading your first file.</p>
        <a href="{{ route('admin.files.create') }}" 
           class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition text-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Upload First File
        </a>
    </div>
@else
    <!-- Files Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($files as $file)
        <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition">
            <!-- File Icon -->
            <div class="flex items-center mb-4">
                @if($file->category === 'image')
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @elseif($file->category === 'video')
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @elseif($file->category === 'audio')
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                        </svg>
                    </div>
                @elseif($file->category === 'document')
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @else
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                @endif
                <div class="ml-3 flex-1">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                        {{ strtoupper($file->file_extension) }}
                    </span>
                </div>
            </div>

            <!-- File Info -->
            <h3 class="text-lg font-semibold text-gray-800 mb-2 truncate" title="{{ $file->name }}">
                {{ $file->name }}
            </h3>
            
            @if($file->description)
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $file->description }}</p>
            @endif

            <div class="text-xs text-gray-500 space-y-1 mb-4">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                    {{ $file->file_size_formatted }}
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ $file->created_at->format('M d, Y') }}
                </div>
                @if($file->is_public)
                <div class="flex items-center text-green-600">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Public
                </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex flex-col space-y-1.5">
                <div class="flex space-x-1.5">
                    <a href="{{ $file->download_url }}" 
                       class="flex-1 text-center px-2 py-1.5 bg-indigo-600 text-white rounded text-xs hover:bg-indigo-700 transition">
                        Download
                    </a>
                    <button onclick="copyFileUrl('{{ storage_public_url($file->file_path) }}', this)" 
                            class="flex-1 text-center px-2 py-1.5 bg-green-600 text-white rounded text-xs hover:bg-green-700 transition">
                        Copy URL
                    </button>
                </div>
                <div class="flex space-x-1.5">
                    <a href="{{ route('admin.files.edit', $file->hash) }}" 
                       class="flex-1 px-2 py-1.5 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition text-center text-xs">
                        Edit
                    </a>
                    <form action="{{ route('admin.files.destroy', $file->hash) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this file?');"
                          class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-2 py-1.5 border border-red-300 text-red-600 rounded hover:bg-red-50 transition text-xs">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Total Count -->
    <div class="mt-6 text-sm text-gray-600">
        Total: {{ $files->count() }} file{{ $files->count() !== 1 ? 's' : '' }}
    </div>
@endif

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 flex items-center space-x-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    <span id="toast-message">URL copied to clipboard!</span>
</div>

<script>
function copyFileUrl(url, button) {
    // Create temporary input element
    const tempInput = document.createElement('input');
    tempInput.value = url;
    document.body.appendChild(tempInput);
    
    // Select and copy
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        showToast('URL copied to clipboard!');
        
        // Visual feedback on button
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.remove('bg-green-600', 'hover:bg-green-700');
        button.classList.add('bg-gray-600');
        
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('bg-gray-600');
            button.classList.add('bg-green-600', 'hover:bg-green-700');
        }, 2000);
    } catch (err) {
        showToast('Failed to copy URL', 'error');
    }
    
    // Remove temporary input
    document.body.removeChild(tempInput);
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    
    toastMessage.textContent = message;
    
    if (type === 'error') {
        toast.classList.remove('bg-green-600');
        toast.classList.add('bg-red-600');
    } else {
        toast.classList.remove('bg-red-600');
        toast.classList.add('bg-green-600');
    }
    
    // Show toast
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
    
    // Hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3000);
}
</script>
@endsection
