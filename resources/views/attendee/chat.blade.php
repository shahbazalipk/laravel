@extends('attendee.layout')

@section('title', 'Chat with ' . $attendee->full_name)

@section('content')
<div class="bg-white rounded-xl shadow-sm flex flex-col" style="height: calc(100vh - 200px);">
    <!-- Chat Header -->
    <div class="p-4 border-b flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('attendee.messages') }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold">
                {{ substr($attendee->first_name, 0, 1) }}{{ substr($attendee->last_name, 0, 1) }}
            </div>
            <div>
                <h2 class="font-semibold text-gray-900">{{ $attendee->full_name }}</h2>
                @if($attendee->job_title)
                    <p class="text-xs text-gray-500">{{ $attendee->job_title }}</p>
                @endif
            </div>
        </div>
        <a href="{{ route('attendee.attendees.show', $attendee) }}" 
           class="text-sm text-indigo-600 hover:text-indigo-700">
            View Profile
        </a>
    </div>

    <!-- Messages Container -->
    <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4">
        @foreach($messages as $message)
            <div class="flex {{ $message->sender_id === $registration->id ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs lg:max-w-md">
                    <div class="px-4 py-2 rounded-lg {{ $message->sender_id === $registration->id ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-900' }}">
                        <p class="text-sm">{{ $message->message }}</p>
                        @if($message->attachment)
                            <a href="{{ storage_public_url($message->attachment) }}" 
                               target="_blank"
                               class="block mt-2 text-xs underline">
                                View Attachment
                            </a>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1 {{ $message->sender_id === $registration->id ? 'text-right' : '' }}">
                        {{ $message->created_at->format('g:i A') }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Message Input -->
    <div class="p-4 border-t">
        <form id="message-form" class="flex gap-2">
            <input type="text" 
                   id="message-input"
                   placeholder="Type a message..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   required>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                Send
            </button>
        </form>
    </div>
</div>

<script>
const messagesContainer = document.getElementById('messages-container');
const messageForm = document.getElementById('message-form');
const messageInput = document.getElementById('message-input');

// Scroll to bottom
function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

scrollToBottom();

// Send message
messageForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const message = messageInput.value.trim();
    if (!message) return;
    
    fetch('{{ route('attendee.messages.send', $attendee) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadMessages();
        }
    })
    .catch(error => console.error('Error:', error));
});

// Load messages
function loadMessages() {
    fetch('{{ route('attendee.messages.get', $attendee) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderMessages(data.messages);
            }
        });
}

// Render messages
function renderMessages(messages) {
    messagesContainer.innerHTML = '';
    messages.forEach(message => {
        const isOwn = message.sender_id === {{ $registration->id }};
        const div = document.createElement('div');
        div.className = `flex ${isOwn ? 'justify-end' : 'justify-start'}`;
        div.innerHTML = `
            <div class="max-w-xs lg:max-w-md">
                <div class="px-4 py-2 rounded-lg ${isOwn ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-900'}">
                    <p class="text-sm">${escapeHtml(message.message)}</p>
                </div>
                <p class="text-xs text-gray-500 mt-1 ${isOwn ? 'text-right' : ''}">
                    ${formatTime(message.created_at)}
                </p>
            </div>
        `;
        messagesContainer.appendChild(div);
    });
    scrollToBottom();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

// Poll for new messages every 3 seconds
setInterval(loadMessages, 3000);
</script>
@endsection
