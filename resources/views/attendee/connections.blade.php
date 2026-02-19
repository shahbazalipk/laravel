@extends('attendee.layout')

@section('title', 'My Connections')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">My Connections</h1>
    <p class="text-gray-600">Manage your professional network</p>
</div>

<!-- Tabs -->
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <div class="flex gap-2 border-b">
        <button onclick="showTab('connections')" 
                id="tab-connections"
                class="px-4 py-2 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600">
            Connections ({{ $connections->count() }})
        </button>
        <button onclick="showTab('requests')" 
                id="tab-requests"
                class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-600 hover:text-gray-900">
            Requests ({{ $pendingRequests->count() }})
        </button>
        <button onclick="showTab('sent')" 
                id="tab-sent"
                class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-600 hover:text-gray-900">
            Sent ({{ $sentRequests->count() }})
        </button>
    </div>
</div>

<!-- Connections Tab -->
<div id="content-connections" class="space-y-4">
    @forelse($connections as $connection)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold text-xl">
                        {{ substr($connection->attendee->first_name, 0, 1) }}{{ substr($connection->attendee->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            <a href="{{ route('attendee.attendees.show', $connection->attendee) }}" class="hover:text-indigo-600">
                                {{ $connection->attendee->full_name }}
                            </a>
                        </h3>
                        @if($connection->attendee->job_title)
                            <p class="text-sm text-gray-600">{{ $connection->attendee->job_title }}</p>
                        @endif
                        @if($connection->attendee->company_name)
                            <p class="text-sm text-gray-500">{{ $connection->attendee->company_name }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">Connected {{ $connection->accepted_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('attendee.messages.show', $connection->attendee) }}" 
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Message
                    </a>
                    <button onclick="removeConnection({{ $connection->id }})" 
                            class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold rounded-lg transition">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">No Connections Yet</h3>
            <p class="text-gray-600 mb-4">Start connecting with other attendees</p>
            <a href="{{ route('attendee.attendees') }}" 
               class="inline-block px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                Browse Attendees
            </a>
        </div>
    @endforelse
</div>

<!-- Requests Tab -->
<div id="content-requests" class="hidden space-y-4">
    @forelse($pendingRequests as $request)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold text-xl">
                        {{ substr($request->sender->first_name, 0, 1) }}{{ substr($request->sender->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $request->sender->full_name }}</h3>
                        @if($request->sender->job_title)
                            <p class="text-sm text-gray-600">{{ $request->sender->job_title }}</p>
                        @endif
                        @if($request->message)
                            <p class="text-sm text-gray-700 mt-2 italic">"{{ $request->message }}"</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $request->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="acceptRequest({{ $request->id }})" 
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition">
                        Accept
                    </button>
                    <button onclick="rejectRequest({{ $request->id }})" 
                            class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold rounded-lg transition">
                            Reject
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <p class="text-gray-600">No pending requests</p>
        </div>
    @endforelse
</div>

<!-- Sent Tab -->
<div id="content-sent" class="hidden space-y-4">
    @forelse($sentRequests as $request)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gray-400 flex items-center justify-center text-white font-semibold text-xl">
                        {{ substr($request->receiver->first_name, 0, 1) }}{{ substr($request->receiver->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $request->receiver->full_name }}</h3>
                        @if($request->receiver->job_title)
                            <p class="text-sm text-gray-600">{{ $request->receiver->job_title }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">Sent {{ $request->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <button onclick="cancelRequest({{ $request->id }})" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">
                    Cancel
                </button>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <p class="text-gray-600">No sent requests</p>
        </div>
    @endforelse
</div>

<script>
function showTab(tab) {
    // Hide all tabs
    document.getElementById('content-connections').classList.add('hidden');
    document.getElementById('content-requests').classList.add('hidden');
    document.getElementById('content-sent').classList.add('hidden');
    
    // Remove active state from all buttons
    document.getElementById('tab-connections').classList.remove('border-indigo-600', 'text-indigo-600');
    document.getElementById('tab-connections').classList.add('border-transparent', 'text-gray-600');
    document.getElementById('tab-requests').classList.remove('border-indigo-600', 'text-indigo-600');
    document.getElementById('tab-requests').classList.add('border-transparent', 'text-gray-600');
    document.getElementById('tab-sent').classList.remove('border-indigo-600', 'text-indigo-600');
    document.getElementById('tab-sent').classList.add('border-transparent', 'text-gray-600');
    
    // Show selected tab
    document.getElementById('content-' + tab).classList.remove('hidden');
    document.getElementById('tab-' + tab).classList.remove('border-transparent', 'text-gray-600');
    document.getElementById('tab-' + tab).classList.add('border-indigo-600', 'text-indigo-600');
}

function acceptRequest(id) {
    fetch(`/attendee/connections/${id}/accept`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) window.location.reload();
    });
}

function rejectRequest(id) {
    if (!confirm('Reject this request?')) return;
    fetch(`/attendee/connections/${id}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) window.location.reload();
    });
}

function cancelRequest(id) {
    if (!confirm('Cancel this request?')) return;
    fetch(`/attendee/connections/${id}/cancel`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) window.location.reload();
    });
}

function removeConnection(id) {
    if (!confirm('Remove this connection?')) return;
    fetch(`/attendee/connections/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) window.location.reload();
    });
}
</script>
@endsection
