@extends('admin.layout')

@section('title', 'Custom Questions')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Forms</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Custom & Conditional Questions</h1>
        <p class="mt-2 max-w-2xl text-gray-600">Build one reusable question set for each audience: registration, exhibitors, and groups.</p>
    </div>
    @if(count($availableAudiences) > 0)
        <a href="{{ route('admin.custom-forms.create') }}"
           class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
           data-testid="create-custom-form">
            <span class="mr-2 text-xl leading-none">+</span> New form
        </a>
    @else
        <span class="inline-flex cursor-not-allowed items-center justify-center rounded-xl bg-gray-200 px-5 py-3 font-semibold text-gray-500"
              data-testid="create-custom-form-disabled"
              title="Each audience already has a form">
            All audiences in use
        </span>
    @endif
</div>

@if(count($availableAudiences) === 0 && $forms->isNotEmpty())
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" data-testid="audience-limit-note">
        Each audience can only have one form. Delete an existing form if you need to create a replacement.
    </div>
@endif

@if($forms->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center shadow-sm" data-testid="custom-forms-empty">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-2xl text-indigo-600">?</div>
        <h2 class="mt-5 text-xl font-bold text-gray-900">No custom forms yet</h2>
        <p class="mx-auto mt-2 max-w-md text-gray-600">Create a form, then add questions, choices, validation, and conditional behavior.</p>
        <a href="{{ route('admin.custom-forms.create') }}" class="mt-6 inline-flex rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Create your first form</a>
    </div>
@else
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-testid="custom-forms-list">
        @foreach($forms as $form)
            @php
                $audienceClasses = match($form->audience->value) {
                    'registration' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                    'exhibitor' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                    default => 'bg-violet-50 text-violet-700 ring-violet-600/20',
                };
            @endphp
            <article class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                     data-testid="custom-form-card-{{ $form->public_id }}">
                <div class="flex items-start justify-between gap-3">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $audienceClasses }}">{{ $form->audience->label() }}</span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $form->is_active ? 'text-emerald-700' : 'text-gray-500' }}">
                        <span class="h-2 w-2 rounded-full {{ $form->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        {{ $form->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <h2 class="mt-5 text-xl font-bold text-gray-900">{{ $form->name }}</h2>
                <p class="mt-1 font-mono text-xs text-gray-500">{{ $form->slug }}</p>
                <p class="mt-3 flex-1 text-sm leading-6 text-gray-600">{{ $form->description ? Str::limit($form->description, 120) : 'No description added.' }}</p>
                <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
                    <span class="text-sm font-semibold text-gray-700">{{ $form->questions_count }} {{ Str::plural('question', $form->questions_count) }}</span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.custom-forms.edit', $form) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800" data-testid="edit-custom-form-{{ $form->public_id }}">Open builder</a>
                        <form action="{{ route('admin.custom-forms.destroy', $form) }}" method="POST" onsubmit="return confirm('Delete this form and its questions?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800" data-testid="delete-custom-form-{{ $form->public_id }}">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
