@extends('attendee.layout')

@section('title', 'Messages')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Messages</h1>
    <p class="text-gray-600">Chat with your connections</p>
</div>

<div class="bg-white rounded-xl shadow-sm">
    @forelse($connections as $item)
        <a href="{{ route('attendee.messages.show', $item['user']) }}" 
           class="flex items-center gap-4 p-4 border-b hover:bg-gray-50 transition">
            <div class="w-14 h-14 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold">
                {{ substr($item['user']->first_name, 0, 1) }}{{ substr($item['user']->last_name, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-semibold text-gray-900 truncate">{{ $item['user']->full_name }}</h3>
                    @if($item['last_message'])
                        <span class="text-xs text-gray-500">{{ $item['last_message']->created_at->diffForHumans() }}</span>
                    @endif
                </div>
                @if($item['last_message'])
                    <p class="text-sm text-gray-600 truncate">
                        {{ $item['last_message']->sender_id === $registration->id ? 'You: ' : '' }}
                        {{ Str::limit($item['last_message']->message, 50) }}
                    </p>
                @else
                    <p class="text-sm text-gray-400 italic">No messages yet</p>
                @endif
            </div>
            @if($item['unread_count'] > 0)
                <span class="px-2 py-1 bg-indigo-600 text-white text-xs font-semibold rounded-full">
                    {{ $item['unread_count'] }}
                </span>
            @endif
        </a>
    @empty
        <div class="p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">No Messages</h3>
            <p class="text-gray-600 mb-4">Connect with attendees to start messaging</p>
            <a href="{{ route('attendee.connections') }}" 
               class="inline-block px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                View Connections
            </a>
        </div>
    @endforelse
</div>
@endsection
