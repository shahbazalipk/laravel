@extends('admin.layout')

@section('title', 'Create Email Campaign')

@push('head')
<script src="{{ asset('js/campaign-wizard.js') }}"></script>
@endpush

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.email-campaigns.email-campaigns.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create Email Campaign</h1>
            <p class="text-gray-600 mt-1">Follow the steps to create and configure your campaign</p>
        </div>
    </div>
</div>

<!-- Multi-Step Progress Indicator -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex-1 flex items-center" id="step-1-indicator">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-semibold">
                1
            </div>
            <div class="ml-3">
                <div class="text-sm font-medium text-gray-900">Select Template</div>
                <div class="text-xs text-gray-500">Choose email template</div>
            </div>
        </div>
        <div class="flex-shrink-0 w-16 h-0.5 bg-gray-300" id="line-1"></div>
        
        <div class="flex-1 flex items-center" id="step-2-indicator">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">
                2
            </div>
            <div class="ml-3">
                <div class="text-sm font-medium text-gray-500">Configure</div>
                <div class="text-xs text-gray-400">Campaign details</div>
            </div>
        </div>
        <div class="flex-shrink-0 w-16 h-0.5 bg-gray-300" id="line-2"></div>
        
        <div class="flex-1 flex items-center" id="step-3-indicator">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">
                3
            </div>
            <div class="ml-3">
                <div class="text-sm font-medium text-gray-500">Recipients</div>
                <div class="text-xs text-gray-400">Select audience</div>
            </div>
        </div>
        <div class="flex-shrink-0 w-16 h-0.5 bg-gray-300" id="line-3"></div>
        
        <div class="flex-1 flex items-center" id="step-4-indicator">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">
                4
            </div>
            <div class="ml-3">
                <div class="text-sm font-medium text-gray-500">Schedule</div>
                <div class="text-xs text-gray-400">When to send</div>
            </div>
        </div>
        <div class="flex-shrink-0 w-16 h-0.5 bg-gray-300" id="line-4"></div>
        
        <div class="flex-1 flex items-center" id="step-5-indicator">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">
                5
            </div>
            <div class="ml-3">
                <div class="text-sm font-medium text-gray-500">Review</div>
                <div class="text-xs text-gray-400">Confirm & send</div>
            </div>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.email-campaigns.email-campaigns.store') }}" method="POST" id="campaign-form">
        @csrf
        
        <!-- Step 1: Select Template -->
        <div id="step-1" class="step-content">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Step 1: Select Email Template</h2>
            
            @if($templates->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
                    No active templates found. Please <a href="{{ route('admin.email-campaigns.email-templates.create') }}" class="underline font-medium">create a template</a> first.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($templates as $template)
                    <label class="template-card cursor-pointer">
                        <input type="radio" 
                               name="email_template_id" 
                               value="{{ $template->id }}" 
                               class="hidden template-radio"
                               {{ old('email_template_id') == $template->id ? 'checked' : '' }}
                               required>
                        <div class="border-2 border-gray-300 rounded-lg p-4 hover:border-indigo-500 transition template-card-inner">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">{{ $template->name }}</h3>
                                    <p class="text-xs text-gray-500 mt-1">{{ ucfirst($template->category) }}</p>
                                </div>
                                <svg class="w-6 h-6 text-indigo-600 hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $template->subject }}</p>
                            @if($template->description)
                            <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $template->description }}</p>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('email_template_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endif
        </div>
        
        <!-- Step 2: Configure Campaign -->
        <div id="step-2" class="step-content hidden">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Step 2: Configure Campaign</h2>
            
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Campaign Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}"
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
                              placeholder="Optional description of this campaign">{{ old('description') }}</textarea>
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
                               value="{{ old('sender_name') }}"
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
                               value="{{ old('sender_email') }}"
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
                           value="{{ old('reply_to_email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('reply_to_email') border-red-500 @enderror"
                           placeholder="e.g., support@event.com">
                    @error('reply_to_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Step 3: Select Recipients -->
        <div id="step-3" class="step-content hidden">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Step 3: Select Recipients</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Recipient Source <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="space-y-3">
                        <label class="flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition">
                            <input type="radio" 
                                   name="recipient_source" 
                                   value="registrations" 
                                   class="mt-1"
                                   {{ old('recipient_source', 'registrations') === 'registrations' ? 'checked' : '' }}
                                   required>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">Event Registrations</div>
                                <div class="text-sm text-gray-600">Send to registered attendees with optional filters</div>
                            </div>
                        </label>
                        
                        <label class="flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition">
                            <input type="radio" 
                                   name="recipient_source" 
                                   value="csv" 
                                   class="mt-1"
                                   {{ old('recipient_source') === 'csv' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">CSV Upload</div>
                                <div class="text-sm text-gray-600">Upload a CSV file with recipient data</div>
                            </div>
                        </label>
                        
                        <label class="flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition">
                            <input type="radio" 
                                   name="recipient_source" 
                                   value="segment" 
                                   class="mt-1"
                                   {{ old('recipient_source') === 'segment' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">Saved Segment</div>
                                <div class="text-sm text-gray-600">Use a previously saved recipient segment</div>
                            </div>
                        </label>
                    </div>
                    @error('recipient_source')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
                    <p class="text-sm">
                        <strong>Note:</strong> You'll be able to configure filters and upload CSV files after creating the campaign.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Step 4: Schedule -->
        <div id="step-4" class="step-content hidden">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Step 4: Schedule Campaign</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        When to Send
                    </label>
                    
                    <div class="space-y-3">
                        <label class="flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition">
                            <input type="radio" 
                                   name="send_timing" 
                                   value="draft" 
                                   class="mt-1"
                                   {{ old('send_timing', 'draft') === 'draft' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">Save as Draft</div>
                                <div class="text-sm text-gray-600">Don't send yet, I'll send it manually later</div>
                            </div>
                        </label>
                        
                        <label class="flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition">
                            <input type="radio" 
                                   name="send_timing" 
                                   value="scheduled" 
                                   class="mt-1"
                                   {{ old('send_timing') === 'scheduled' ? 'checked' : '' }}>
                            <div class="ml-3 flex-1">
                                <div class="font-medium text-gray-900">Schedule for Later</div>
                                <div class="text-sm text-gray-600 mb-3">Choose a specific date and time</div>
                                <input type="datetime-local" 
                                       name="scheduled_at" 
                                       id="scheduled_at" 
                                       value="{{ old('scheduled_at') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       min="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </label>
                    </div>
                    @error('scheduled_at')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Step 5: Review -->
        <div id="step-5" class="step-content hidden">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Step 5: Review & Confirm</h2>
            
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-3">Campaign Summary</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Template:</dt>
                            <dd class="text-sm font-medium text-gray-900" id="review-template">-</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Campaign Name:</dt>
                            <dd class="text-sm font-medium text-gray-900" id="review-name">-</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Sender:</dt>
                            <dd class="text-sm font-medium text-gray-900" id="review-sender">-</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Recipients:</dt>
                            <dd class="text-sm font-medium text-gray-900" id="review-recipients">-</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Schedule:</dt>
                            <dd class="text-sm font-medium text-gray-900" id="review-schedule">-</dd>
                        </div>
                    </dl>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                    <p class="text-sm">
                        <strong>Important:</strong> Please review all details carefully before creating the campaign.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="mt-8 flex justify-between">
            <button type="button" 
                    id="prev-btn" 
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition hidden">
                Previous
            </button>
            <div class="flex space-x-3 ml-auto">
                <a href="{{ route('admin.email-campaigns.email-campaigns.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="button" 
                        id="next-btn" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Next
                </button>
                <button type="submit" 
                        id="submit-btn" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition hidden">
                    Create Campaign
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // Initialize Campaign Wizard
    document.addEventListener('DOMContentLoaded', function() {
        campaignWizard = new CampaignWizard({
            totalSteps: 5,
            // Note: These endpoints are optional for MVP - wizard will work without them
            recipientCountEndpoint: null,
            csvUploadEndpoint: null
        });
    });
</script>
@endsection
