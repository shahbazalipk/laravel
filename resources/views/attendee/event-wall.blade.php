@extends('attendee.layout')

@section('title', 'Event Wall')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Event Wall</h1>
        <p class="text-gray-600">Share your experience, connect with other attendees</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Feed -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Create Post Card -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold">
                        {{ substr($registration->first_name, 0, 1) }}{{ substr($registration->last_name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <textarea id="post-content" 
                                  rows="3" 
                                  placeholder="What's on your mind, {{ $registration->first_name }}? Use #hashtags and @mentions"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                    </div>
                </div>

                <!-- Image Preview -->
                <div id="image-preview" class="hidden mb-4 grid grid-cols-3 gap-2"></div>

                <!-- Post Actions -->
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex gap-2">
                        <label class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg cursor-pointer transition">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium">Photo</span>
                            <input type="file" id="post-images" accept="image/*" multiple class="hidden" onchange="previewImages(this)">
                        </label>

                        <button onclick="insertEmoji()" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-.464 5.535a1 1 0 10-1.415-1.414 3 3 0 01-4.242 0 1 1 0 00-1.415 1.414 5 5 0 007.072 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium">Emoji</span>
                        </button>
                    </div>

                    <button onclick="createPost()" 
                            id="post-btn"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                        Post
                    </button>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex gap-2 overflow-x-auto">
                    <a href="{{ route('attendee.event-wall') }}" 
                       class="px-4 py-2 text-sm {{ !request('type') && !request('hashtag') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition whitespace-nowrap">
                        All Posts
                    </a>
                    <a href="{{ route('attendee.event-wall', ['type' => 'image']) }}" 
                       class="px-4 py-2 text-sm {{ request('type') == 'image' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition whitespace-nowrap">
                        Photos
                    </a>
                    <a href="{{ route('attendee.event-wall', ['type' => 'link']) }}" 
                       class="px-4 py-2 text-sm {{ request('type') == 'link' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg transition whitespace-nowrap">
                        Links
                    </a>
                </div>
            </div>

            <!-- Posts Feed -->
            <div id="posts-container" class="space-y-6">
                @forelse($posts as $post)
                    @include('attendee.partials.wall-post', ['post' => $post])
                @empty
                    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Posts Yet</h3>
                        <p class="text-gray-600">Be the first to share something on the event wall!</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($posts->hasPages())
                <div class="mt-6">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Trending Hashtags -->
            @if($trendingHashtags->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Trending Hashtags</h3>
                    <div class="space-y-2">
                        @foreach($trendingHashtags as $hashtag => $count)
                            <a href="{{ route('attendee.event-wall', ['hashtag' => $hashtag]) }}" 
                               class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition">
                                <span class="text-indigo-600 font-medium">#{{ $hashtag }}</span>
                                <span class="text-xs text-gray-500">{{ $count }} posts</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Emojis -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Emojis</h3>
                <div class="grid grid-cols-5 gap-2">
                    @foreach(['😀', '😍', '🎉', '👏', '🔥', '💯', '✨', '🚀', '💪', '🙌'] as $emoji)
                        <button onclick="insertEmojiChar('{{ $emoji }}')" 
                                class="text-2xl p-2 hover:bg-gray-100 rounded-lg transition">
                            {{ $emoji }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Event Hashtags -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Event Hashtags</h3>
                <div class="flex flex-wrap gap-2">
                    <button onclick="insertHashtag('{{ str_replace(' ', '', $registration->event->name) }}')" 
                            class="px-3 py-1 bg-indigo-100 text-indigo-700 text-sm rounded-full hover:bg-indigo-200 transition">
                        #{{ str_replace(' ', '', $registration->event->name) }}
                    </button>
                    <button onclick="insertHashtag('EventHighlights')" 
                            class="px-3 py-1 bg-indigo-100 text-indigo-700 text-sm rounded-full hover:bg-indigo-200 transition">
                        #EventHighlights
                    </button>
                    <button onclick="insertHashtag('Networking')" 
                            class="px-3 py-1 bg-indigo-100 text-indigo-700 text-sm rounded-full hover:bg-indigo-200 transition">
                        #Networking
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedImages = [];

function previewImages(input) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    selectedImages = [];
    
    if (input.files && input.files.length > 0) {
        preview.classList.remove('hidden');
        
        Array.from(input.files).slice(0, 5).forEach((file, index) => {
            selectedImages.push(file);
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover rounded-lg">
                    <button onclick="removeImage(${index})" 
                            type="button"
                            class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 flex items-center justify-center">
                        ×
                    </button>
                `;
                preview.appendChild(div);
            };
            
            reader.readAsDataURL(file);
        });
    } else {
        preview.classList.add('hidden');
    }
}

function removeImage(index) {
    selectedImages.splice(index, 1);
    
    // Update file input
    const dataTransfer = new DataTransfer();
    selectedImages.forEach(file => dataTransfer.items.add(file));
    const input = document.getElementById('post-images');
    input.files = dataTransfer.files;
    
    // Re-render preview
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    if (selectedImages.length === 0) {
        preview.classList.add('hidden');
    } else {
        selectedImages.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover rounded-lg">
                    <button onclick="removeImage(${idx})" 
                            type="button"
                            class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 flex items-center justify-center">
                        ×
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

function insertEmojiChar(emoji) {
    const textarea = document.getElementById('post-content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + emoji + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
}

function insertEmoji() {
    const emojis = ['😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊', '😇', '🙂', '😍', '🥰', '😘', '😗', '😙', '😚', '🎉', '🎊', '🎈', '🎁', '🏆', '🥇', '🥈', '🥉', '👏', '🙌', '👍', '💪', '🔥', '💯', '✨', '⭐', '🌟', '💫', '🚀', '💡'];
    const emoji = emojis[Math.floor(Math.random() * emojis.length)];
    insertEmojiChar(emoji);
}

function insertHashtag(tag) {
    const textarea = document.getElementById('post-content');
    const text = textarea.value;
    textarea.value = text + (text ? ' ' : '') + '#' + tag + ' ';
    textarea.focus();
}

function createPost() {
    const content = document.getElementById('post-content').value.trim();
    const imagesInput = document.getElementById('post-images');
    const images = imagesInput.files;
    
    if (!content && images.length === 0) {
        alert('Please write something or add images');
        return;
    }
    
    const formData = new FormData();
    formData.append('content', content);
    formData.append('post_type', images.length > 0 ? 'image' : 'text');
    
    // Append actual files from the selectedImages array
    selectedImages.forEach((image, index) => {
        formData.append(`images[${index}]`, image);
    });
    
    const btn = document.getElementById('post-btn');
    btn.disabled = true;
    btn.textContent = 'Posting...';
    
    fetch('{{ route('attendee.event-wall.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Post response:', data);
        if (data.success) {
            // Clear form
            document.getElementById('post-content').value = '';
            imagesInput.value = '';
            selectedImages = [];
            document.getElementById('image-preview').innerHTML = '';
            document.getElementById('image-preview').classList.add('hidden');
            
            // Reload page to show new post
            window.location.reload();
        } else {
            alert('Failed to create post: ' + (data.message || 'Unknown error'));
            btn.disabled = false;
            btn.textContent = 'Post';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create post');
        btn.disabled = false;
        btn.textContent = 'Post';
    });
}
</script>
@endsection
