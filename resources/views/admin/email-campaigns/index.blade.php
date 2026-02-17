@extends('admin.layout')

@section('title', 'Email Campaigns')

@section('content')
<!-- Header Section -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Email Campaigns</h1>
        <p class="text-gray-600 mt-1">Create and manage email campaigns to communicate with registrants</p>
    </div>
    <a href="{{ route('admin.email-campaigns.email-campaigns.create') }}" 
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Create Campaign
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.email-campaigns.email-campaigns.index') }}" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" id="status" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>Sending</option>
                <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>
        
        @if(request('status'))
        <div class="flex items-end">
            <a href="{{ route('admin.email-campaigns.email-campaigns.index') }}" 
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Clear Filters
            </a>
        </div>
        @endif
    </form>
</div>

@if($campaigns->isEmpty())
    <!-- Empty State -->
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Campaigns Found</h3>
        <p class="text-gray-600 mb-4">Get started by creating your first email campaign.</p>
        <a href="{{ route('admin.email-campaigns.email-campaigns.create') }}" 
           class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create First Campaign
        </a>
    </div>
@else
    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Campaign
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Recipients
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statistics
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($campaigns as $campaign)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $campaign->name }}</div>
                                <div class="text-sm text-gray-500">Template: {{ $campaign->template->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'scheduled' => 'bg-blue-100 text-blue-800',
                                    'sending' => 'bg-yellow-100 text-yellow-800',
                                    'paused' => 'bg-orange-100 text-orange-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                ];
                                $statusColor = $statusColors[$campaign->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                {{ ucfirst($campaign->status) }}
                            </span>
                            
                            @if($campaign->status === 'sending')
                            <div class="mt-2">
                                @php
                                    $progress = $campaign->total_recipients > 0 
                                        ? ($campaign->sent_count / $campaign->total_recipients) * 100 
                                        : 0;
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $campaign->sent_count }} / {{ $campaign->total_recipients }}
                                </div>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ number_format($campaign->total_recipients) }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst($campaign->recipient_source) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($campaign->sent_count > 0)
                            <div class="text-sm space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Sent:</span>
                                    <span class="font-medium">{{ number_format($campaign->sent_count) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Delivered:</span>
                                    <span class="font-medium">{{ number_format($campaign->delivered_count) }} ({{ number_format($campaign->delivery_rate, 1) }}%)</span>
                                </div>
                                @if($campaign->opened_count > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Opened:</span>
                                    <span class="font-medium">{{ number_format($campaign->opened_count) }} ({{ number_format($campaign->open_rate, 1) }}%)</span>
                                </div>
                                @endif
                                @if($campaign->clicked_count > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Clicked:</span>
                                    <span class="font-medium">{{ number_format($campaign->clicked_count) }} ({{ number_format($campaign->click_rate, 1) }}%)</span>
                                </div>
                                @endif
                            </div>
                            @else
                            <span class="text-sm text-gray-500">Not sent yet</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $campaign->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $campaign->created_at->format('g:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <!-- View Button -->
                                <a href="{{ route('admin.email-campaigns.email-campaigns.show', $campaign) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 transition"
                                   title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                
                                @if($campaign->status === 'draft')
                                <!-- Edit Button -->
                                <a href="{{ route('admin.email-campaigns.email-campaigns.edit', $campaign) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 transition"
                                   title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                
                                <!-- Send Button -->
                                <form action="{{ route('admin.email-campaigns.email-campaigns.send', $campaign) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to send this campaign?');"
                                      class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="text-green-600 hover:text-green-900 transition"
                                            title="Send Campaign">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                
                                @if($campaign->status === 'sending')
                                <!-- Pause Button -->
                                <form action="{{ route('admin.email-campaigns.email-campaigns.pause', $campaign) }}" 
                                      method="POST" 
                                      class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="text-orange-600 hover:text-orange-900 transition"
                                            title="Pause Campaign">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                
                                @if($campaign->status === 'paused')
                                <!-- Resume Button -->
                                <form action="{{ route('admin.email-campaigns.email-campaigns.resume', $campaign) }}" 
                                      method="POST" 
                                      class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="text-green-600 hover:text-green-900 transition"
                                            title="Resume Campaign">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                
                                @if(in_array($campaign->status, ['sending', 'paused', 'scheduled']))
                                <!-- Cancel Button -->
                                <form action="{{ route('admin.email-campaigns.email-campaigns.cancel', $campaign) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to cancel this campaign?');"
                                      class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 transition"
                                            title="Cancel Campaign">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                
                                @if(in_array($campaign->status, ['draft', 'completed', 'cancelled', 'failed']))
                                <!-- Delete Button -->
                                <form action="{{ route('admin.email-campaigns.email-campaigns.destroy', $campaign) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this campaign?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 transition"
                                            title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Total Count -->
    <div class="mt-4 text-sm text-gray-600">
        Total: {{ $campaigns->count() }} campaign{{ $campaigns->count() !== 1 ? 's' : '' }}
    </div>
@endif
@endsection
