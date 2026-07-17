@extends('admin.layout')

@section('title', 'Industries')

@section('content')
<!-- Header Section -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Industries</h1>
        <p class="text-gray-600 mt-1">Manage industry categories for exhibitors</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-2">
        <button type="button"
                onclick="document.getElementById('bulkImportModal').classList.remove('hidden')"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition"
                data-testid="bulk-import-button">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Bulk Import
        </button>
        <a href="{{ route('admin.industries.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Industry
        </a>
    </div>
</div>

@if($industries->isEmpty())
    <!-- Empty State -->
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Industries Found</h3>
        <p class="text-gray-600 mb-4">Get started by creating your first industry or importing a list.</p>
        <div class="flex flex-col sm:flex-row gap-2 justify-center">
            <button type="button"
                    onclick="document.getElementById('bulkImportModal').classList.remove('hidden')"
                    class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Bulk Import
            </button>
            <a href="{{ route('admin.industries.create') }}"
               class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add First Industry
            </a>
        </div>
    </div>
@else
    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Slug
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Color
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Sort Order
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($industries as $industry)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $industry->name }}</div>
                            @if($industry->description)
                                <div class="text-sm text-gray-500">{{ Str::limit($industry->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 font-mono">{{ $industry->slug }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($industry->color)
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded border-2 border-gray-200" style="background-color: {{ $industry->color }}"></div>
                                    <span class="ml-2 text-sm text-gray-600 font-mono">{{ $industry->color }}</span>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $industry->sort_order }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="{{ route('admin.industries.toggle-active', $industry->hash) }}"
                                  method="POST"
                                  class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $industry->is_active ? 'bg-indigo-600' : 'bg-gray-200' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $industry->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('admin.industries.edit', $industry->hash) }}"
                                   class="text-indigo-600 hover:text-indigo-900 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.industries.destroy', $industry->hash) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this industry?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
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
        Total: {{ $industries->count() }} industry{{ $industries->count() !== 1 ? 's' : '' }}
    </div>
@endif

<!-- Bulk Import Modal -->
<div id="bulkImportModal"
     class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
     data-testid="bulk-import-modal">
    <div class="relative top-10 sm:top-16 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white mb-10">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Bulk Import Industries</h3>
            <button type="button"
                    onclick="document.getElementById('bulkImportModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600"
                    aria-label="Close">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <p class="text-sm text-gray-600 mb-4">
            Paste one industry name per line, or upload a TXT/CSV file. Duplicate names are skipped.
        </p>

        <form action="{{ route('admin.industries.bulk-import') }}"
              method="POST"
              enctype="multipart/form-data"
              data-testid="bulk-import-form">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="bulk_names" class="block text-sm font-medium text-gray-700 mb-2">
                        Paste industries
                    </label>
                    <textarea name="names"
                              id="bulk_names"
                              rows="8"
                              placeholder="Technology&#10;Healthcare&#10;Finance&#10;Education"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                              data-testid="bulk-import-names">{{ old('names') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Optional CSV format: name, or name,color in the first columns</p>
                </div>

                <div class="relative flex items-center">
                    <div class="flex-grow border-t border-gray-200"></div>
                    <span class="flex-shrink mx-3 text-xs text-gray-400 uppercase">or</span>
                    <div class="flex-grow border-t border-gray-200"></div>
                </div>

                <div>
                    <label for="import_file" class="block text-sm font-medium text-gray-700 mb-2">
                        Upload file (TXT or CSV)
                    </label>
                    <input type="file"
                           name="import_file"
                           id="import_file"
                           accept=".txt,.csv,text/plain,text/csv"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           data-testid="bulk-import-file">
                    <a href="{{ asset('samples/industries-sample.txt') }}"
                       download
                       class="mt-2 inline-flex items-center text-xs text-indigo-600 hover:text-indigo-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download Sample File
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="bulk_color" class="block text-sm font-medium text-gray-700 mb-2">
                            Default color
                        </label>
                        <div class="flex items-center space-x-3">
                            <input type="color"
                                   name="color"
                                   id="bulk_color"
                                   value="{{ old('color', '#6366f1') }}"
                                   class="h-10 w-16 border border-gray-300 rounded cursor-pointer">
                            <span id="bulk_color_text" class="text-sm text-gray-600 font-mono">{{ old('color', '#6366f1') }}</span>
                        </div>
                    </div>

                    <div>
                        <label for="bulk_sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                            Starting sort order
                        </label>
                        <input type="number"
                               name="sort_order"
                               id="bulk_sort_order"
                               value="{{ old('sort_order', 0) }}"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Mark imported industries as active</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button"
                        onclick="document.getElementById('bulkImportModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                        data-testid="bulk-import-submit">
                    Import Industries
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const colorPicker = document.getElementById('bulk_color');
        const colorText = document.getElementById('bulk_color_text');
        if (colorPicker && colorText) {
            colorPicker.addEventListener('input', function () {
                colorText.textContent = this.value;
            });
        }

        @if(old('names') || session('error'))
            document.getElementById('bulkImportModal')?.classList.remove('hidden');
        @endif
    })();
</script>
@endsection
