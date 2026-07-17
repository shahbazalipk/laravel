@extends('admin.layout')

@section('title', 'Edit '.$pipeline->name)

@section('content')
<div class="mb-7">
    <h1 class="text-3xl font-bold text-gray-900">Edit {{ $pipeline->name }}</h1>
</div>

<form action="{{ route('admin.sales.pipelines.update', $pipeline) }}" method="POST" class="max-w-2xl space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="pipeline-edit-form">
    @csrf @method('PUT')
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-800">Name</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $pipeline->name) }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
    </div>
    <div>
        <label for="description" class="block text-sm font-semibold text-gray-800">Description</label>
        <textarea id="description" name="description" rows="3" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('description', $pipeline->description) }}</textarea>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="currency" class="block text-sm font-semibold text-gray-800">Currency</label>
            <input id="currency" name="currency" type="text" value="{{ old('currency', $pipeline->currency) }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
        </div>
        <div>
            <label for="revenue_target" class="block text-sm font-semibold text-gray-800">Revenue target</label>
            <input id="revenue_target" name="revenue_target" type="number" step="0.01" value="{{ old('revenue_target', $pipeline->revenue_target) }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
        </div>
    </div>
    <div class="flex gap-3">
        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700">Save</button>
        <a href="{{ route('admin.sales.pipelines.show', $pipeline) }}" class="rounded-xl border border-gray-300 px-6 py-3 font-semibold text-gray-700">Cancel</a>
    </div>
</form>
@endsection
