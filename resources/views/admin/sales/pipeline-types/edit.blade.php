@extends('admin.layout')

@section('title', 'Edit '.$type->name)

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Sales</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Edit {{ $type->name }}</h1>
    </div>
    <a href="{{ route('admin.sales.pipeline-types.show', $type) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View details</a>
</div>

<form action="{{ route('admin.sales.pipeline-types.update', $type) }}" method="POST" class="space-y-6" data-testid="pipeline-type-edit-form">
    @csrf @method('PUT')
    @include('admin.sales.pipeline-types._form', ['type' => $type])
    <div class="flex gap-3">
        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="pipeline-type-submit">Save changes</button>
        <a href="{{ route('admin.sales.pipeline-types.index') }}" class="rounded-xl border border-gray-300 px-6 py-3 font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
    </div>
</form>
@endsection
