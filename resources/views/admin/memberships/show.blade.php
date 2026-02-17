@extends('admin.layout')

@section('title', 'Membership Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.memberships.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center">
                <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $membership->color }}"></div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $membership->name }}</h1>
            </div>
            <p class="text-gray-600 mt-1">Manage membership codes and verification data</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.memberships.edit', $membership) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Membership
            </a>
        </div>
    </div>

    <!-- Membership Info Card -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Verification Type</h3>
                @if($membership->verification_type === 'upload_file')
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload File
                    </span>
                @else
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Third Party API
                    </span>
                @endif
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Status</h3>
                @if($membership->is_active)
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Active
                    </span>
                @else
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                        Inactive
                    </span>
                @endif
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Total Codes</h3>
                <p class="text-lg font-semibold text-gray-900">{{ $membership->codes()->count() }}</p>
            </div>
        </div>
        @if($membership->description)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-sm font-medium text-gray-500 mb-1">Description</h3>
                <p class="text-sm text-gray-700">{{ $membership->description }}</p>
            </div>
        @endif
    </div>
</div>

<!-- Action Buttons -->
<div class="mb-6 flex justify-between items-center">
    <div class="flex space-x-3">
        <!-- Add Single Code Button -->
        <button onclick="document.getElementById('addCodeModal').classList.remove('hidden')"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Code
        </button>

        <!-- Import Codes Button -->
        <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Import Codes
        </button>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.memberships.show', $membership) }}" class="flex items-center">
        <input type="text" 
               name="search" 
               value="{{ $search ?? '' }}"
               placeholder="Search codes..." 
               class="px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        <button type="submit" 
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-r-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </button>
    </form>
</div>

<!-- Codes Table -->
@if($codes->isEmpty())
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Membership Codes</h3>
        <p class="text-gray-600 mb-4">Add codes manually or import them from a file.</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Allowed Usage
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Used
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created At
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Updated At
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($codes as $code)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $code->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono font-medium text-gray-900">{{ $code->code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $code->allowed_usage }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $code->used }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($code->status === 'active')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @elseif($code->status === 'inactive')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Expired
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $code->created_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $code->updated_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <form action="{{ route('admin.memberships.codes.destroy', [$membership, $code->id]) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this code?');"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $codes->links() }}
    </div>

    <div class="mt-4 text-sm text-gray-600">
        Showing {{ $codes->firstItem() ?? 0 }} to {{ $codes->lastItem() ?? 0 }} of {{ $codes->total() }} code{{ $codes->total() !== 1 ? 's' : '' }}
    </div>
@endif

<!-- Add Code Modal -->
<div id="addCodeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Add Membership Code</h3>
            <button onclick="document.getElementById('addCodeModal').classList.add('hidden')" 
                    class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('admin.memberships.codes.store', $membership) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                        Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="code" 
                           id="code" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Enter membership code">
                </div>

                <div>
                    <label for="allowed_usage" class="block text-sm font-medium text-gray-700 mb-2">
                        Allowed Usage <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="allowed_usage" 
                           id="allowed_usage" 
                           value="1"
                           min="1"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" 
                            id="status"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        onclick="document.getElementById('addCodeModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Add Code
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Import Membership Codes</h3>
            <button onclick="document.getElementById('importModal').classList.add('hidden')" 
                    class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('admin.memberships.codes.import', $membership) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="import_file" class="block text-sm font-medium text-gray-700 mb-2">
                        File (TXT or CSV) <span class="text-red-500">*</span>
                    </label>
                    <input type="file" 
                           name="import_file" 
                           id="import_file" 
                           accept=".txt,.csv"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500">One code per line</p>
                    <a href="{{ asset('samples/membership-codes-sample.txt') }}" 
                       download
                       class="mt-2 inline-flex items-center text-xs text-indigo-600 hover:text-indigo-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download Sample File
                    </a>
                </div>

                <div>
                    <label for="import_allowed_usage" class="block text-sm font-medium text-gray-700 mb-2">
                        Allowed Usage (for all codes) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="allowed_usage" 
                           id="import_allowed_usage" 
                           value="1"
                           min="1"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        onclick="document.getElementById('importModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Import Codes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
