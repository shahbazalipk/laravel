@extends('admin.layout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Gallery Settings</h1>
        <p class="text-gray-600 mt-1">Configure your event gallery settings</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Store -->
        <a href="{{ route('admin.gallery-settings.store') }}" 
           class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition group">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition">
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Store</h3>
            <p class="text-sm text-gray-600">Configure store integration and settings</p>
        </a>

        <!-- Photo Sizes -->
        <a href="{{ route('admin.gallery-settings.photo-sizes') }}" 
           class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition group">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition">
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Photo Sizes</h3>
            <p class="text-sm text-gray-600">Manage upload and download photo sizes</p>
        </a>

        <!-- Presets -->
        <a href="{{ route('admin.gallery-settings.presets') }}" 
           class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition group">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition">
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Presets</h3>
            <p class="text-sm text-gray-600">Create and manage photo editor presets</p>
        </a>

        <!-- Branding -->
        <a href="{{ route('admin.gallery-settings.branding') }}" 
           class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition group">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition">
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Branding</h3>
            <p class="text-sm text-gray-600">Customize gallery appearance and branding</p>
        </a>

        <!-- Forms -->
        <a href="{{ route('admin.gallery-settings.forms') }}" 
           class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition group">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition">
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Forms</h3>
            <p class="text-sm text-gray-600">Manage forms for online gallery visitors</p>
        </a>
    </div>
</div>
@endsection
