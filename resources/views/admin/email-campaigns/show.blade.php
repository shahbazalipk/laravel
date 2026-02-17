@extends('admin.layout')

@section('title', 'Campaign Details')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/campaign-statistics.js') }}"></script>
@endpush

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.email-campaigns.email-campaigns.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $campaign->name }}</h1>
                <p class="text-gray-600 mt-1">Campaign details and statistics</p>
            </div>
        </div>
        
        <div class="flex space-x-2">
            @if($campaign->status === 'draft')
            <a href="{{ route('admin.email-campaigns.email-campaigns.edit', $campaign) }}" 
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
            
            <form action="{{ route('admin.email-campaigns.email-campaigns.send', $campaign) }}" 
                  method="POST" 
                  onsubmit="return confirm('Are you sure you want to send this campaign?');"
                  class="inline">
                @csrf
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send Campaign
                </button>
            </form>
            @endif
            
            @if($campaign->status === 'sending')
            <form action="{{ route('admin.email-campaigns.email-campaigns.pause', $campaign) }}" 
                  method="POST" 
                  class="inline">
                @csrf
                <button type="submit" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Pause
                </button>
            </form>
            @endif
            
            @if($campaign->status === 'paused')
            <form action="{{ route('admin.email-campaigns.email-campaigns.resume', $campaign) }}" 
                  method="POST" 
                  class="inline">
                @csrf
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Resume
                </button>
            </form>
            @endif
            
            @if(in_array($campaign->status, ['sending', 'paused', 'scheduled']))
            <form action="{{ route('admin.email-campaigns.email-campaigns.cancel', $campaign) }}" 
                  method="POST" 
                  onsubmit="return confirm('Are you sure you want to cancel this campaign?');"
                  class="inline">
                @csrf
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Cancel
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- Status Badge -->
<div class="mb-6">
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
    <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusColor }}">
        {{ ucfirst($campaign->status) }}
    </span>
</div>

<!-- Campaign Information -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Campaign Information</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Template</label>
            <p class="text-sm text-gray-900">{{ $campaign->template->name }}</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Source</label>
            <p class="text-sm text-gray-900">{{ ucfirst($campaign->recipient_source) }}</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sender</label>
            <p class="text-sm text-gray-900">{{ $campaign->sender_name }} &lt;{{ $campaign->sender_email }}&gt;</p>
        </div>
        
        @if($campaign->reply_to_email)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reply-To</label>
            <p class="text-sm text-gray-900">{{ $campaign->reply_to_email }}</p>
        </div>
        @endif
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
            <p class="text-sm text-gray-900">{{ $campaign->created_at->format('M d, Y g:i A') }}</p>
        </div>
        
        @if($campaign->scheduled_at)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled For</label>
            <p class="text-sm text-gray-900">{{ $campaign->scheduled_at->format('M d, Y g:i A') }}</p>
        </div>
        @endif
        
        @if($campaign->started_at)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Started</label>
            <p class="text-sm text-gray-900">{{ $campaign->started_at->format('M d, Y g:i A') }}</p>
        </div>
        @endif
        
        @if($campaign->completed_at)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Completed</label>
            <p class="text-sm text-gray-900">{{ $campaign->completed_at->format('M d, Y g:i A') }}</p>
        </div>
        @endif
        
        @if($campaign->description)
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <p class="text-sm text-gray-900">{{ $campaign->description }}</p>
        </div>
        @endif
    </div>
</div>

