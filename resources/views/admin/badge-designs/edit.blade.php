@extends('admin.layout')

@section('title', 'Edit Badge Design')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.badge-designs.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Badge Design</h1>
            <p class="text-gray-600 mt-1">{{ $badgeDesign->name }}</p>
        </div>
    </div>
</div>

<form action="{{ route('admin.badge-designs.update', $badgeDesign) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Settings Panel -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Design Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $badgeDesign->name) }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="2"
                                  class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $badgeDesign->description) }}</textarea>
                    </div>

                    <div class="flex items-center gap-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_default" value="1" {{ old('is_default', $badgeDesign->is_default) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Set as Default Design</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $badgeDesign->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Layout Settings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Layout</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="size" class="block text-sm font-medium text-gray-700 mb-2">Badge Size</label>
                        <select name="size" id="size" class="w-full rounded-lg border-gray-300">
                            <option value="4x6" {{ old('size', $badgeDesign->size) == '4x6' ? 'selected' : '' }}>4" x 6"</option>
                            <option value="3x4" {{ old('size') == '3x4' ? 'selected' : '' }}>3" x 4"</option>
                            <option value="custom" {{ old('size') == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>

                    <div>
                        <label for="orientation" class="block text-sm font-medium text-gray-700 mb-2">Orientation</label>
                        <select name="orientation" id="orientation" class="w-full rounded-lg border-gray-300">
                            <option value="portrait" {{ old('orientation', $badgeDesign->orientation) == 'portrait' ? 'selected' : '' }}>Portrait</option>
                            <option value="landscape" {{ old('orientation') == 'landscape' ? 'selected' : '' }}>Landscape</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Header Settings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Header</h2>
                
                <div class="space-y-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_header" value="1" {{ old('show_header', $badgeDesign->show_header) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">Show Header</span>
                    </label>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Background Color</label>
                            <input type="color" name="header_bg_color" value="{{ old('header_bg_color', $badgeDesign->header_bg_color) }}"
                                   class="w-full h-10 rounded border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Text Color</label>
                            <input type="color" name="header_text_color" value="{{ old('header_text_color', $badgeDesign->header_text_color) }}"
                                   class="w-full h-10 rounded border-gray-300">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="show_event_logo" value="1" {{ old('show_event_logo', $badgeDesign->show_event_logo) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Show Event Logo</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="show_event_name" value="1" {{ old('show_event_name', $badgeDesign->show_event_name) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Show Event Name</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="show_event_dates" value="1" {{ old('show_event_dates', $badgeDesign->show_event_dates) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Show Event Dates</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Content Settings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Content</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="show_profile_picture" value="1" {{ old('show_profile_picture', $badgeDesign->show_profile_picture) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Show Profile Picture</span>
                        </label>
                        
                        <select name="profile_picture_shape" class="rounded-lg border-gray-300 text-sm">
                            <option value="circle" {{ old('profile_picture_shape', $badgeDesign->profile_picture_shape) == 'circle' ? 'selected' : '' }}>Circle</option>
                            <option value="square" {{ old('profile_picture_shape') == 'square' ? 'selected' : '' }}>Square</option>
                            <option value="rounded" {{ old('profile_picture_shape') == 'rounded' ? 'selected' : '' }}>Rounded</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="show_name" value="1" {{ old('show_name', $badgeDesign->show_name) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Show Name</span>
                        </label>
                        
                        <select name="name_font_size" class="rounded-lg border-gray-300 text-sm">
                            <option value="2xl" {{ old('name_font_size') == '2xl' ? 'selected' : '' }}>Small</option>
                            <option value="3xl" {{ old('name_font_size', $badgeDesign->name_font_size) == '3xl' ? 'selected' : '' }}>Medium</option>
                            <option value="4xl" {{ old('name_font_size') == '4xl' ? 'selected' : '' }}>Large</option>
                        </select>
                    </div>

                    <label class="flex items-center">
                        <input type="checkbox" name="show_job_title" value="1" {{ old('show_job_title', $badgeDesign->show_job_title) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">Show Job Title</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" name="show_company" value="1" {{ old('show_company', $badgeDesign->show_company) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">Show Company</span>
                    </label>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="show_category" value="1" {{ old('show_category', $badgeDesign->show_category) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Show Category</span>
                        </label>
                        
                        <select name="category_style" class="rounded-lg border-gray-300 text-sm">
                            <option value="badge" {{ old('category_style', $badgeDesign->category_style) == 'badge' ? 'selected' : '' }}>Badge Style</option>
                            <option value="text" {{ old('category_style') == 'text' ? 'selected' : '' }}>Plain Text</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- QR Code Settings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">QR Code</h2>
                
                <div class="space-y-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_qr_code" value="1" {{ old('show_qr_code', $badgeDesign->show_qr_code) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">Show QR Code</span>
                    </label>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Size</label>
                            <select name="qr_code_size" class="w-full rounded-lg border-gray-300">
                                <option value="24" {{ old('qr_code_size') == '24' ? 'selected' : '' }}>Small (96px)</option>
                                <option value="32" {{ old('qr_code_size', $badgeDesign->qr_code_size) == '32' ? 'selected' : '' }}>Medium (128px)</option>
                                <option value="40" {{ old('qr_code_size') == '40' ? 'selected' : '' }}>Large (160px)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                            <select name="qr_code_position" class="w-full rounded-lg border-gray-300">
                                <option value="left" {{ old('qr_code_position') == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="center" {{ old('qr_code_position', $badgeDesign->qr_code_position) == 'center' ? 'selected' : '' }}>Center</option>
                                <option value="right" {{ old('qr_code_position') == 'right' ? 'selected' : '' }}>Right</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Settings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Footer</h2>
                
                <div class="space-y-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_footer" value="1" {{ old('show_footer', $badgeDesign->show_footer) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">Show Footer</span>
                    </label>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Background Color</label>
                        <input type="color" name="footer_bg_color" value="{{ old('footer_bg_color', $badgeDesign->footer_bg_color) }}"
                               class="w-full h-10 rounded border-gray-300">
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="show_registration_number" value="1" {{ old('show_registration_number', $badgeDesign->show_registration_number) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Show Registration Number</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="show_location" value="1" {{ old('show_location', $badgeDesign->show_location) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Show Location</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="show_website" value="1" {{ old('show_website', $badgeDesign->show_website) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">Show Website</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Border Settings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Border</h2>
                
                <div class="space-y-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_border" value="1" {{ old('show_border', $badgeDesign->show_border) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">Show Border</span>
                    </label>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Border Color</label>
                            <input type="color" name="border_color" value="{{ old('border_color', $badgeDesign->border_color) }}"
                                   class="w-full h-10 rounded border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Border Width</label>
                            <select name="border_width" class="w-full rounded-lg border-gray-300">
                                <option value="2" {{ old('border_width') == '2' ? 'selected' : '' }}>Thin (2px)</option>
                                <option value="4" {{ old('border_width', $badgeDesign->border_width) == '4' ? 'selected' : '' }}>Medium (4px)</option>
                                <option value="8" {{ old('border_width') == '8' ? 'selected' : '' }}>Thick (8px)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Live Preview</h2>
                
                <!-- Badge Preview -->
                <div id="badge-preview" class="bg-gray-100 p-4 rounded-lg">
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden mx-auto" style="width: 200px; height: 300px; font-size: 6px;">
                        <!-- Header -->
                        <div id="preview-header" class="text-white p-3 text-center" style="background-color: #4F46E5;">
                            <div id="preview-event-logo" class="mb-1">
                                <svg class="w-6 h-6 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"></path>
                                </svg>
                            </div>
                            <h3 id="preview-event-name" class="font-bold" style="font-size: 10px;">Sample Event 2026</h3>
                            <p id="preview-event-dates" style="font-size: 7px;">Feb 19-21, 2026</p>
                        </div>

                        <!-- Content -->
                        <div class="p-3 text-center">
                            <!-- Profile Picture -->
                            <div id="preview-profile-picture" class="mb-2">
                                <div class="w-16 h-16 rounded-full mx-auto bg-gray-300 flex items-center justify-center">
                                    <span class="text-2xl text-gray-600">JD</span>
                                </div>
                            </div>

                            <!-- Name -->
                            <h2 id="preview-name" class="font-bold text-gray-900 mb-1" style="font-size: 12px;">John Doe</h2>
                            
                            <!-- Job Title -->
                            <p id="preview-job-title" class="text-gray-700" style="font-size: 8px;">Senior Developer</p>
                            
                            <!-- Company -->
                            <p id="preview-company" class="font-semibold text-gray-800" style="font-size: 8px;">Tech Company Inc.</p>

                            <!-- Category -->
                            <div id="preview-category" class="mt-2">
                                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full" style="font-size: 6px;">VIP Pass</span>
                            </div>

                            <!-- QR Code -->
                            <div id="preview-qr-code" class="mt-2">
                                <div class="w-16 h-16 mx-auto bg-gray-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Registration Number -->
                            <div id="preview-reg-number" class="mt-2">
                                <p class="text-gray-500 font-mono" style="font-size: 6px;">REG-12345</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div id="preview-footer" class="p-2 text-center border-t" style="background-color: #F3F4F6; font-size: 6px;">
                            <p id="preview-location" class="text-gray-600">Dubai, UAE</p>
                            <p id="preview-website" class="text-gray-600">www.event.com</p>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-4 text-center">Preview updates as you change settings</p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('admin.badge-designs.index') }}" 
           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Cancel
        </a>
        <button type="submit" 
                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
            Update Design
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Size and Orientation updates
    document.querySelector('[name="size"]').addEventListener('change', function() {
        const preview = document.querySelector('#badge-preview > div');
        if (this.value === '4x6') {
            preview.style.width = '200px';
            preview.style.height = '300px';
        } else if (this.value === '3x4') {
            preview.style.width = '150px';
            preview.style.height = '200px';
        }
    });

    document.querySelector('[name="orientation"]').addEventListener('change', function() {
        const preview = document.querySelector('#badge-preview > div');
        const currentWidth = preview.style.width;
        const currentHeight = preview.style.height;
        
        if (this.value === 'landscape') {
            preview.style.width = currentHeight;
            preview.style.height = currentWidth;
        } else {
            // Reset to portrait
            const size = document.querySelector('[name="size"]').value;
            if (size === '4x6') {
                preview.style.width = '200px';
                preview.style.height = '300px';
            } else if (size === '3x4') {
                preview.style.width = '150px';
                preview.style.height = '200px';
            }
        }
    });

    // Header updates
    document.querySelector('[name="show_header"]').addEventListener('change', function() {
        document.getElementById('preview-header').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="header_bg_color"]').addEventListener('input', function() {
        document.getElementById('preview-header').style.backgroundColor = this.value;
    });

    document.querySelector('[name="header_text_color"]').addEventListener('input', function() {
        document.getElementById('preview-header').style.color = this.value;
    });

    document.querySelector('[name="show_event_logo"]').addEventListener('change', function() {
        document.getElementById('preview-event-logo').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="show_event_name"]').addEventListener('change', function() {
        document.getElementById('preview-event-name').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="show_event_dates"]').addEventListener('change', function() {
        document.getElementById('preview-event-dates').style.display = this.checked ? 'block' : 'none';
    });

    // Content updates
    document.querySelector('[name="show_profile_picture"]').addEventListener('change', function() {
        document.getElementById('preview-profile-picture').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="profile_picture_shape"]').addEventListener('change', function() {
        const pic = document.querySelector('#preview-profile-picture > div');
        pic.classList.remove('rounded-full', 'rounded-lg', 'rounded-none');
        if (this.value === 'circle') pic.classList.add('rounded-full');
        else if (this.value === 'rounded') pic.classList.add('rounded-lg');
    });

    document.querySelector('[name="show_name"]').addEventListener('change', function() {
        document.getElementById('preview-name').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="name_font_size"]').addEventListener('change', function() {
        const sizes = { '2xl': '10px', '3xl': '12px', '4xl': '14px' };
        document.getElementById('preview-name').style.fontSize = sizes[this.value];
    });

    document.querySelector('[name="show_job_title"]').addEventListener('change', function() {
        document.getElementById('preview-job-title').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="show_company"]').addEventListener('change', function() {
        document.getElementById('preview-company').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="show_category"]').addEventListener('change', function() {
        document.getElementById('preview-category').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="category_style"]').addEventListener('change', function() {
        const badge = document.querySelector('#preview-category span');
        if (this.value === 'text') {
            badge.className = 'text-gray-700';
            badge.style.fontSize = '8px';
        } else {
            badge.className = 'px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full';
            badge.style.fontSize = '6px';
        }
    });

    // QR Code updates
    document.querySelector('[name="show_qr_code"]').addEventListener('change', function() {
        document.getElementById('preview-qr-code').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="qr_code_size"]').addEventListener('change', function() {
        const sizes = { '24': '12', '32': '16', '40': '20' };
        const qr = document.querySelector('#preview-qr-code > div');
        qr.className = `w-${sizes[this.value]} h-${sizes[this.value]} mx-auto bg-gray-200 flex items-center justify-center`;
    });

    document.querySelector('[name="qr_code_position"]').addEventListener('change', function() {
        const qr = document.getElementById('preview-qr-code');
        qr.className = this.value === 'center' ? 'mt-2' : this.value === 'left' ? 'mt-2 text-left' : 'mt-2 text-right';
    });

    // Footer updates
    document.querySelector('[name="show_footer"]').addEventListener('change', function() {
        document.getElementById('preview-footer').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="footer_bg_color"]').addEventListener('input', function() {
        document.getElementById('preview-footer').style.backgroundColor = this.value;
    });

    document.querySelector('[name="show_registration_number"]').addEventListener('change', function() {
        document.getElementById('preview-reg-number').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="show_location"]').addEventListener('change', function() {
        document.getElementById('preview-location').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('[name="show_website"]').addEventListener('change', function() {
        document.getElementById('preview-website').style.display = this.checked ? 'block' : 'none';
    });

    // Border updates
    document.querySelector('[name="show_border"]').addEventListener('change', function() {
        const preview = document.querySelector('#badge-preview > div');
        if (this.checked) {
            preview.style.border = document.querySelector('[name="border_width"]').value + 'px solid ' + document.querySelector('[name="border_color"]').value;
        } else {
            preview.style.border = 'none';
        }
    });

    document.querySelector('[name="border_color"]').addEventListener('input', function() {
        if (document.querySelector('[name="show_border"]').checked) {
            const preview = document.querySelector('#badge-preview > div');
            preview.style.borderColor = this.value;
        }
    });

    document.querySelector('[name="border_width"]').addEventListener('change', function() {
        if (document.querySelector('[name="show_border"]').checked) {
            const preview = document.querySelector('#badge-preview > div');
            preview.style.borderWidth = this.value + 'px';
        }
    });

    // Initialize border
    const preview = document.querySelector('#badge-preview > div');
    preview.style.border = '4px solid #4F46E5';
});
</script>
@endsection
