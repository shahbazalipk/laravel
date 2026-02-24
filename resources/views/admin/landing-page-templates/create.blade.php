@extends('admin.layout')

@section('title', 'Create Landing Page Template')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Create Landing Page Template</h1>
    <p class="text-gray-600 mt-1">Design a new template for event landing pages</p>
</div>

<form action="{{ route('admin.landing-page-templates.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        @include('admin.landing-page-templates._form')
    </div>

    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.landing-page-templates.index') }}" 
           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Cancel
        </a>
        <button type="submit" 
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            Create Template
        </button>
    </div>
</form>
@endsection
