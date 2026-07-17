@extends('admin.layout')

@section('title', 'Edit '.$form->name)

@section('content')
<div class="mb-7"><h1 class="text-3xl font-bold text-gray-900">Edit {{ $form->name }}</h1></div>
<form action="{{ route('admin.sales.inquiry-forms.update', $form) }}" method="POST" class="space-y-6" data-testid="inquiry-form-edit-form">
    @csrf @method('PUT')
    @include('admin.sales.inquiry-forms._form', ['form' => $form])
    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700">Save changes</button>
</form>
@endsection