<!-- Send Progress (for sending campaigns) -->
@if($campaign->status === 'sending')
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Send Progress</h2>
    
    @php
        $progress = $campaign->total_recipients > 0 
            ? ($campaign->sent_count / $campaign->total_recipients) * 100 
            : 0;
    @endphp
    
    <div class="mb-4">
        <div class="flex justify-between text-sm text-gray-600 mb-2">
            <span>{{ number_format($campaign->sent_count) }} of {{ number_format($campaign->total_recipients) }} sent</span>
            <span>{{ number_format($progress, 1) }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-indigo-600 h-4 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
        </div>
    </div>
    
    <p class="text-sm text-gray-600">
        Campaign is currently being sent. This page will auto-refresh every 30 seconds.
    </p>
</div>
@endif

<!-- Statistics -->
@if($campaign->sent_count > 0)
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Campaign Statistics</h2>
        <button onclick="window.location.reload()" 
                class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh
        </button>
    </div>
    
    <!-- Statistics Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-50 rounded-lg p-4" data-stat="total_recipients">
            <div class="text-sm text-gray-600 mb-1">Total Recipients</div>
            <div class="text-2xl font-bold text-gray-900 stat-value">{{ number_format($campaign->total_recipients) }}</div>
        </div>
        
        <div class="bg-blue-50 rounded-lg p-4" data-stat="sent_count">
            <div class="text-sm text-gray-600 mb-1">Sent</div>
            <div class="text-2xl font-bold text-blue-900 stat-value">{{ number_format($campaign->sent_count) }}</div>
        </div>
        
        <div class="bg-green-50 rounded-lg p-4" data-stat="delivered_count">
            <div class="text-sm text-gray-600 mb-1">Delivered</div>
            <div class="text-2xl font-bold text-green-900 stat-value">{{ number_format($campaign->delivered_count) }}</div>
            <div class="text-xs text-gray-600 mt-1 stat-percent">{{ number_format($campaign->delivery_rate, 1) }}%</div>
        </div>
        
        <div class="bg-purple-50 rounded-lg p-4" data-stat="opened_count">
            <div class="text-sm text-gray-600 mb-1">Opened</div>
            <div class="text-2xl font-bold text-purple-900 stat-value">{{ number_format($campaign->opened_count) }}</div>
            <div class="text-xs text-gray-600 mt-1 stat-percent">{{ number_format($campaign->open_rate, 1) }}%</div>
        </div>
        
        <div class="bg-indigo-50 rounded-lg p-4" data-stat="clicked_count">
            <div class="text-sm text-gray-600 mb-1">Clicked</div>
            <div class="text-2xl font-bold text-indigo-900 stat-value">{{ number_format($campaign->clicked_count) }}</div>
            <div class="text-xs text-gray-600 mt-1 stat-percent">{{ number_format($campaign->click_rate, 1) }}%</div>
        </div>
        
        <div class="bg-orange-50 rounded-lg p-4" data-stat="bounced_count">
            <div class="text-sm text-gray-600 mb-1">Bounced</div>
            <div class="text-2xl font-bold text-orange-900 stat-value">{{ number_format($campaign->bounced_count) }}</div>
            @if($campaign->sent_count > 0)
            <div class="text-xs text-gray-600 mt-1 stat-percent">{{ number_format(($campaign->bounced_count / $campaign->sent_count) * 100, 1) }}%</div>
            @endif
        </div>
        
        <div class="bg-red-50 rounded-lg p-4" data-stat="failed_count">
            <div class="text-sm text-gray-600 mb-1">Failed</div>
            <div class="text-2xl font-bold text-red-900 stat-value">{{ number_format($campaign->failed_count) }}</div>
            @if($campaign->sent_count > 0)
            <div class="text-xs text-gray-600 mt-1 stat-percent">{{ number_format(($campaign->failed_count / $campaign->sent_count) * 100, 1) }}%</div>
            @endif
        </div>
        
        <div class="bg-gray-50 rounded-lg p-4" data-stat="unsubscribed_count">
            <div class="text-sm text-gray-600 mb-1">Unsubscribed</div>
            <div class="text-2xl font-bold text-gray-900 stat-value">{{ number_format($campaign->unsubscribed_count) }}</div>
        </div>
    </div>
    
    <!-- Charts Placeholder -->
    <div class="border-t border-gray-200 pt-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Performance Over Time</h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Delivery Chart -->
            <div class="bg-white rounded-lg border p-4">
                <canvas id="delivery-chart" 
                        data-sent="{{ $campaign->sent_count }}"
                        data-delivered="{{ $campaign->delivered_count }}"
                        data-bounced="{{ $campaign->bounced_count }}"
                        data-failed="{{ $campaign->failed_count }}"
                        height="250"></canvas>
            </div>
            
            <!-- Engagement Chart -->
            <div class="bg-white rounded-lg border p-4">
                <canvas id="engagement-chart"
                        data-delivered="{{ $campaign->delivered_count }}"
                        data-opened="{{ $campaign->opened_count }}"
                        data-clicked="{{ $campaign->clicked_count }}"
                        height="250"></canvas>
            </div>
        </div>
        
        <!-- Timeline Chart -->
        <div class="bg-white rounded-lg border p-4">
            <canvas id="timeline-chart" 
                    data-timeline='{"labels":[],"sent":[],"delivered":[],"opened":[],"clicked":[]}'
                    height="200"></canvas>
        </div>
        
        <!-- Refresh Indicator -->
        @if($campaign->status === 'sending')
        <div class="mt-4 flex items-center justify-center space-x-2">
            <div id="refresh-indicator" class="hidden">
                <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <span id="refresh-countdown" class="text-sm text-gray-600">Next refresh in 30s</span>
        </div>
        @endif
    </div>
</div>
@endif

<!-- Recipients List -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Recipients</h2>
        @if($campaign->status === 'completed')
        <a href="#" 
           class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Report
        </a>
        @endif
    </div>
    
    @if($campaign->recipients->isEmpty())
    <div class="text-center py-8">
        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <p class="text-sm text-gray-600">No recipients added yet</p>
        @if($campaign->status === 'draft')
        <p class="text-xs text-gray-500 mt-1">Recipients will be added when you configure the campaign</p>
        @endif
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Recipient
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Sent At
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Last Activity
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($campaign->recipients->take(50) as $recipient)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $recipient->first_name }} {{ $recipient->last_name }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $recipient->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $recipientStatusColors = [
                                'pending' => 'bg-gray-100 text-gray-800',
                                'sent' => 'bg-blue-100 text-blue-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'opened' => 'bg-purple-100 text-purple-800',
                                'clicked' => 'bg-indigo-100 text-indigo-800',
                                'bounced' => 'bg-orange-100 text-orange-800',
                                'failed' => 'bg-red-100 text-red-800',
                            ];
                            $recipientStatusColor = $recipientStatusColors[$recipient->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $recipientStatusColor }}">
                            {{ ucfirst($recipient->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $recipient->sent_at ? $recipient->sent_at->format('M d, g:i A') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($recipient->clicked_at)
                            Clicked {{ $recipient->clicked_at->format('M d, g:i A') }}
                        @elseif($recipient->opened_at)
                            Opened {{ $recipient->opened_at->format('M d, g:i A') }}
                        @elseif($recipient->delivered_at)
                            Delivered {{ $recipient->delivered_at->format('M d, g:i A') }}
                        @elseif($recipient->bounced_at)
                            Bounced {{ $recipient->bounced_at->format('M d, g:i A') }}
                        @elseif($recipient->failed_at)
                            Failed {{ $recipient->failed_at->format('M d, g:i A') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if($campaign->recipients->count() > 50)
    <div class="mt-4 text-sm text-gray-600 text-center">
        Showing first 50 of {{ number_format($campaign->recipients->count()) }} recipients
    </div>
    @endif
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Initialize Campaign Statistics Dashboard
    document.addEventListener('DOMContentLoaded', function() {
        campaignStatistics = new CampaignStatistics({
            campaignId: {{ $campaign->id }},
            refreshInterval: 30000,
            statisticsEndpoint: '{{ route("admin.email-campaigns.statistics", $campaign) }}',
            autoRefresh: {{ $campaign->status === 'sending' ? 'true' : 'false' }}
        });
    });
</script>
@endsection
