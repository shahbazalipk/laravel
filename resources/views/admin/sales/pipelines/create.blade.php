@extends('admin.layout')

@section('title', 'Create Pipeline')

@section('content')
<div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Sales</p>
    <h1 class="mt-1 text-3xl font-bold text-gray-900">Create pipeline</h1>
</div>

<form action="{{ route('admin.sales.pipelines.store') }}" method="POST" class="max-w-2xl space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="pipeline-create-form">
    @csrf
    <div>
        <label for="sales_pipeline_type_id" class="block text-sm font-semibold text-gray-800">Pipeline type</label>
        <select id="sales_pipeline_type_id" name="sales_pipeline_type_id" required class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3" data-testid="pipeline-type-select">
            @foreach($types as $type)
                <option value="{{ $type->id }}" @selected(old('sales_pipeline_type_id', $selectedType?->id) == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-800">Name</label>
        <input id="name" name="name" type="text" required value="{{ old('name') }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3" data-testid="pipeline-name">
    </div>
    <div>
        <label for="description" class="block text-sm font-semibold text-gray-800">Description</label>
        <textarea id="description" name="description" rows="3" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('description') }}</textarea>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="currency" class="block text-sm font-semibold text-gray-800">Currency</label>
            <input id="currency" name="currency" type="text" value="{{ old('currency', current_event_currency()) }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
            <p class="mt-1 text-xs text-gray-500">Defaults to the event currency.</p>
        </div>
        <div>
            <label for="revenue_target" class="block text-sm font-semibold text-gray-800">Revenue target</label>
            <input id="revenue_target" name="revenue_target" type="number" step="0.01" value="{{ old('revenue_target') }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
        </div>
    </div>
    <div class="flex gap-3">
        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="pipeline-submit">Create pipeline</button>
        <a href="{{ route('admin.sales.pipelines.index') }}" class="rounded-xl border border-gray-300 px-6 py-3 font-semibold text-gray-700">Cancel</a>
    </div>
</form>
@endsection
