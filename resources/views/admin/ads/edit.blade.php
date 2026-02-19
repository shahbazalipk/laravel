@extends('admin.layout')

@section('title', 'Edit Ad')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Ad</h1>
            <p class="text-gray-600 mt-1">Update advertisement details</p>
        </div>
        <a href="{{ route('admin.ads.index') }}" 
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
            Back to Ads
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
        <p class="font-semibold mb-2">Please fix the following errors:</p>
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.ads.update', $ad) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        @include('admin.ads._form')
        
        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.ads.index') }}" 
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                Update Ad
            </button>
        </div>
    </form>
</div>
@endsection
