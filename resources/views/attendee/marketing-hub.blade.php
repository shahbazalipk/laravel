@extends('attendee.layout')

@section('title', 'Marketing Hub')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Marketing Hub</h1>
    <p class="text-gray-600">Download and share event promotional materials</p>
</div>

<!-- Featured Assets -->
@if($featured->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Featured Materials</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($featured as $asset)
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg overflow-hidden">
                    @if($asset->image_path)
                        <img src="{{ storage_public_url($asset->image_path) }}" 
                             alt="{{ $asset->title }}"
                             class="w-full h-48 object-cover">
                    @endif
                    <div class="p-4 text-white">
                        <h3 class="font-bold text-lg mb-2">{{ $asset->title }}</h3>
                        <p class="text-sm opacity-90 mb-3">{{ Str::limit($asset->description, 80) }}</p>
                        <button onclick="viewAsset({{ $asset->id }})" 
                                class="w-full px-4 py-2 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-gray-100 transition">
                            View & Download
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Filter Tabs -->
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <div class="flex gap-2 overflow-x-auto">
        <a href="{{ route('attendee.marketing-hub') }}" 
           class="px-4 py-2 text-sm {{ !request('type') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition whitespace-nowrap">
            All Materials
        </a>
        <a href="{{ route('attendee.marketing-hub', ['type' => 'poster']) }}" 
           class="px-4 py-2 text-sm {{ request('type') == 'poster' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition whitespace-nowrap">
            Posters
        </a>
        <a href="{{ route('attendee.marketing-hub', ['type' => 'social_post']) }}" 
           class="px-4 py-2 text-sm {{ request('type') == 'social_post' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition whitespace-nowrap">
            Social Posts
        </a>
        <a href="{{ route('attendee.marketing-hub', ['type' => 'hashtag']) }}" 
           class="px-4 py-2 text-sm {{ request('type') == 'hashtag' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition whitespace-nowrap">
            Hashtags
        </a>
        <a href="{{ route('attendee.marketing-hub', ['type' => 'caption']) }}" 
           class="px-4 py-2 text-sm {{ request('type') == 'caption' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition whitespace-nowrap">
            Captions
        </a>
    </div>
</div>

<!-- Assets Grid -->
@if($assets->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Materials Available</h3>
        <p class="text-gray-600">Check back later for promotional materials</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($assets as $asset)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition overflow-hidden">
                <!-- Image -->
                @if($asset->image_path)
                    <div class="aspect-video bg-gray-100 overflow-hidden">
                        <img src="{{ storage_public_url($asset->image_path) }}" 
                             alt="{{ $asset->title }}"
                             class="w-full h-full object-cover hover:scale-105 transition duration-300 cursor-pointer"
                             onclick="viewAsset({{ $asset->id }})">
                    </div>
                @else
                    <div class="aspect-video bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
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
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded">
                                {{ ucfirst(str_replace('_', ' ', $asset->type)) }}
                            </span>
                        </div>
                    </div>

                    @if($asset->description)
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $asset->description }}</p>
                    @endif

                    <!-- Hashtags Preview -->
                    @if($asset->hashtags && count($asset->hashtags) > 0)
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach(array_slice($asset->hashtags, 0, 3) as $hashtag)
                                <span class="text-xs text-indigo-600">#{{ $hashtag }}</span>
                            @endforeach
                            @if(count($asset->hashtags) > 3)
                                <span class="text-xs text-gray-500">+{{ count($asset->hashtags) - 3 }}</span>
                            @endif
                        </div>
                    @endif

                    <!-- Actions -->
                    <button onclick="viewAsset({{ $asset->id }})" 
                            class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                        View Details
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($assets->hasPages())
        <div class="mt-8">
            {{ $assets->links() }}
        </div>
    @endif
@endif

<!-- Asset Detail Modal -->
<div id="asset-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div id="modal-content"></div>
    </div>
</div>

<script>
const storageUrlTemplate = @json(\Illuminate\Support\Facades\Storage::disk('public')->url('__PATH__'));
function storagePublicUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return storageUrlTemplate.replace('__PATH__', path);
}

function viewAsset(assetId) {
    const asset = @json($assets->items()).find(a => a.id === assetId) || @json($featured).find(a => a.id === assetId);
    if (!asset) return;
    
    const modal = document.getElementById('asset-modal');
    const content = document.getElementById('modal-content');
    
    let html = `
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-2xl font-bold text-gray-900">${asset.title}</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            ${asset.image_path ? `
                <img src="${storagePublicUrl(asset.image_path)}" 
                     alt="${asset.title}"
                     class="w-full rounded-lg mb-4">
            ` : ''}
            
            ${asset.description ? `<p class="text-gray-700 mb-4">${asset.description}</p>` : ''}
            
            ${asset.caption_text ? `
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-semibold text-gray-900">Caption Text</h3>
                        <button onclick="copyText('${asset.caption_text.replace(/'/g, "\\'")}', 'caption')" 
                                class="text-sm text-indigo-600 hover:text-indigo-700">
                            Copy
                        </button>
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">${asset.caption_text}</p>
                </div>
            ` : ''}
            
            ${asset.hashtags && asset.hashtags.length > 0 ? `
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-semibold text-gray-900">Hashtags</h3>
                        <button onclick="copyHashtags(${JSON.stringify(asset.hashtags)})" 
                                class="text-sm text-indigo-600 hover:text-indigo-700">
                            Copy All
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        ${asset.hashtags.map(tag => `<span class="text-indigo-600">#${tag}</span>`).join(' ')}
                    </div>
                </div>
            ` : ''}
            
            ${asset.social_platforms && asset.social_platforms.length > 0 ? `
                <div class="mb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Optimized For:</h3>
                    <div class="flex flex-wrap gap-2">
                        ${asset.social_platforms.map(platform => `
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">${platform}</span>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
            
            <div class="flex gap-3 pt-4 border-t">
                ${asset.image_path ? `
                    <a href="/attendee/marketing-hub/${asset.id}/download" 
                       class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-center">
                        Download
                    </a>
                ` : ''}
                <button onclick="shareAsset(${asset.id})" 
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg">
                    Share
                </button>
            </div>
        </div>
    `;
    
    content.innerHTML = html;
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('asset-modal').classList.add('hidden');
}

function copyText(text, type) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
}

function copyHashtags(hashtags) {
    const text = hashtags.map(tag => '#' + tag).join(' ');
    navigator.clipboard.writeText(text).then(() => {
        alert('Hashtags copied to clipboard!');
    });
}

function shareAsset(assetId) {
    fetch(`/attendee/marketing-hub/${assetId}/share`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });
    alert('Thanks for sharing!');
}

// Close modal on outside click
document.getElementById('asset-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
