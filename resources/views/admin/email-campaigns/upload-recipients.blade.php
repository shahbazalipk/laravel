@extends('admin.layout')

@section('title', 'Upload Recipients')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.email-campaigns.email-campaigns.show', $campaign) }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Upload Recipients</h1>
            <p class="text-gray-600 mt-1">Upload a CSV file with recipient data for {{ $campaign->name }}</p>
        </div>
    </div>
</div>

<!-- Instructions -->
<div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg mb-6">
    <h3 class="font-medium mb-2">CSV File Requirements:</h3>
    <ul class="text-sm space-y-1 list-disc list-inside">
        <li>File must be in CSV format (.csv)</li>
        <li>First row should contain column headers</li>
        <li>Must include an email column</li>
        <li>Recommended columns: email, first_name, last_name, company</li>
        <li>Maximum file size: 10MB</li>
    </ul>
</div>

<!-- Upload Form -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Step 1: Upload CSV File</h2>
    
    <form action="{{ route('admin.recipient-upload.store', $campaign) }}" 
          method="POST" 
          enctype="multipart/form-data"
          id="upload-form">
        @csrf
        
        <!-- File Upload Dropzone -->
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-500 transition"
             id="dropzone">
            <input type="file" 
                   name="csv_file" 
                   id="csv_file" 
                   accept=".csv"
                   class="hidden"
                   required>
            
            <div id="dropzone-content">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="text-gray-600 mb-2">
                    <button type="button" 
                            onclick="document.getElementById('csv_file').click()"
                            class="text-indigo-600 hover:text-indigo-900 font-medium">
                        Click to upload
                    </button>
                    or drag and drop
                </p>
                <p class="text-sm text-gray-500">CSV file up to 10MB</p>
            </div>
            
            <div id="file-info" class="hidden">
                <svg class="w-12 h-12 mx-auto text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-900 font-medium" id="file-name"></p>
                <p class="text-sm text-gray-500" id="file-size"></p>
                <button type="button" 
                        onclick="clearFile()"
                        class="mt-2 text-sm text-red-600 hover:text-red-900">
                    Remove file
                </button>
            </div>
        </div>
        
        @error('csv_file')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        
        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('admin.email-campaigns.email-campaigns.show', $campaign) }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    id="upload-btn"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                Upload & Preview
            </button>
        </div>
    </form>
</div>

<!-- Preview Section (shown after upload) -->
@if(isset($preview))
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Step 2: Map Columns</h2>
    
    <form action="{{ route('admin.recipient-upload.confirm', $campaign) }}" method="POST">
        @csrf
        
        <div class="mb-6">
            <p class="text-sm text-gray-600 mb-4">
                Map the columns from your CSV file to the recipient fields. The email field is required.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="email_column" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Column <span class="text-red-500">*</span>
                    </label>
                    <select name="mapping[email]" 
                            id="email_column"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            required>
                        <option value="">Select column</option>
                        @foreach($preview['headers'] as $header)
                        <option value="{{ $header }}" {{ strtolower($header) === 'email' ? 'selected' : '' }}>
                            {{ $header }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="first_name_column" class="block text-sm font-medium text-gray-700 mb-2">
                        First Name Column
                    </label>
                    <select name="mapping[first_name]" 
                            id="first_name_column"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Select column</option>
                        @foreach($preview['headers'] as $header)
                        <option value="{{ $header }}" {{ in_array(strtolower($header), ['first_name', 'firstname', 'first name']) ? 'selected' : '' }}>
                            {{ $header }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="last_name_column" class="block text-sm font-medium text-gray-700 mb-2">
                        Last Name Column
                    </label>
                    <select name="mapping[last_name]" 
                            id="last_name_column"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Select column</option>
                        @foreach($preview['headers'] as $header)
                        <option value="{{ $header }}" {{ in_array(strtolower($header), ['last_name', 'lastname', 'last name']) ? 'selected' : '' }}>
                            {{ $header }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="company_column" class="block text-sm font-medium text-gray-700 mb-2">
                        Company Column
                    </label>
                    <select name="mapping[company]" 
                            id="company_column"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Select column</option>
                        @foreach($preview['headers'] as $header)
                        <option value="{{ $header }}" {{ strtolower($header) === 'company' ? 'selected' : '' }}>
                            {{ $header }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Preview Data -->
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Preview (First 10 Rows)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            @foreach($preview['headers'] as $header)
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                {{ $header }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($preview['rows'] as $row)
                        <tr>
                            @foreach($preview['headers'] as $header)
                            <td class="px-4 py-2 text-sm text-gray-900">
                                {{ $row[$header] ?? '-' }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Validation Summary -->
        @if(isset($preview['validation']))
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Validation Summary</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600 mb-1">Valid Emails</div>
                    <div class="text-2xl font-bold text-green-900">{{ $preview['validation']['valid'] }}</div>
                </div>
                
                <div class="bg-red-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600 mb-1">Invalid Emails</div>
                    <div class="text-2xl font-bold text-red-900">{{ $preview['validation']['invalid'] }}</div>
                </div>
                
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600 mb-1">Duplicates</div>
                    <div class="text-2xl font-bold text-yellow-900">{{ $preview['validation']['duplicates'] }}</div>
                </div>
            </div>
            
            @if($preview['validation']['invalid'] > 0)
            <div class="mt-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                <p class="text-sm">
                    <strong>Warning:</strong> {{ $preview['validation']['invalid'] }} invalid email(s) will be skipped.
                </p>
            </div>
            @endif
            
            @if($preview['validation']['duplicates'] > 0)
            <div class="mt-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
                <p class="text-sm">
                    <strong>Note:</strong> {{ $preview['validation']['duplicates'] }} duplicate email(s) will be removed.
                </p>
            </div>
            @endif
        </div>
        @endif
        
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.email-campaigns.email-campaigns.show', $campaign) }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Confirm & Add Recipients
            </button>
        </div>
    </form>
</div>
@endif

<!-- Sample CSV Template -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Need a Template?</h2>
    <p class="text-sm text-gray-600 mb-4">
        Download our sample CSV template to see the correct format.
    </p>
    
    <a href="#" 
       class="inline-flex items-center text-indigo-600 hover:text-indigo-900 text-sm font-medium">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Download Sample CSV Template
    </a>
</div>
@endsection

@section('scripts')
<script>
    const fileInput = document.getElementById('csv_file');
    const dropzone = document.getElementById('dropzone');
    const dropzoneContent = document.getElementById('dropzone-content');
    const fileInfo = document.getElementById('file-info');
    const uploadBtn = document.getElementById('upload-btn');
    
    // File input change
    fileInput.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            displayFile(this.files[0]);
        }
    });
    
    // Drag and drop
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-indigo-500', 'bg-indigo-50');
    });
    
    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-indigo-500', 'bg-indigo-50');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-indigo-500', 'bg-indigo-50');
        
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].name.endsWith('.csv')) {
            fileInput.files = files;
            displayFile(files[0]);
        } else {
            alert('Please upload a CSV file');
        }
    });
    
    function displayFile(file) {
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        
        dropzoneContent.classList.add('hidden');
        fileInfo.classList.remove('hidden');
        uploadBtn.disabled = false;
    }
    
    function clearFile() {
        fileInput.value = '';
        dropzoneContent.classList.remove('hidden');
        fileInfo.classList.add('hidden');
        uploadBtn.disabled = true;
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
</script>
@endsection
