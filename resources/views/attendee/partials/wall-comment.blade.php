<div class="flex gap-3" id="comment-{{ $comment->id }}">
    <div class="w-8 h-8 rounded-full bg-gray-400 flex items-center justify-center text-white text-sm font-semibold">
        {{ substr($comment->registration->first_name, 0, 1) }}{{ substr($comment->registration->last_name, 0, 1) }}
    </div>
    <div class="flex-1">
        <div class="bg-gray-100 rounded-lg p-3">
            <h5 class="font-semibold text-sm text-gray-900">
                {{ $comment->registration->first_name }} {{ $comment->registration->last_name }}
            </h5>
            <p class="text-sm text-gray-800 mt-1">{{ $comment->content }}</p>
            
            @if($comment->image)
                <img src="{{ asset('storage/' . $comment->image) }}" 
                     alt="Comment image" 
                     class="mt-2 max-w-xs rounded-lg cursor-pointer hover:opacity-90 transition"
                     onclick="openImageModal('{{ asset('storage/' . $comment->image) }}')">
            @endif
        </div>
        
        <div class="flex items-center gap-4 mt-2 text-xs text-gray-600">
            <button onclick="toggleCommentLike({{ $comment->id }})" 
                    id="comment-like-btn-{{ $comment->id }}"
                    class="{{ $comment->isLikedBy($registration->id) ? 'text-indigo-600 font-semibold' : '' }} hover:text-indigo-600">
                Like{{ $comment->likes_count > 0 ? ' (' . $comment->likes_count . ')' : '' }}
            </button>
            <button onclick="toggleReplyForm({{ $comment->id }})" class="hover:text-indigo-600">
                Reply
            </button>
            <span>{{ $comment->created_at->diffForHumans() }}</span>
            
            @if($comment->registration_id === $registration->id)
                <button onclick="deleteComment({{ $comment->id }})" class="hover:text-red-600">
                    Delete
                </button>
            @endif
        </div>

        <!-- Reply Form -->
        <div id="reply-form-{{ $comment->id }}" class="hidden mt-3 flex gap-2">
            <input type="text" 
                   id="reply-input-{{ $comment->id }}"
                   placeholder="Write a reply..."
                   class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <button onclick="postReply({{ $postId }}, {{ $comment->id }})" 
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                Reply
            </button>
        </div>

        <!-- Replies -->
        @if($comment->replies->isNotEmpty())
            <div class="mt-3 space-y-3">
                @foreach($comment->replies as $reply)
                    <div class="flex gap-2" id="comment-{{ $reply->id }}">
                        <div class="w-7 h-7 rounded-full bg-gray-300 flex items-center justify-center text-white text-xs font-semibold">
                            {{ substr($reply->registration->first_name, 0, 1) }}{{ substr($reply->registration->last_name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <div class="bg-gray-50 rounded-lg p-2">
                                <h6 class="font-semibold text-xs text-gray-900">
                                    {{ $reply->registration->first_name }} {{ $reply->registration->last_name }}
                                </h6>
                                <p class="text-xs text-gray-800 mt-1">{{ $reply->content }}</p>
                            </div>
                            <div class="flex items-center gap-3 mt-1 text-xs text-gray-600">
                                <button onclick="toggleCommentLike({{ $reply->id }})" 
                                        id="comment-like-btn-{{ $reply->id }}"
                                        class="{{ $reply->isLikedBy($registration->id) ? 'text-indigo-600 font-semibold' : '' }} hover:text-indigo-600">
                                    Like{{ $reply->likes_count > 0 ? ' (' . $reply->likes_count . ')' : '' }}
                                </button>
                                <span>{{ $reply->created_at->diffForHumans() }}</span>
                                
                                @if($reply->registration_id === $registration->id)
                                    <button onclick="deleteComment({{ $reply->id }})" class="hover:text-red-600">
                                        Delete
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
function toggleCommentLike(commentId) {
    fetch(`/attendee/event-wall/comments/${commentId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById(`comment-like-btn-${commentId}`);
            if (data.liked) {
                btn.classList.add('text-indigo-600', 'font-semibold');
            } else {
                btn.classList.remove('text-indigo-600', 'font-semibold');
            }
            btn.textContent = `Like${data.likes_count > 0 ? ' (' + data.likes_count + ')' : ''}`;
        }
    })
    .catch(error => console.error('Error:', error));
}

function toggleReplyForm(commentId) {
    const form = document.getElementById(`reply-form-${commentId}`);
    form.classList.toggle('hidden');
    if (!form.classList.contains('hidden')) {
        document.getElementById(`reply-input-${commentId}`).focus();
    }
}

function postReply(postId, parentId) {
    const input = document.getElementById(`reply-input-${parentId}`);
    const content = input.value.trim();
    
    if (!content) {
        alert('Please write a reply');
        return;
    }
    
    fetch(`/attendee/event-wall/${postId}/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            content,
            parent_id: parentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to post reply');
    });
}

function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }
    
    fetch(`/attendee/event-wall/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`comment-${commentId}`).remove();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete comment');
    });
}
</script>
