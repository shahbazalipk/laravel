@extends('admin.layout')

@section('title', 'Create Inquiry Form')

@section('content')
<div class="mb-7"><h1 class="text-3xl font-bold text-gray-900">Create inquiry form</h1></div>
<form action="{{ route('admin.sales.inquiry-forms.store') }}" method="POST" class="space-y-6" data-testid="inquiry-form-create-form">
    @csrf
    @include('admin.sales.inquiry-forms._form')
    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="inquiry-form-submit">Create form</button>
</form>
@endsection
