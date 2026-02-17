@extends('admin.layout')

@section('title', 'Edit Email Template')

@push('head')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script src="{{ asset('js/email-template-editor.js') }}"></script>
@endpush

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.email-campaigns.email-templates.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Email Template</h1>
            <p class="text-gray-600 mt-1">Update template information</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.email-campaigns.email-templates.update', $template) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Template Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $template->name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                       placeholder="e.g., Event Invitation"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <select name="category" 
                        id="category"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('category') border-red-500 @enderror"
                        required>
                    <option value="">Select a category</option>
                    <option value="invitation" {{ old('category', $template->category) === 'invitation' ? 'selected' : '' }}>Invitation</option>
                    <option value="reminder" {{ old('category', $template->category) === 'reminder' ? 'selected' : '' }}>Reminder</option>
                    <option value="confirmation" {{ old('category', $template->category) === 'confirmation' ? 'selected' : '' }}>Confirmation</option>
                    <option value="update" {{ old('category', $template->category) === 'update' ? 'selected' : '' }}>Update</option>
                    <option value="thank_you" {{ old('category', $template->category) === 'thank_you' ? 'selected' : '' }}>Thank You</option>
                    <option value="custom" {{ old('category', $template->category) === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
                @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Subject -->
        <div class="mt-6">
            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                Email Subject <span class="text-red-500">*</span>
            </label>
            <div class="flex items-start space-x-2">
                <input type="text" 
                       name="subject" 
                       id="subject" 
                       value="{{ old('subject', $template->subject) }}"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('subject') border-red-500 @enderror"
                       placeholder="e.g., You're Invited to @{{event_name}}"
                       required>
                <button type="button" 
                        onclick="toggleMergeCodePicker('subject')"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    Insert Code
                </button>
            </div>
            @error('subject')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- HTML Content -->
        <div class="mt-6">
            <label for="html_content" class="block text-sm font-medium text-gray-700 mb-2">
                HTML Content <span class="text-red-500">*</span>
            </label>
            <textarea name="html_content" 
                      id="html_content" 
                      class="@error('html_content') border-red-500 @enderror"
                      required>{{ old('html_content', $template->html_content) }}</textarea>
            @error('html_content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Use the "Merge Code" button in the toolbar to insert personalization codes.</p>
        </div>

        <!-- Text Content -->
        <div class="mt-6">
            <label for="text_content" class="block text-sm font-medium text-gray-700 mb-2">
                Plain Text Content
            </label>
            <textarea name="text_content" 
                      id="text_content" 
                      rows="8"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm @error('text_content') border-red-500 @enderror"
                      placeholder="Optional plain text version for email clients that don't support HTML">{{ old('text_content', $template->text_content) }}</textarea>
            @error('text_content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
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
                      placeholder="Optional description for internal use">{{ old('description', $template->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Is Active -->
        <div class="mt-6">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Active (available for use in campaigns)</span>
            </label>
        </div>

        <!-- Usage Info -->
        @if($template->usage_count > 0)
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm text-blue-800">
                        This template is currently used by <strong>{{ $template->usage_count }}</strong> campaign{{ $template->usage_count !== 1 ? 's' : '' }}.
                        Changes will not affect existing campaigns.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="mt-8 flex justify-between items-center">
            <button type="button" 
                    onclick="previewTemplate()"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Preview Template
            </button>
            <div class="flex space-x-3">
                <a href="{{ route('admin.email-campaigns.email-templates.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Update Template
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Merge Code Picker Modal -->
<div id="mergeCodeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Insert Merge Code</h3>
            <button onclick="closeMergeCodePicker()" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="space-y-2 max-h-96 overflow-y-auto">
            @foreach($mergeCodes as $category => $codes)
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ ucfirst($category) }}</h4>
                    @foreach($codes as $code => $description)
                        <button type="button"
                                onclick="insertMergeCode('{{ $code }}')"
                                class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded transition">
                            <div class="text-sm font-mono text-indigo-600">{!! '{{' . $code . '}}' !!}</div>
                            <div class="text-xs text-gray-500">{{ $description }}</div>
                        </button>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Template Preview</h3>
            <button onclick="closePreview()" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="previewContent" class="border rounded-lg p-4 bg-gray-50">
            <!-- Preview will be loaded here -->
        </div>
    </div>
</div>

<script>
// Initialize Email Template Editor
document.addEventListener('DOMContentLoaded', function() {
    const mergeCodes = @json($mergeCodes);
    
    emailTemplateEditor = new EmailTemplateEditor({
        contentField: 'html_content',
        subjectField: 'subject',
        autoSaveInterval: 30000,
        autoSaveKey: 'email_template_draft_{{ $template->id }}',
        mergeCodes: mergeCodes
    });
});

// Subject field merge code picker
let currentField = null;

function toggleMergeCodePicker(fieldId) {
    currentField = fieldId;
    document.getElementById('mergeCodeModal').classList.remove('hidden');
}

function closeMergeCodePicker() {
    document.getElementById('mergeCodeModal').classList.add('hidden');
    currentField = null;
}

function insertMergeCode(code) {
    if (!currentField) return;
    
    const field = document.getElementById(currentField);
    const mergeCode = '{{' + code + '}}';
    
    if (field.tagName === 'INPUT') {
        const start = field.selectionStart;
        const end = field.selectionEnd;
        const text = field.value;
        field.value = text.substring(0, start) + mergeCode + text.substring(end);
        field.selectionStart = field.selectionEnd = start + mergeCode.length;
        field.focus();
    }
    
    closeMergeCodePicker();
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('mergeCodeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMergeCodePicker();
    }
});

document.getElementById('previewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});
</script>
@endsection
