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
@endsection
