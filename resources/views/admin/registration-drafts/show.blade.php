@extends('admin.layout')

@section('title', 'Registration Draft')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <a href="{{ route('admin.registrations.index', ['stage' => 'draft']) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← Back to drafts</a>
        <h1 class="mt-2 text-2xl font-bold text-gray-800">Incomplete registration draft</h1>
        <p class="mt-1 text-gray-600">Started online and not yet submitted</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button type="button"
                onclick="document.getElementById('deleteDraftModal').classList.remove('hidden')"
                class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50"
                data-testid="open-delete-draft-modal">
            Delete Draft
        </button>
        <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800" data-testid="draft-detail-badge">Draft</span>
        @if($draft->isExpired())
            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Expired</span>
        @endif
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
            Step {{ $draft->current_step->number() }}: {{ $draft->current_step->label() }}
        </span>
    </div>
</div>

@php
    $profilePicture = $payload['profile_picture'] ?? null;
    $profilePictureUrl = $profilePicture ? storage_public_url($profilePicture) : null;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3" data-testid="draft-detail">
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <div class="mb-6 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                @if($profilePictureUrl)
                    <img src="{{ $profilePictureUrl }}"
                         alt="Profile photo"
                         class="h-24 w-24 rounded-full border-4 border-white object-cover shadow-md ring-1 ring-slate-200"
                         data-testid="draft-profile-photo">
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-400 ring-1 ring-slate-200"
                         data-testid="draft-profile-photo-placeholder">
                        No photo
                    </div>
                @endif
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ trim(($payload['first_name'] ?? '').' '.($payload['last_name'] ?? '')) ?: 'Incomplete profile' }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $draft->email }}</p>
                    @if($profilePictureUrl)
                        <a href="{{ $profilePictureUrl }}"
                           target="_blank"
                           rel="noopener"
                           class="mt-2 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            Open full photo
                        </a>
                    @endif
                </div>
            </div>

            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Contact</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $draft->email }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email verified</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $draft->isEmailVerified() ? 'Yes' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Phone</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $payload['phone'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Draft reference</dt>
                    <dd class="mt-1 font-medium text-gray-900">DRAFT-{{ strtoupper(substr($draft->public_id, 0, 8)) }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Information entered</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-gray-500">Name</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ trim(($payload['first_name'] ?? '').' '.($payload['last_name'] ?? '')) ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Job title</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $payload['job_title'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Company</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $payload['company_name'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Category</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $category?->name ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        @include('admin.partials.custom-form-responses', [
            'customFormResponses' => $draft->customFormResponses,
        ])
    </div>

    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Timeline</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Started</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $draft->created_at?->format('M d, Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Last updated</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $draft->updated_at?->format('M d, Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Expires</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $draft->expires_at?->format('M d, Y H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            This person started online registration but has not submitted yet. They can resume from the same browser or emailed resume link until the draft expires.
        </div>
    </div>
</div>

<div id="deleteDraftModal"
     class="{{ $errors->has('confirmation') ? '' : 'hidden' }} fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 p-4 backdrop-blur-sm sm:p-6"
     role="dialog"
     aria-modal="true"
     aria-labelledby="delete-draft-title"
     data-testid="delete-draft-modal">
    <div class="flex min-h-full items-start justify-center pt-6 sm:items-center sm:pt-0">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 id="delete-draft-title" class="text-lg font-semibold text-slate-900">
                            Permanently delete draft?
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">This action cannot be undone.</p>
                    </div>
                    <button type="button"
                            onclick="document.getElementById('deleteDraftModal').classList.add('hidden')"
                            class="rounded-lg p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                            aria-label="Close delete dialog">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.registration-drafts.destroy', $draft) }}"
                  method="POST"
                  class="space-y-5 px-5 py-5 sm:px-6"
                  data-testid="delete-draft-form">
                @csrf
                @method('DELETE')

                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    This incomplete or expired draft and any saved answers will be permanently removed.
                </div>

                <div>
                    <label for="delete_draft_confirmation" class="block text-sm font-medium text-slate-700">
                        Enter <span class="font-mono font-semibold text-slate-950">{{ $draft->displayReference() }}</span> to confirm
                    </label>
                    <input id="delete_draft_confirmation"
                           name="confirmation"
                           type="text"
                           value="{{ old('confirmation') }}"
                           autocomplete="off"
                           required
                           class="mt-2 w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 {{ $errors->has('confirmation') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-red-500 focus:ring-red-200' }}"
                           data-testid="delete-draft-confirmation">
                    @error('confirmation')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button"
                            onclick="document.getElementById('deleteDraftModal').classList.add('hidden')"
                            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            data-testid="delete-draft-submit">
                        Permanently Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
