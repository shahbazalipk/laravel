@extends('admin.layout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Edit Marketing Asset</h1>
            <a href="{{ route('admin.marketing-assets.index') }}" class="text-gray-600 hover:text-gray-800">
                ← Back to Assets
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('admin.marketing-assets.update', $marketingAsset) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.marketing-assets._form', ['asset' => $marketingAsset])
                
                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('admin.marketing-assets.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                        Update Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
