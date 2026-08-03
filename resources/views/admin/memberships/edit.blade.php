@extends('admin.layout')

@section('title', 'Edit Membership List')

@section('content')
<div class="mb-6" data-testid="membership-edit-page">
    <div class="mb-4 flex items-center">
        <a href="{{ route('admin.memberships.index') }}" class="mr-4 text-gray-600 hover:text-gray-900" aria-label="Back">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Membership List</h1>
            <p class="mt-1 text-gray-600">Update {{ $membership->name }}</p>
        </div>
    </div>
</div>

<div class="rounded-lg bg-white p-6 shadow-sm">
    <form action="{{ route('admin.memberships.update', $membership) }}" method="POST" enctype="multipart/form-data" data-testid="membership-form">
        @csrf
        @method('PUT')
        @include('admin.memberships._form')
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.memberships.index') }}" class="rounded-lg border border-gray-300 px-6 py-2 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2 text-white hover:bg-indigo-700" data-testid="membership-submit">Save Changes</button>
        </div>
    </form>
</div>
@endsection
