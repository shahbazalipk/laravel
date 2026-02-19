@extends('admin.layout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Marketing Assets</h1>
            <p class="text-gray-600 text-sm">Manage promotional materials for attendees</p>
        </div>
        <a href="{{ route('admin.marketing-assets.create') }}" 
           class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
            + Create Asset
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="flex border-b overflow-x-auto">
            <a href="{{ route('admin.marketing-assets.index') }}" 
               class="px-6 py-3 text-sm font-medium {{ !request('type') ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-800' }}">
                All ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.marketing-assets.index', ['type' => 'poster']) }}" 
               class="px-6 py-3 text-sm font-medium {{ request('type') == 'poster' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-800' }}">
                Posters ({{ $counts['poster'] }})
            </a>
            <a href="{{ route('admin.marketing-assets.index', ['type' => 'social_post']) }}" 
               class="px-6 py-3 text-sm font-medium {{ request('type') == 'social_post' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-800' }}">
                Social Posts ({{ $counts['social_post'] }})
            </a>
            <a href="{{ route('admin.marketing-assets.index', ['type' => 'hashtag']) }}" 
               class="px-6 py-3 text-sm font-medium {{ request('type') == 'hashtag' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-800' }}">
                Hashtags ({{ $counts['hashtag'] }})
            </a>
            <a href="{{ route('admin.marketing-assets.index', ['type' => 'caption']) }}" 
               class="px-6 py-3 text-sm font-medium {{ request('type') == 'caption' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-800' }}">
                Captions ({{ $counts['caption'] }})
            </a>
        </div>
    </div>

    <!-- Assets Grid -->
    @if($assets->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">No Marketing Assets</h3>
            <p class="text-gray-600 mb-4">Create your first marketing asset to get started</p>
            <a href="{{ route('admin.marketing-assets.create') }}" 
               class="inline-block px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                Create Asset
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($assets as $asset)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                    <!-- Image Preview -->
                    @if($asset->image_path)
                        <div class="aspect-video bg-gray-100 rounded-t-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $asset->image_path) }}" 
                                 alt="{{ $asset->title }}"
                                 class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="aspect-video bg-gradient-to-br from-blue-500 to-purple-600 rounded-t-lg flex items-center justify-center">
                            <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif

                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">{{ $asset->title }}</h3>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">
                                        {{ ucfirst(str_replace('_', ' ', $asset->type)) }}
                                    </span>
                                    @if($asset->is_featured)
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">
                                            Featured
                                        </span>
                                    @endif
                                    @if(!$asset->is_active)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($asset->description)
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $asset->description }}</p>
                        @endif

                        <!-- Stats -->
                        <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                {{ $asset->download_count }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                {{ $asset->share_count }}
                            </span>
                        </div>

                        <!-- Hashtags -->
                        @if($asset->hashtags && count($asset->hashtags) > 0)
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach(array_slice($asset->hashtags, 0, 3) as $hashtag)
                                    <span class="text-xs text-blue-600">#{{ $hashtag }}</span>
                                @endforeach
                                @if(count($asset->hashtags) > 3)
                                    <span class="text-xs text-gray-500">+{{ count($asset->hashtags) - 3 }}</span>
                                @endif
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a href="{{ route('admin.marketing-assets.edit', $asset) }}" 
                               class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded text-center">
                                Edit
                            </a>
                            <form action="{{ route('admin.marketing-assets.duplicate', $asset) }}" 
                                  method="POST" 
                                  class="flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded">
                                    Duplicate
                                </button>
                            </form>
                            <form action="{{ route('admin.marketing-assets.destroy', $asset) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Delete this asset?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $assets->links() }}
        </div>
    @endif
</div>
@endsection
