@extends('admin.layout')

@section('title', 'Inquiry Forms')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Sales</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Inquiry Forms</h1>
    </div>
    <a href="{{ route('admin.sales.inquiry-forms.create') }}" class="rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="create-inquiry-form">+ New form</a>
</div>

<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-testid="inquiry-forms-list">
    @forelse($forms as $form)
        <article class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="inquiry-form-card-{{ $form->public_id }}">
            <div class="flex items-start justify-between">
                <span class="rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $form->status->value === 'published' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">
                    {{ $form->status->label() }}
                </span>
                <span class="text-xs text-gray-500">{{ $form->submissions_count }} submissions</span>
            </div>
            <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $form->name }}</h2>
            <p class="mt-1 font-mono text-xs text-gray-500">{{ $form->slug }}</p>
            <div class="mt-5 flex items-center gap-3 border-t border-gray-100 pt-4">
                <a href="{{ route('admin.sales.inquiry-forms.show', $form) }}" class="text-sm font-semibold text-indigo-600">View</a>
                <a href="{{ route('admin.sales.inquiry-forms.edit', $form) }}" class="text-sm font-semibold text-gray-600">Edit</a>
            </div>
        </article>
    @empty
        <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-gray-600">No inquiry forms yet.</p>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $forms->links() }}</div>
@endsection
