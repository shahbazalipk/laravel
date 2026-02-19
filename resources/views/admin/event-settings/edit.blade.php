@extends('admin.layout')

@section('title', 'Event Settings')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Event Settings</h1>
    <p class="text-gray-600 mt-1">Configure your event details, registration settings, and integrations</p>
</div>

<!-- Tab Navigation -->
<div class="bg-white rounded-t-lg shadow-sm">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
            <button type="button" onclick="switchTab('basic')" id="tab-basic" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-indigo-500 text-indigo-600">
                Basic Information
            </button>
            <button type="button" onclick="switchTab('dates')" id="tab-dates" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Dates & Times
            </button>
            <button type="button" onclick="switchTab('financial')" id="tab-financial" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Financial
            </button>
            <button type="button" onclick="switchTab('registration')" id="tab-registration" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Registration
            </button>
            <button type="button" onclick="switchTab('urls')" id="tab-urls" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                URLs & Links
            </button>
            <button type="button" onclick="switchTab('media')" id="tab-media" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Media & Assets
            </button>
            <button type="button" onclick="switchTab('contact')" id="tab-contact" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Contact & Address
            </button>
            <button type="button" onclick="switchTab('social')" id="tab-social" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Social Media
            </button>
            <button type="button" onclick="switchTab('email')" id="tab-email" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Email Settings
            </button>
            <button type="button" onclick="switchTab('seo')" id="tab-seo" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                SEO & Captcha
            </button>
        </nav>
    </div>
</div>

<form action="{{ route('admin.event-settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <!-- Tab Content Container -->
    <div class="bg-white rounded-b-lg shadow-sm p-6 mb-6">
        
        <!-- Basic Information Tab -->
        <div id="content-basic" class="tab-content">
            @include('admin.event-settings._basic')
        </div>

        <!-- Dates & Times Tab -->
        <div id="content-dates" class="tab-content hidden">
            @include('admin.event-settings._dates')
        </div>

        <!-- Financial Tab -->
        <div id="content-financial" class="tab-content hidden">
            @include('admin.event-settings._financial')
        </div>

        <!-- Registration Tab -->
        <div id="content-registration" class="tab-content hidden">
            @include('admin.event-settings._registration')
        </div>

        <!-- URLs Tab -->
        <div id="content-urls" class="tab-content hidden">
            @include('admin.event-settings._urls')
        </div>

        <!-- Media Tab -->
        <div id="content-media" class="tab-content hidden">
            @include('admin.event-settings._media')
        </div>

        <!-- Contact & Address Tab -->
        <div id="content-contact" class="tab-content hidden">
            @include('admin.event-settings._manager')
            @include('admin.event-settings._messages')
        </div>

        <!-- Social Media Tab -->
        <div id="content-social" class="tab-content hidden">
            @include('admin.event-settings._social')
        </div>

        <!-- Email Settings Tab -->
        <div id="content-email" class="tab-content hidden">
            @include('admin.event-settings._email')
        </div>

        <!-- SEO & Captcha Tab -->
        <div id="content-seo" class="tab-content hidden">
            @include('admin.event-settings._seo')
            @include('admin.event-settings._captcha')
        </div>
    </div>

    <!-- Form Actions -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.dashboard') }}" 
           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Cancel
        </a>
        <button type="submit" 
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            Update Settings
        </button>
    </div>
</form>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-indigo-500', 'text-indigo-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-indigo-500', 'text-indigo-600');
}
</script>
@endsection
