@extends('admin.layout')

@section('title', 'Create Pipeline Type')

@section('content')
<div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Sales</p>
    <h1 class="mt-1 text-3xl font-bold text-gray-900">Create pipeline type</h1>
</div>

<form action="{{ route('admin.sales.pipeline-types.store') }}" method="POST" class="space-y-6" data-testid="pipeline-type-create-form">
    @csrf
    @include('admin.sales.pipeline-types._form')
    <div class="flex gap-3">
        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="pipeline-type-submit">Create type</button>
        <a href="{{ route('admin.sales.pipeline-types.index') }}" class="rounded-xl border border-gray-300 px-6 py-3 font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
    </div>
</form>
@endsection
