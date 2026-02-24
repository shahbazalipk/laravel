@extends('admin.layout')

@section('title', 'Landing Page Templates')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Landing Page Templates</h1>
        <p class="text-gray-600 mt-1">Manage CMS templates for event landing pages</p>
    </div>
    <a href="{{ route('admin.landing-page-templates.create') }}" 
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
        + New Template
    </a>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow-sm">
    @if($templates->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
            @foreach($templates as $template)
                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition">
                    <!-- Preview Image -->
                    <div class="h-48 bg-gray-100 relative">
                        @if($template->preview_image)
                            <img src="{{ asset('storage/' . $template->preview_image) }}" 
                                 alt="{{ $template->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Status Badge -->
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-1 text-xs font-semibold rounded {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $template->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Template Info -->
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-1">{{ $template->name }}</h3>
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                            {{ $template->description ?? 'No description' }}
                        </p>
                        
                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.landing-page-templates.preview', $template) }}" 
                                   target="_blank"
                                   class="text-sm text-indigo-600 hover:text-indigo-800">
                                    Preview
                                </a>
                                <a href="{{ route('admin.landing-page-templates.edit', $template) }}" 
                                   class="text-sm text-indigo-600 hover:text-indigo-800">
                                    Edit
                                </a>
                            </div>
                            
                            <div class="flex space-x-2">
                                <form action="{{ route('admin.landing-page-templates.toggle-active', $template) }}" 
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="text-sm {{ $template->is_active ? 'text-gray-600 hover:text-gray-800' : 'text-green-600 hover:text-green-800' }}">
                                        {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.landing-page-templates.destroy', $template) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this template?')" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $templates->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No templates</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new template.</p>
            <div class="mt-6">
                <a href="{{ route('admin.landing-page-templates.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    + New Template
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
