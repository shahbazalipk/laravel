@extends('admin.layout')

@section('title', 'Edit Group Type')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.group-types.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Group Type</h1>
            <p class="text-gray-600 mt-1">Update group type information</p>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.group-types.update', $groupType) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $groupType->name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                       placeholder="e.g., Corporate Group"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                    Slug
                </label>
                <input type="text" 
                       name="slug" 
                       id="slug" 
                       value="{{ old('slug', $groupType->slug) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('slug') border-red-500 @enderror"
                       placeholder="Auto-generated from name">
                <p class="mt-1 text-xs text-gray-400">Leave empty to auto-generate from name</p>
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Description -->
        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>
            <textarea name="description" 
                      id="description" 
                      rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
                      placeholder="Optional description">{{ old('description', $groupType->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Color -->
            <div>
                <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                    Color
                </label>
                <div class="flex items-center space-x-3">
                    <input type="color" 
                           name="color" 
                           id="color" 
                           value="{{ old('color', $groupType->color ?? '#6366f1') }}"
                           class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                    <input type="text" 
                           id="color-text"
                           value="{{ old('color', $groupType->color ?? '#6366f1') }}"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                           readonly>
                </div>
                @error('color')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                    Sort Order
                </label>
                <input type="number" 
                       name="sort_order" 
                       id="sort_order" 
                       value="{{ old('sort_order', $groupType->sort_order) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-500 @enderror"
                       placeholder="0">
                @error('sort_order')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Active Status -->
        <div class="mt-6">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', $groupType->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Active (visible and usable)</span>
            </label>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.group-types.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Update Group Type
            </button>
        </div>
    </form>
</div>

<script>
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
    });

    slugInput.addEventListener('input', function() {
        if (this.value) {
            delete this.dataset.autoGenerated;
        }
    });

    // Color picker sync
    const colorPicker = document.getElementById('color');
    const colorText = document.getElementById('color-text');

    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
    });
</script>
@endsection
