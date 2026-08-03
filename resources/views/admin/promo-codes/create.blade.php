@extends('admin.layout')

@section('title', 'Create Promo Code')

@section('content')
<div class="mb-6" data-testid="promo-code-create-page">
    <div class="mb-4 flex items-center">
        <a href="{{ route('admin.promo-codes.index') }}" class="mr-4 text-gray-600 hover:text-gray-900" aria-label="Back">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create Promo Code</h1>
            <p class="mt-1 text-gray-600">Add a new discount code for this event</p>
        </div>
    </div>
</div>

<div class="rounded-lg bg-white p-6 shadow-sm">
    <form action="{{ route('admin.promo-codes.store') }}" method="POST" enctype="multipart/form-data" data-testid="promo-code-form">
        @csrf
        @include('admin.promo-codes._form')

        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.promo-codes.index') }}"
               class="rounded-lg border border-gray-300 px-6 py-2 text-gray-700 transition hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-6 py-2 text-white transition hover:bg-indigo-700"
                    data-testid="promo-code-submit">
                Create Promo Code
            </button>
        </div>
    </form>
</div>
@endsection
