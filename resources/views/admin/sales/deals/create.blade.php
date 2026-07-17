@extends('admin.layout')

@section('title', 'Create Deal')

@section('content')
<div class="mb-7">
    <h1 class="text-3xl font-bold text-gray-900">Create deal</h1>
</div>

<form action="{{ route('admin.sales.deals.store') }}" method="POST" class="max-w-2xl space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="deal-create-form">
    @csrf
    <div>
        <label for="sales_pipeline_id" class="block text-sm font-semibold text-gray-800">Pipeline</label>
        <select id="sales_pipeline_id" name="sales_pipeline_id" required class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3" data-testid="deal-pipeline-select">
            @foreach($pipelines as $pipeline)
                <option value="{{ $pipeline->id }}" @selected(old('sales_pipeline_id', $selectedPipeline?->id) == $pipeline->id)>{{ $pipeline->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="title" class="block text-sm font-semibold text-gray-800">Title</label>
        <input id="title" name="title" type="text" required value="{{ old('title') }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3" data-testid="deal-title">
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="value" class="block text-sm font-semibold text-gray-800">Value</label>
            <input id="value" name="value" type="number" step="0.01" value="{{ old('value') }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
        </div>
        <div>
            <label for="expected_close_date" class="block text-sm font-semibold text-gray-800">Expected close</label>
            <input id="expected_close_date" name="expected_close_date" type="date" value="{{ old('expected_close_date') }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
        </div>
    </div>
    <fieldset class="rounded-xl border border-gray-200 p-4">
        <legend class="px-2 text-sm font-semibold text-gray-800">Primary contact (optional)</legend>
        <div class="mt-3 grid gap-4 sm:grid-cols-2">
            <input name="contact[name]" type="text" placeholder="Name" value="{{ old('contact.name') }}" class="rounded-xl border border-gray-300 px-4 py-3">
            <input name="contact[email]" type="email" placeholder="Email" value="{{ old('contact.email') }}" class="rounded-xl border border-gray-300 px-4 py-3">
            <input name="contact[phone]" type="text" placeholder="Phone" value="{{ old('contact.phone') }}" class="rounded-xl border border-gray-300 px-4 py-3">
            <input name="contact[company_name]" type="text" placeholder="Company" value="{{ old('contact.company_name') }}" class="rounded-xl border border-gray-300 px-4 py-3">
        </div>
    </fieldset>
    <div>
        <label for="description" class="block text-sm font-semibold text-gray-800">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('description') }}</textarea>
    </div>
    <div class="flex gap-3">
        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="deal-submit">Create deal</button>
        <a href="{{ route('admin.sales.deals.index') }}" class="rounded-xl border border-gray-300 px-6 py-3 font-semibold text-gray-700">Cancel</a>
    </div>
</form>
@endsection
