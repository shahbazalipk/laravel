@extends('admin.layout')

@section('title', 'Registration Category Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.registration-categories.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $registrationCategory->name }}</h1>
                <p class="text-gray-600 mt-1">Registration Category Details</p>
            </div>
        </div>
        <a href="{{ route('admin.registration-categories.edit', $registrationCategory) }}" 
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Category
        </a>
    </div>
</div>

<!-- Category Information -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Basic Info Card -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $registrationCategory->name }}</dd>
            </div>
            @if($registrationCategory->badge_name)
            <div>
                <dt class="text-sm font-medium text-gray-500">Badge Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $registrationCategory->badge_name }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                    @if($registrationCategory->is_active)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                    @endif
                </dd>
            </div>
            @if($registrationCategory->color)
            <div>
                <dt class="text-sm font-medium text-gray-500">Color</dt>
                <dd class="mt-1 flex items-center">
                    <div class="w-6 h-6 rounded border border-gray-300 mr-2" style="background-color: {{ $registrationCategory->color }}"></div>
                    <span class="text-sm text-gray-900 font-mono">{{ $registrationCategory->color }}</span>
                </dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Pricing Card -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Pricing & Validity</h2>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Price</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $registrationCategory->currency }} {{ number_format($registrationCategory->price, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">VAT</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $registrationCategory->vat_percentage }}%</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Valid From</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $registrationCategory->valid_from->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Valid To</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $registrationCategory->valid_to->format('M d, Y') }}</dd>
            </div>
            @if($registrationCategory->capacity)
            <div>
                <dt class="text-sm font-medium text-gray-500">Capacity</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $registrationCategory->capacity }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Relationships Card -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Relationships</h2>
        <dl class="space-y-3">
            @if($registrationCategory->mobilePersona)
            <div>
                <dt class="text-sm font-medium text-gray-500">Mobile Persona</dt>
                <dd class="mt-1 flex items-center">
                    @if($registrationCategory->mobile_persona_color)
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $registrationCategory->mobile_persona_color }}"></div>
                    @endif
                    <span class="text-sm text-gray-900">{{ $registrationCategory->mobilePersona->name }}</span>
                </dd>
            </div>
            @endif
            @if($registrationCategory->membership)
            <div>
                <dt class="text-sm font-medium text-gray-500">Membership</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $registrationCategory->membership->name }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Visible</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $registrationCategory->visible ? 'Yes' : 'No' }}</dd>
            </div>
        </dl>
    </div>
</div>

<!-- Category Types Section -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Category Types</h2>
        @if($availableTypes->isNotEmpty())
        <button onclick="document.getElementById('attach-modal').classList.remove('hidden')" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm flex items-center transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Attach Type
        </button>
        @endif
    </div>

    @if($registrationCategory->categoryTypes->isEmpty())
        <div class="text-center py-8">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <p class="text-gray-500">No category types attached</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attached At</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($registrationCategory->categoryTypes as $type)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($type->color)
                                    <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $type->color }}"></div>
                                @endif
                                <span class="text-sm font-medium text-gray-900">{{ $type->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">{{ $type->slug ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($type->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">{{ $type->pivot->created_at ? $type->pivot->created_at->format('M d, Y H:i') : '-' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <form action="{{ route('admin.registration-categories.types.detach', [$registrationCategory, $type]) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to detach this category type?');"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 transition text-sm">
                                    Detach
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Attach Type Modal -->
<div id="attach-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Attach Category Type</h3>
            <button onclick="document.getElementById('attach-modal').classList.add('hidden')" 
                    class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('admin.registration-categories.types.attach', $registrationCategory) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="category_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Category Type
                </label>
                <select name="category_type_id" 
                        id="category_type_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        required>
                    <option value="">Choose a type...</option>
                    @foreach($availableTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="document.getElementById('attach-modal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Attach
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
