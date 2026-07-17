@extends('admin.layout')

@section('title', 'Edit '.$deal->title)

@section('content')
<div class="mb-7">
    <h1 class="text-3xl font-bold text-gray-900">Edit {{ $deal->title }}</h1>
    <p class="font-mono text-sm text-gray-500">{{ $deal->reference }}</p>
</div>

<form action="{{ route('admin.sales.deals.update', $deal) }}" method="POST" class="max-w-2xl space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="deal-edit-form">
    @csrf @method('PUT')
    <div>
        <label for="title" class="block text-sm font-semibold text-gray-800">Title</label>
        <input id="title" name="title" type="text" required value="{{ old('title', $deal->title) }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="value" class="block text-sm font-semibold text-gray-800">Value</label>
            <input id="value" name="value" type="number" step="0.01" value="{{ old('value', $deal->value) }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
        </div>
        <div>
            <label for="probability" class="block text-sm font-semibold text-gray-800">Probability %</label>
            <input id="probability" name="probability" type="number" min="0" max="100" value="{{ old('probability', $deal->probability) }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
        </div>
        <div>
            <label for="expected_close_date" class="block text-sm font-semibold text-gray-800">Expected close</label>
            <input id="expected_close_date" name="expected_close_date" type="date" value="{{ old('expected_close_date', $deal->expected_close_date?->format('Y-m-d')) }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
        </div>
    </div>
    <div>
        <label for="description" class="block text-sm font-semibold text-gray-800">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('description', $deal->description) }}</textarea>
    </div>
    <div class="flex gap-3">
        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700">Save</button>
        <a href="{{ route('admin.sales.deals.show', $deal) }}" class="rounded-xl border border-gray-300 px-6 py-3 font-semibold text-gray-700">Cancel</a>
    </div>
</form>
@endsection
