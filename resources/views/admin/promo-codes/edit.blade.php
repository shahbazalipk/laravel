@extends('admin.layout')

@section('title', 'Edit Promo Code')

@section('content')
<div class="mb-6" data-testid="promo-code-edit-page">
    <div class="mb-4 flex items-center">
        <a href="{{ route('admin.promo-codes.index') }}" class="mr-4 text-gray-600 hover:text-gray-900" aria-label="Back">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Promo Code</h1>
            <p class="mt-1 text-gray-600">Update {{ $promoCode->code }}</p>
        </div>
        <a href="{{ route('admin.promo-codes.show', $promoCode) }}"
           class="ml-auto rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
           data-testid="promo-code-edit-view-details">
            View details
        </a>
    </div>
</div>

<div class="rounded-lg bg-white p-6 shadow-sm">
    <form action="{{ route('admin.promo-codes.update', $promoCode) }}" method="POST" enctype="multipart/form-data" data-testid="promo-code-form">
        @csrf
        @method('PUT')
        @include('admin.promo-codes._form')

        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.promo-codes.index') }}"
               class="rounded-lg border border-gray-300 px-6 py-2 text-gray-700 transition hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-6 py-2 text-white transition hover:bg-indigo-700"
                    data-testid="promo-code-submit">
                Save Changes
            </button>
        </div>
    </form>

    @if(($promoCode->emails_count ?? $promoCode->emails()->count()) > 0)
        <form action="{{ route('admin.promo-codes.clear-emails', $promoCode) }}"
              method="POST"
              class="mt-4"
              onsubmit="return confirm('Clear the email allowlist now?');">
            @csrf
            <button type="submit"
                    class="text-sm font-medium text-red-600 hover:text-red-800"
                    data-testid="promo-clear-emails-now">
                Clear email list now
            </button>
        </form>
    @endif
</div>
@endsection
