@extends('admin.layout')

@section('title', 'Export Registrations')

@section('content')
<!-- Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Export Registrations</h1>
    <p class="text-gray-600 mt-1">Export registration data in various formats</p>
</div>

<!-- Export Options -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- CSV Export -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center mb-4">
            <div class="bg-green-100 p-3 rounded-lg mr-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">CSV Export</h3>
                <p class="text-sm text-gray-600">Export to comma-separated values</p>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-4">
            Best for spreadsheet applications like Excel, Google Sheets, or data analysis tools.
        </p>
        <form action="{{ route('admin.registrations.export') }}" method="POST">
            @csrf
            <input type="hidden" name="format" value="csv">
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                Export as CSV
            </button>
        </form>
    </div>

    <!-- Excel Export -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center mb-4">
            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Excel Export</h3>
                <p class="text-sm text-gray-600">Export to Microsoft Excel format</p>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-4">
            Includes formatting, multiple sheets, and is optimized for Microsoft Excel.
        </p>
        <form action="{{ route('admin.registrations.export') }}" method="POST">
            @csrf
            <input type="hidden" name="format" value="xlsx">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                Export as Excel
            </button>
        </form>
    </div>

    <!-- PDF Export -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center mb-4">
            <div class="bg-red-100 p-3 rounded-lg mr-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">PDF Export</h3>
                <p class="text-sm text-gray-600">Export to PDF document</p>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-4">
            Professional formatted document, ideal for printing or sharing reports.
        </p>
        <form action="{{ route('admin.registrations.export') }}" method="POST">
            @csrf
            <input type="hidden" name="format" value="pdf">
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                Export as PDF
            </button>
        </form>
    </div>

    <!-- JSON Export -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center mb-4">
            <div class="bg-purple-100 p-3 rounded-lg mr-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">JSON Export</h3>
                <p class="text-sm text-gray-600">Export to JSON format</p>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-4">
            Structured data format, perfect for API integrations and developers.
        </p>
        <form action="{{ route('admin.registrations.export') }}" method="POST">
            @csrf
            <input type="hidden" name="format" value="json">
            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                Export as JSON
            </button>
        </form>
    </div>
</div>

<!-- Export Filters -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Export Filters</h3>
    <p class="text-sm text-gray-600 mb-4">Customize what data to include in your export</p>
    
    <form action="{{ route('admin.registrations.export') }}" method="POST" id="exportForm">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Format Selection -->
            <div>
                <label for="format" class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                <select name="format" id="format" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                    <option value="csv">CSV</option>
                    <option value="xlsx">Excel (XLSX)</option>
                    <option value="pdf">PDF</option>
                    <option value="json">JSON</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">Registration Status</label>
                <select name="status_filter" id="status_filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category_filter" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category_filter" id="category_filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Registration Type Filter -->
            <div>
                <label for="type_filter" class="block text-sm font-medium text-gray-700 mb-2">Registration Type</label>
                <select name="type_filter" id="type_filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="individual">Individual</option>
                    <option value="exhibitor">Exhibitor</option>
                    <option value="group">Group</option>
                </select>
            </div>

            <!-- Check-in Status -->
            <div>
                <label for="checkin_filter" class="block text-sm font-medium text-gray-700 mb-2">Check-in Status</label>
                <select name="checkin_filter" id="checkin_filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="checked_in">Checked In</option>
                    <option value="not_checked_in">Not Checked In</option>
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Registration Date From</label>
                <input type="date" 
                       name="date_from" 
                       id="date_from" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Registration Date To</label>
                <input type="date" 
                       name="date_to" 
                       id="date_to" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
        </div>

        <!-- Fields to Include -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Fields to Include</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="basic_info" checked class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Basic Info</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="contact" checked class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Contact Details</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="company" checked class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Company Info</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="payment" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Payment Info</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="checkin" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Check-in Data</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="additional" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Additional Info</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="qr_code" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">QR Code</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="timestamps" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Timestamps</span>
                </label>
            </div>
        </div>

        <!-- Export Button -->
        <div class="flex justify-end">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition">
                Export with Filters
            </button>
        </div>
    </form>
</div>

<!-- Export Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Registrations</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-indigo-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Checked In</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['checked_in'] }}</p>
            </div>
            <div class="bg-green-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-orange-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>
@endsection
