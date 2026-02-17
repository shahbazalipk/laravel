@extends('admin.layout')

@section('title', 'Edit Category')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Edit Category</h1>
    <p class="mt-2 text-gray-600">Update category information</p>
</div>

<div class="bg-white shadow rounded-lg p-6 max-w-2xl">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" name="name" id="name" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       value="{{ old('name', $category->name) }}">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="color" class="block text-sm font-medium text-gray-700">Color *</label>
                <div class="mt-1 flex items-center space-x-3">
                    <input type="color" name="color" id="color" required
                           class="h-10 w-20 rounded border-gray-300"
                           value="{{ old('color', $category->color) }}">
                    <input type="text" id="color-text" readonly
                           class="block w-32 rounded-md border-gray-300 bg-gray-50"
                           value="{{ old('color', $category->color) }}">
                </div>
                @error('color')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       value="{{ old('sort_order', $category->sort_order) }}">
                @error('sort_order')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Active
                </label>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('admin.categories.index') }}" 
               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Update Category
            </button>
        </div>
    </form>
</div>

<script>
    const colorInput = document.getElementById('color');
    const colorText = document.getElementById('color-text');
    colorInput.addEventListener('input', (e) => {
        colorText.value = e.target.value;
    });
</script>
@endsection
