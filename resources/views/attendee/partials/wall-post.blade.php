<div class="bg-white rounded-xl shadow-sm p-6" id="post-{{ $post->id }}">
    <!-- Post Header -->
    <div class="flex items-start justify-between mb-4">
        <div class="flex items-start gap-3">
            <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold">
                {{ substr($post->registration->first_name, 0, 1) }}{{ substr($post->registration->last_name, 0, 1) }}
            </div>
            <div>
                <h4 class="font-semibold text-gray-900">
                    {{ $post->registration->first_name }} {{ $post->registration->last_name }}
                </h4>
                <p class="text-sm text-gray-500">
                    {{ $post->registration->registrationCategory->name ?? 'Attendee' }} • 
                    {{ $post->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
        
        @if($post->registration_id === $registration->id)
            <button onclick="deletePost({{ $post->id }})" 
                    class="text-gray-400 hover:text-red-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        @endif
    </div>

    <!-- Post Content -->
    @if($post->content)
        <div class="mb-4 text-gray-800 whitespace-pre-wrap">
            {!! nl2br(e($post->content)) !!}
        </div>
    @endif

    <!-- Post Images -->
    @if($post->images && count($post->images) > 0)
        <div class="mb-4 grid {{ count($post->images) === 1 ? 'grid-cols-1' : 'grid-cols-2' }} gap-2">
            @foreach($post->images as $image)
                <img src="{{ storage_public_url($image) }}" 
                     alt="Post image" 
                     class="w-full rounded-lg {{ count($post->images) === 1 ? 'max-h-96' : 'aspect-square' }} object-cover cursor-pointer hover:opacity-90 transition"
                     onclick="openImageModal('{{ storage_public_url($image) }}')"
                     onerror="console.error('Failed to load image:', '{{ $image }}'); this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23ddd%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EImage not found%3C/text%3E%3C/svg%3E';">
            @endforeach
        </div>
    @endif

    <!-- Post Link -->
    @if($post->post_type === 'link' && $post->link_url)
        <a href="{{ $post->link_url }}" 
           target="_blank"
           class="block mb-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            <div class="flex items-center gap-2 text-indigo-600 hover:text-indigo-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                <span class="font-medium">{{ $post->link_title ?? $post->link_url }}</span>
            </div>
        </a>
    @endif

    <!-- Hashtags -->
    @if($post->hashtags && count($post->hashtags) > 0)
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach($post->hashtags as $hashtag)
                <a href="{{ route('attendee.event-wall', ['hashtag' => $hashtag]) }}" 
                   class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                    #{{ $hashtag }}
                </a>
            @endforeach
        </div>
    @endif

    <!-- Post Stats -->
    <div class="flex items-center gap-4 py-3 border-t border-b text-sm text-gray-600">
        <span>{{ $post->likes_count }} {{ $post->likes_count === 1 ? 'like' : 'likes' }}</span>
        <span>{{ $post->comments_count }} {{ $post->comments_count === 1 ? 'comment' : 'comments' }}</span>
    </div>

    <!-- Post Actions -->
    <div class="flex items-center gap-2 pt-3">
        <button onclick="toggleLike({{ $post->id }})" 
                id="like-btn-{{ $post->id }}"
                class="flex-1 flex items-center justify-center gap-2 py-2 {{ $post->isLikedBy($registration->id) ? 'text-indigo-600' : 'text-gray-600' }} hover:bg-gray-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="{{ $post->isLikedBy($registration->id) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
            </svg>
            <span class="font-medium">Like</span>
        </button>

        <button onclick="toggleComments({{ $post->id }})" 
                class="flex-1 flex items-center justify-center gap-2 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <span class="font-medium">Comment</span>
        </button>
    </div>

    <!-- Comments Section -->
    <div id="comments-{{ $post->id }}" class="hidden mt-4 pt-4 border-t space-y-4">
        <!-- Comment Form -->
        <div class="flex gap-3">
            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-semibold">
                {{ substr($registration->first_name, 0, 1) }}{{ substr($registration->last_name, 0, 1) }}
            </div>
            <div class="flex-1">
                <textarea id="comment-input-{{ $post->id }}" 
                          rows="2" 
                          placeholder="Write a comment..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                <button onclick="postComment({{ $post->id }})" 
                        class="mt-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                    Post Comment
                </button>
            </div>
        </div>

        <!-- Comments List -->
        <div id="comments-list-{{ $post->id }}" class="space-y-4">
            @foreach($post->comments()->with('registration.registrationCategory', 'replies.registration')->latest()->get() as $comment)
                @include('attendee.partials.wall-comment', ['comment' => $comment, 'postId' => $post->id])
            @endforeach
        </div>
    </div>
</div>

<script>
function toggleLike(postId) {
    const btn = document.getElementById(`like-btn-${postId}`);
    
    fetch(`/attendee/event-wall/${postId}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const svg = btn.querySelector('svg');
            if (data.liked) {
                btn.classList.add('text-indigo-600');
                btn.classList.remove('text-gray-600');
                svg.setAttribute('fill', 'currentColor');
            } else {
                btn.classList.remove('text-indigo-600');
                btn.classList.add('text-gray-600');
                svg.setAttribute('fill', 'none');
            }
            
            // Update likes count
            const statsDiv = document.querySelector(`#post-${postId} .flex.items-center.gap-4`);
            const likesSpan = statsDiv.querySelector('span:first-child');
            likesSpan.textContent = `${data.likes_count} ${data.likes_count === 1 ? 'like' : 'likes'}`;
        }
    })
    .catch(error => console.error('Error:', error));
}

function toggleComments(postId) {
    const commentsDiv = document.getElementById(`comments-${postId}`);
    commentsDiv.classList.toggle('hidden');
}

function postComment(postId) {
    const input = document.getElementById(`comment-input-${postId}`);
    const content = input.value.trim();
    
    if (!content) {
        alert('Please write a comment');
        return;
    }
    
    fetch(`/attendee/event-wall/${postId}/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to post comment');
    });
}

function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this post?')) {
        return;
    }
    
    fetch(`/attendee/event-wall/${postId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`post-${postId}`).remove();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete post');
    });
}

function openImageModal(imageUrl) {
    // Simple image modal - can be enhanced with a proper lightbox library
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4';
    modal.onclick = () => modal.remove();
    modal.innerHTML = `<img src="${imageUrl}" class="max-w-full max-h-full rounded-lg">`;
    document.body.appendChild(modal);
}
</script>
