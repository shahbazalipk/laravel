@extends('admin.layout')

@section('title', 'Gallery')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Event Gallery</h1>
        <p class="text-gray-600 mt-1">Manage event photos</p>
    </div>
    <a href="{{ route('admin.gallery.create') }}" 
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Upload Photos
    </a>
</div>

@if($photos->isEmpty())
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Photos Yet</h3>
        <p class="text-gray-600 mb-4">Upload your first event photos to get started.</p>
        <a href="{{ route('admin.gallery.create') }}" 
           class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Upload Photos
        </a>
    </div>
@else
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($photos as $photo)
        <div class="bg-white rounded-lg shadow-sm overflow-hidden group relative">
            <div class="aspect-square overflow-hidden bg-gray-100">
                <img src="{{ asset('storage/' . $photo->image_path) }}" 
                     alt="{{ $photo->title }}"
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
            </div>
            
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                <div class="flex gap-2">
                    <a href="{{ route('admin.gallery.edit', $photo) }}" 
                       class="p-2 bg-white rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                    <form action="{{ route('admin.gallery.destroy', $photo) }}" method="POST" 
                          onsubmit="return confirm('Delete this photo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 bg-white rounded-lg hover:bg-red-50 transition">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            @if($photo->is_featured)
                <div class="absolute top-2 right-2">
                    <span class="px-2 py-1 bg-yellow-500 text-white text-xs rounded-full">Featured</span>
                </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $photos->links() }}
    </div>
@endif
@endsection
