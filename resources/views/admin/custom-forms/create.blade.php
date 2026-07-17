@extends('admin.layout')

@section('title', 'Create Custom Form')

@section('content')
<div class="mx-auto max-w-4xl">
    <a href="{{ route('admin.custom-forms.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Custom Questions</a>
    <div class="mt-4">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">New form</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Create a question set</h1>
        <p class="mt-2 text-gray-600">Choose the audience now. You can build and reorder questions on the next screen.</p>
    </div>

    @if($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" data-testid="form-errors">
            <p class="font-semibold">Please correct the highlighted fields.</p>
        </div>
    @endif

    <form action="{{ route('admin.custom-forms.store') }}" method="POST"
          class="mt-7 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8"
          data-testid="create-custom-form-form">
        @csrf
        @include('admin.custom-forms._form')
        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.custom-forms.index') }}" class="rounded-xl border border-gray-300 px-5 py-3 text-center font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="save-custom-form">Create and build</button>
        </div>
    </form>
</div>
@endsection
