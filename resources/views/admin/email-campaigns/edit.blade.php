@extends('admin.layout')

@section('title', 'Edit Email Campaign')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.email-campaigns.email-campaigns.show', $campaign) }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Campaign</h1>
            <p class="text-gray-600 mt-1">Update campaign details</p>
        </div>
    </div>
</div>

@if($campaign->status !== 'draft')
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-6">
    <p class="text-sm">
        <strong>Note:</strong> This campaign has already been sent or scheduled. Only basic details can be edited.
    </p>
</div>
@endif

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.email-campaigns.email-campaigns.update', $campaign) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Campaign Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $campaign->name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                       placeholder="e.g., Event Invitation 2024"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
                          placeholder="Optional description of this campaign">{{ old('description', $campaign->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="sender_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Sender Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="sender_name" 
                           id="sender_name" 
                           value="{{ old('sender_name', $campaign->sender_name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sender_name') border-red-500 @enderror"
                           placeholder="e.g., Event Team"
                           required>
                    @error('sender_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="sender_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Sender Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           name="sender_email" 
                           id="sender_email" 
                           value="{{ old('sender_email', $campaign->sender_email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sender_email') border-red-500 @enderror"
                           placeholder="e.g., noreply@event.com"
                           required>
                    @error('sender_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div>
                <label for="reply_to_email" class="block text-sm font-medium text-gray-700 mb-2">
                    Reply-To Email
                </label>
                <input type="email" 
                       name="reply_to_email" 
                       id="reply_to_email" 
                       value="{{ old('reply_to_email', $campaign->reply_to_email) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('reply_to_email') border-red-500 @enderror"
                       placeholder="e.g., support@event.com">
                @error('reply_to_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Read-only fields -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Campaign Details (Read-only)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Template</label>
                        <div class="px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                            {{ $campaign->template->name }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Source</label>
                        <div class="px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                            {{ ucfirst($campaign->recipient_source) }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <div class="px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                            {{ ucfirst($campaign->status) }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Recipients</label>
                        <div class="px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                            {{ number_format($campaign->total_recipients) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.email-campaigns.email-campaigns.show', $campaign) }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Update Campaign
            </button>
        </div>
    </form>
</div>
@endsection
