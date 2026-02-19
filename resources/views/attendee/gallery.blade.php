@extends('attendee.layout')

@section('title', 'Event Gallery')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Event Gallery</h1>
    <p class="text-gray-600">Browse and share photos from the event</p>
</div>

<!-- Filter Options -->
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <div class="flex flex-wrap items-center gap-4">
        <a href="{{ route('attendee.gallery') }}" 
           class="px-4 py-2 text-sm {{ !request('featured') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition">
            All Photos ({{ $photos->total() }})
        </a>
        @if($featuredCount > 0)
            <a href="{{ route('attendee.gallery', ['featured' => 1]) }}" 
               class="px-4 py-2 text-sm {{ request('featured') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition">
                Featured ({{ $featuredCount }})
            </a>
        @endif
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
            <a href="{{ route('attendee.gallery.photo', $photo) }}" 
               class="group relative aspect-square bg-gray-100 rounded-lg overflow-hidden hover:shadow-lg transition">
                <img src="{{ asset('storage/' . $photo->image_path) }}" 
                     alt="{{ $photo->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                
                <!-- Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition">
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                        <h3 class="text-white font-semibold text-sm truncate">{{ $photo->title }}</h3>
                        @if($photo->description)
                            <p class="text-white/80 text-xs truncate">{{ $photo->description }}</p>
                        @endif
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
            </a>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $photos->appends(request()->query())->links() }}
    </div>
@endif
@endsection
