@extends('admin.layout')

@section('title', 'Create Registration Category')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.registration-categories.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create Registration Category</h1>
            <p class="text-gray-600 mt-1">Add a new registration category with pricing and validation rules</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.registration-categories.store') }}" method="POST">
        @csrf
        
        <!-- Basic Information -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="e.g., Standard Registration"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mobile Persona -->
                <div>
                    <label for="mobile_persona_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Mobile Persona <span class="text-red-500">*</span>
                    </label>
                    <select name="mobile_persona_id" 
                            id="mobile_persona_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('mobile_persona_id') border-red-500 @enderror"
                            required>
                        <option value="">Select a persona</option>
                        @foreach($personas as $persona)
                            <option value="{{ $persona->id }}" {{ old('mobile_persona_id') == $persona->id ? 'selected' : '' }}>
                                {{ $persona->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('mobile_persona_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mobile Persona Color -->
                <div>
                    <label for="mobile_persona_color" class="block text-sm font-medium text-gray-700 mb-2">
                        Mobile Persona Color
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="color" 
                               name="mobile_persona_color" 
                               id="mobile_persona_color" 
                               value="{{ old('mobile_persona_color', '#6366f1') }}"
                               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               id="mobile-persona-color-text"
                               value="{{ old('mobile_persona_color', '#6366f1') }}"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                               readonly>
                    </div>
                    @error('mobile_persona_color')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Badge Name -->
                <div class="md:col-span-2">
                    <label for="badge_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Name on Badge/Certificate
                    </label>
                    <input type="text" 
                           name="badge_name" 
                           id="badge_name" 
                           value="{{ old('badge_name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('badge_name') border-red-500 @enderror"
                           placeholder="Name to display on badge or certificate">
                    @error('badge_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Instructions</h2>
            <div class="space-y-6">
                <!-- Instructions Text -->
                <div>
                    <label for="instructions_text" class="block text-sm font-medium text-gray-700 mb-2">
                        Instructions Text
                    </label>
                    <textarea name="instructions_text" 
                              id="instructions_text" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('instructions_text') border-red-500 @enderror"
                              placeholder="Brief instructions for registrants">{{ old('instructions_text') }}</textarea>
                    @error('instructions_text')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instruction Description -->
                <div>
                    <label for="instruction_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Instruction Description
                    </label>
                    <textarea name="instruction_description" 
                              id="instruction_description" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('instruction_description') border-red-500 @enderror"
                              placeholder="Detailed description or additional instructions">{{ old('instruction_description') }}</textarea>
                    @error('instruction_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Pricing & Validity -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Pricing & Validity</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Valid From -->
                <div>
                    <label for="valid_from" class="block text-sm font-medium text-gray-700 mb-2">
                        Valid From <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="valid_from" 
                           id="valid_from" 
                           value="{{ old('valid_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('valid_from') border-red-500 @enderror"
                           required>
                    @error('valid_from')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Valid To -->
                <div>
                    <label for="valid_to" class="block text-sm font-medium text-gray-700 mb-2">
                        Valid To <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="valid_to" 
                           id="valid_to" 
                           value="{{ old('valid_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('valid_to') border-red-500 @enderror"
                           required>
                    @error('valid_to')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                        Price <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="price" 
                           id="price" 
                           value="{{ old('price', '0.00') }}"
                           step="0.01"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('price') border-red-500 @enderror"
                           required>
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Currency -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                        Currency
                    </label>
                    <input type="text" 
                           name="currency" 
                           id="currency" 
                           value="{{ old('currency', 'AED') }}"
                           maxlength="3"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('currency') border-red-500 @enderror"
                           placeholder="AED">
                    <p class="mt-1 text-xs text-gray-500">3-letter currency code (default: AED)</p>
                    @error('currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- VAT Percentage -->
                <div>
                    <label for="vat_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                        VAT Percentage
                    </label>
                    <input type="number" 
                           name="vat_percentage" 
                           id="vat_percentage" 
                           value="{{ old('vat_percentage', '5.00') }}"
                           step="0.01"
                           min="0"
                           max="100"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('vat_percentage') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Default: 5.00%</p>
                    @error('vat_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Show TRN -->
                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="show_trn" 
                               value="1"
                               {{ old('show_trn') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Show TRN (Tax Registration Number)</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Visibility & Limits -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Visibility & Limits</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Visible -->
                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="visible" 
                               value="1"
                               {{ old('visible', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Visible to registrants</span>
                    </label>
                </div>

                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                        Capacity
                    </label>
                    <input type="number" 
                           name="capacity" 
                           id="capacity" 
                           value="{{ old('capacity') }}"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('capacity') border-red-500 @enderror"
                           placeholder="Leave empty for unlimited">
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Minimum Items -->
                <div>
                    <label for="minimum_items" class="block text-sm font-medium text-gray-700 mb-2">
                        Minimum Items <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="minimum_items" 
                           id="minimum_items" 
                           value="{{ old('minimum_items', '0') }}"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('minimum_items') border-red-500 @enderror"
                           required>
                    @error('minimum_items')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Maximum Items -->
                <div>
                    <label for="maximum_items" class="block text-sm font-medium text-gray-700 mb-2">
                        Maximum Items
                    </label>
                    <input type="number" 
                           name="maximum_items" 
                           id="maximum_items" 
                           value="{{ old('maximum_items') }}"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('maximum_items') border-red-500 @enderror"
                           placeholder="Leave empty for unlimited">
                    @error('maximum_items')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Minimum Options -->
                <div>
                    <label for="minimum_options" class="block text-sm font-medium text-gray-700 mb-2">
                        Minimum Options <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="minimum_options" 
                           id="minimum_options" 
                           value="{{ old('minimum_options', '0') }}"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('minimum_options') border-red-500 @enderror"
                           required>
                    @error('minimum_options')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Maximum Options -->
                <div>
                    <label for="maximum_options" class="block text-sm font-medium text-gray-700 mb-2">
                        Maximum Options
                    </label>
                    <input type="number" 
                           name="maximum_options" 
                           id="maximum_options" 
                           value="{{ old('maximum_options') }}"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('maximum_options') border-red-500 @enderror"
                           placeholder="Leave empty for unlimited">
                    @error('maximum_options')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Professional/Student ID Requirements -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Professional/Student ID Requirements</h2>
            <div class="space-y-6">
                <!-- Need Professional/Student ID -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="need_professional_student_id" 
                               id="need_professional_student_id"
                               value="1"
                               {{ old('need_professional_student_id') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Require Professional / Student ID</span>
                    </label>
                </div>

                <!-- Professional/Student ID Message -->
                <div id="professional-id-message-section" class="hidden">
                    <label for="professional_student_id_message" class="block text-sm font-medium text-gray-700 mb-2">
                        Professional / Student ID Message
                    </label>
                    <textarea name="professional_student_id_message" 
                              id="professional_student_id_message" 
                              rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('professional_student_id_message') border-red-500 @enderror"
                              placeholder="Message to display when requesting ID">{{ old('professional_student_id_message') }}</textarea>
                    @error('professional_student_id_message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Membership Requirements -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Membership Requirements</h2>
            <div class="space-y-6">
                <!-- Need Membership ID -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="need_membership_id" 
                               id="need_membership_id"
                               value="1"
                               {{ old('need_membership_id') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Require Membership ID</span>
                    </label>
                </div>

                <div id="membership-section" class="hidden space-y-6">
                    <!-- Membership -->
                    <div>
                        <label for="membership_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Membership
                        </label>
                        <select name="membership_id" 
                                id="membership_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('membership_id') border-red-500 @enderror">
                            <option value="">Select a membership</option>
                            @foreach($memberships as $membership)
                                <option value="{{ $membership->id }}" {{ old('membership_id') == $membership->id ? 'selected' : '' }}>
                                    {{ $membership->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('membership_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Membership Not Found Message -->
                    <div>
                        <label for="membership_not_found_message" class="block text-sm font-medium text-gray-700 mb-2">
                            Membership Not Found Message
                        </label>
                        <textarea name="membership_not_found_message" 
                                  id="membership_not_found_message" 
                                  rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('membership_not_found_message') border-red-500 @enderror"
                                  placeholder="Message when membership ID is not found">{{ old('membership_not_found_message') }}</textarea>
                        @error('membership_not_found_message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Membership Invalid Message -->
                    <div>
                        <label for="membership_invalid_message" class="block text-sm font-medium text-gray-700 mb-2">
                            Membership Invalid Message
                        </label>
                        <textarea name="membership_invalid_message" 
                                  id="membership_invalid_message" 
                                  rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('membership_invalid_message') border-red-500 @enderror"
                                  placeholder="Message when membership ID is invalid">{{ old('membership_invalid_message') }}</textarea>
                        @error('membership_invalid_message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Protection -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Password Protection</h2>
            <div class="space-y-6">
                <!-- Needs Password -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="needs_password" 
                               id="needs_password"
                               value="1"
                               {{ old('needs_password') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Require Password for Registration</span>
                    </label>
                </div>

                <!-- Password -->
                <div id="password-section" class="hidden">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input type="text" 
                           name="password" 
                           id="password" 
                           value="{{ old('password') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-500 @enderror"
                           placeholder="Enter password required for registration">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Additional Settings</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sponsored -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="sponsored" 
                               value="1"
                               {{ old('sponsored') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Sponsored</span>
                    </label>
                </div>

                <!-- Send to DTCM -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="send_to_dtcm" 
                               value="1"
                               {{ old('send_to_dtcm') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Send to DTCM</span>
                    </label>
                </div>

                <!-- Pipelines -->
                <div class="md:col-span-2">
                    <label for="pipelines" class="block text-sm font-medium text-gray-700 mb-2">
                        Pipeline(s)
                    </label>
                    <input type="text" 
                           name="pipelines" 
                           id="pipelines" 
                           value="{{ old('pipelines') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('pipelines') border-red-500 @enderror"
                           placeholder="Comma-separated pipeline names">
                    @error('pipelines')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                        Color
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="color" 
                               name="color" 
                               id="color" 
                               value="{{ old('color', '#6366f1') }}"
                               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               id="color-text"
                               value="{{ old('color', '#6366f1') }}"
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
                           value="{{ old('sort_order', 0) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-500 @enderror"
                           placeholder="0">
                    @error('sort_order')
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
                          placeholder="Optional description for this category">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Is Active -->
        <div class="mb-8">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Active (visible and usable)</span>
            </label>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.registration-categories.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Create Category
            </button>
        </div>
    </form>
</div>

<script>
    // Sync color pickers with text inputs
    const mobilePersonaColorPicker = document.getElementById('mobile_persona_color');
    const mobilePersonaColorText = document.getElementById('mobile-persona-color-text');
    
    mobilePersonaColorPicker.addEventListener('input', function() {
        mobilePersonaColorText.value = this.value;
    });

    const colorPicker = document.getElementById('color');
    const colorText = document.getElementById('color-text');
    
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
    });

    // Toggle professional/student ID message section
    const needProfessionalIdCheckbox = document.getElementById('need_professional_student_id');
    const professionalIdMessageSection = document.getElementById('professional-id-message-section');

    needProfessionalIdCheckbox.addEventListener('change', function() {
        if (this.checked) {
            professionalIdMessageSection.classList.remove('hidden');
        } else {
            professionalIdMessageSection.classList.add('hidden');
        }
    });

    // Toggle membership section
    const needMembershipIdCheckbox = document.getElementById('need_membership_id');
    const membershipSection = document.getElementById('membership-section');

    needMembershipIdCheckbox.addEventListener('change', function() {
        if (this.checked) {
            membershipSection.classList.remove('hidden');
        } else {
            membershipSection.classList.add('hidden');
        }
    });

    // Toggle password section
    const needsPasswordCheckbox = document.getElementById('needs_password');
    const passwordSection = document.getElementById('password-section');

    needsPasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordSection.classList.remove('hidden');
        } else {
            passwordSection.classList.add('hidden');
        }
    });

    // Initialize sections on page load
    if (needProfessionalIdCheckbox.checked) {
        professionalIdMessageSection.classList.remove('hidden');
    }
    if (needMembershipIdCheckbox.checked) {
        membershipSection.classList.remove('hidden');
    }
    if (needsPasswordCheckbox.checked) {
        passwordSection.classList.remove('hidden');
    }
</script>
@endsection
