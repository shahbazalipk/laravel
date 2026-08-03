@extends('admin.layout')

@section('title', 'Event URLs')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Event URLs</h1>
        <p class="text-gray-600 mt-1">Manage registration URLs for different event types</p>
    </div>
    <a href="{{ route('admin.event-urls.create') }}" 
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add New URL
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.event-urls.index') }}" class="flex gap-4">
        <div class="flex-1">
            <select name="type" class="w-full rounded-lg border-gray-300">
                <option value="">All Types</option>
                <option value="online" {{ request('type') === 'online' ? 'selected' : '' }}>Online</option>
                <option value="onsite" {{ request('type') === 'onsite' ? 'selected' : '' }}>Onsite</option>
                <option value="exhibitors" {{ request('type') === 'exhibitors' ? 'selected' : '' }}>Exhibitors</option>
                <option value="groups" {{ request('type') === 'groups' ? 'selected' : '' }}>Groups</option>
                <option value="badge" {{ request('type') === 'badge' ? 'selected' : '' }}>Badge Printing</option>
            </select>
        </div>
        <div class="flex-1">
            <select name="status" class="w-full rounded-lg border-gray-300">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition">
            Filter
        </button>
        <a href="{{ route('admin.event-urls.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition">
            Clear
        </a>
    </form>
</div>

@if($urls->isEmpty())
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No URLs Created</h3>
        <p class="text-gray-600 mb-4">Get started by creating your first event URL.</p>
        <a href="{{ route('admin.event-urls.create') }}" 
           class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add First URL
        </a>
    </div>
@else
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            URL Path
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Categories
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($urls as $url)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $url->name }}</div>
                            @if($url->description)
                                <div class="text-xs text-gray-500">{{ Str::limit($url->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $url->type === 'online' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $url->type === 'onsite' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $url->type === 'exhibitors' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $url->type === 'groups' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $url->type === 'badge' ? 'bg-orange-100 text-orange-800' : '' }}">
                                    {{ ucfirst($url->type) }}
                                </span>
                                @if($url->type === 'online')
                                    <span class="px-2 py-0.5 text-xs rounded {{ $url->usesSinglePageRegistration() ? 'bg-violet-50 text-violet-700' : 'bg-slate-50 text-slate-600' }}"
                                          data-testid="event-url-format-{{ $url->id }}">
                                        {{ $url->registration_format?->label() ?? 'Multi-step wizard' }}
                                    </span>
                                @endif
                                @if($url->type === 'badge')
                                    <div class="flex gap-1">
                                        @if($url->allow_reprint)
                                            <span class="px-2 py-0.5 text-xs bg-blue-50 text-blue-700 rounded">Reprint</span>
                                        @endif
                                        @if($url->allow_print_from_photo)
                                            <span class="px-2 py-0.5 text-xs bg-green-50 text-green-700 rounded">Photo</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="text-sm text-gray-900 font-mono">{{ $url->full_url }}</div>
                                <button onclick="copyUrl('{{ url($url->full_url) }}')" 
                                        class="text-indigo-600 hover:text-indigo-900 transition"
                                        title="Copy full URL">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                <a href="{{ url($url->full_url) }}" target="_blank" class="text-indigo-600 hover:underline">
                                    {{ url($url->full_url) }}
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs text-gray-600">
                                @if(empty($url->enabled_categories))
                                    <span class="text-gray-400">All categories</span>
                                @else
                                    {{ count($url->enabled_categories) }} selected
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($url->is_active)
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.event-urls.stats', $url) }}"
                               class="mr-3 text-emerald-600 hover:text-emerald-900"
                               data-testid="event-url-stats-{{ $url->id }}">Stats</a>
                            <a href="{{ route('admin.event-urls.edit', $url) }}"
                               class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <form action="{{ route('admin.event-urls.destroy', $url) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this URL?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $urls->links() }}
    </div>
@endif

<script>
function copyUrl(url) {
    navigator.clipboard.writeText(url).then(function() {
        // Show success message
        const message = document.createElement('div');
        message.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        message.textContent = 'URL copied to clipboard!';
        document.body.appendChild(message);
        
        setTimeout(() => {
            message.remove();
        }, 3000);
    }).catch(function(err) {
        alert('Failed to copy URL: ' + err);
    });
}
</script>
@endsection
