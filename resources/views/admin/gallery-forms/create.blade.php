@extends('admin.layout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.gallery-settings.forms') }}" class="text-indigo-600 hover:text-indigo-800 text-sm mb-2 inline-block">
                ← Back to Forms
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Create Form</h1>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('admin.gallery-forms.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Form Name *</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Form Fields</label>
                    <div id="fields-container" class="space-y-3">
                        <!-- Fields will be added here dynamically -->
                    </div>
                    <button type="button" 
                            onclick="addField()"
                            class="mt-3 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm">
                        + Add Field
                    </button>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_default" 
                               value="1"
                               {{ old('is_default') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Set as default form</span>
                    </label>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.gallery-settings.forms') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md">
                        Create Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let fieldIndex = 0;

function addField() {
    const container = document.getElementById('fields-container');
    const fieldHtml = `
        <div class="flex gap-2 items-start p-3 border border-gray-200 rounded-md" id="field-${fieldIndex}">
            <div class="flex-1">
                <input type="text" 
                       name="fields[${fieldIndex}][label]" 
                       placeholder="Field Label"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md mb-2"
                       required>
                <select name="fields[${fieldIndex}][type]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="text">Text</option>
                    <option value="email">Email</option>
                    <option value="tel">Phone</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Select</option>
                    <option value="checkbox">Checkbox</option>
                </select>
                <label class="flex items-center mt-2">
                    <input type="checkbox" 
                           name="fields[${fieldIndex}][required]" 
                           value="1"
                           class="rounded border-gray-300 text-indigo-600">
                    <span class="ml-2 text-sm text-gray-700">Required</span>
                </label>
            </div>
            <button type="button" 
                    onclick="removeField(${fieldIndex})"
                    class="p-2 text-red-600 hover:bg-red-50 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', fieldHtml);
    fieldIndex++;
}

function removeField(index) {
    document.getElementById(`field-${index}`).remove();
}

// Add one field by default
addField();
</script>
@endsection
