@extends('admin.layout')

@section('title', 'Registration Details')

@section('content')
@php
    use App\Payments\Enums\RegistrationPaymentSummaryStatus;
    $headerPaymentStatus = $paymentSummary['summary_status'] instanceof RegistrationPaymentSummaryStatus
        ? $paymentSummary['summary_status']
        : RegistrationPaymentSummaryStatus::tryFrom($registration->payment_status) ?? RegistrationPaymentSummaryStatus::Pending;
@endphp

<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
        <div class="flex items-center">
            <a href="{{ route('admin.registrations.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Registration Details</h1>
                <p class="text-gray-600 mt-1">{{ $registration->registration_number }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.registrations.edit', $registration) }}" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Edit Registration
            </a>
            <button type="button"
                    id="delete-registration"
                    onclick="document.getElementById('deleteRegistrationModal').classList.remove('hidden')"
                    class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50"
                    data-testid="open-delete-registration-modal">
                Delete Registration
            </button>
            @if(!$registration->checked_in)
            <form action="{{ route('admin.registrations.check-in', $registration) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Check In
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@if($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" data-testid="flash-errors">
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Status Badges -->
<div class="mb-6 flex flex-wrap gap-2">
    @if($registration->registrationStatus)
        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
              style="background-color: {{ $registration->registrationStatus->color }}20; color: {{ $registration->registrationStatus->color }};">
            Status: {{ $registration->registrationStatus->name }}
        </span>
    @endif

    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $headerPaymentStatus->badgeClasses() }}">
        Payment: {{ $headerPaymentStatus->label() }}
    </span>
    
    @if($registration->checked_in)
    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
        Checked In
    </span>
    @endif
    
    @if($registration->email_verified)
    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
        Email Verified
    </span>
    @endif
    
    @if($registration->badge_printed)
    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
        Badge Printed
    </span>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6 order-2 lg:order-1">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Personal Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Full Name</p>
                    <p class="font-medium text-gray-900">{{ $registration->full_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="font-medium text-gray-900">{{ $registration->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p class="font-medium text-gray-900">{{ $registration->phone }}</p>
                </div>
                @if($registration->mobile_phone)
                <div>
                    <p class="text-sm text-gray-600">Mobile</p>
                    <p class="font-medium text-gray-900">{{ $registration->mobile_phone }}</p>
                </div>
                @endif
                @if($registration->job_title)
                <div>
                    <p class="text-sm text-gray-600">Job Title</p>
                    <p class="font-medium text-gray-900">{{ $registration->job_title }}</p>
                </div>
                @endif
                @if($registration->department)
                <div>
                    <p class="text-sm text-gray-600">Department</p>
                    <p class="font-medium text-gray-900">{{ $registration->department }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Company Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Company Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Company Name</p>
                    <p class="font-medium text-gray-900">{{ $registration->company_name }}</p>
                </div>
                @if($registration->industry)
                <div>
                    <p class="text-sm text-gray-600">Industry</p>
                    <p class="font-medium text-gray-900">{{ $registration->industry->name }}</p>
                </div>
                @endif
                @if($registration->businessActivity)
                <div>
                    <p class="text-sm text-gray-600">Business Activity</p>
                    <p class="font-medium text-gray-900">{{ $registration->businessActivity->name }}</p>
                </div>
                @endif
                @if($registration->company_size)
                <div>
                    <p class="text-sm text-gray-600">Company Size</p>
                    <p class="font-medium text-gray-900">{{ $registration->company_size }}</p>
                </div>
                @endif
                @if($registration->company_website)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600">Website</p>
                    <a href="{{ $registration->company_website }}" target="_blank" class="font-medium text-indigo-600 hover:text-indigo-800">
                        {{ $registration->company_website }}
                    </a>
                </div>
                @endif
                @if($registration->company_address || $registration->city)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600">Address</p>
                    <p class="font-medium text-gray-900">
                        {{ $registration->company_address }}
                        @if($registration->city), {{ $registration->city }}@endif
                        @if($registration->state), {{ $registration->state }}@endif
                        @if($registration->postal_code) {{ $registration->postal_code }}@endif
                        @if($registration->country)<br>{{ $registration->country }}@endif
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Category-Specific Information -->
        @if($registration->professional_student_id || $registration->membership_id || $registration->professional_id_document_path)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Category Requirements</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($registration->membership_id)
                <div>
                    <p class="text-sm text-gray-600">Membership ID</p>
                    <p class="font-medium text-gray-900">{{ $registration->membership_id }}</p>
                    @if($registration->membership_validated)
                        <span class="text-xs text-green-600">✓ Validated</span>
                    @endif
                </div>
                @endif
                @if($registration->professional_student_id)
                <div>
                    <p class="text-sm text-gray-600">Professional/Student ID</p>
                    <p class="font-medium text-gray-900">{{ $registration->professional_student_id }}</p>
                </div>
                @endif
                @if($registration->professional_id_document_path)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600 mb-2">ID Document</p>
                    <a href="{{ storage_public_url($registration->professional_id_document_path) }}" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        View Document
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Additional Information -->
        @if($registration->dietary_requirements || $registration->special_needs || $registration->tshirt_size)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($registration->dietary_requirements)
                <div>
                    <p class="text-sm text-gray-600">Dietary Requirements</p>
                    <p class="font-medium text-gray-900">{{ $registration->dietary_requirements }}</p>
                </div>
                @endif
                @if($registration->special_needs)
                <div>
                    <p class="text-sm text-gray-600">Special Needs</p>
                    <p class="font-medium text-gray-900">{{ $registration->special_needs }}</p>
                </div>
                @endif
                @if($registration->tshirt_size)
                <div>
                    <p class="text-sm text-gray-600">T-Shirt Size</p>
                    <p class="font-medium text-gray-900">{{ $registration->tshirt_size }}</p>
                </div>
                @endif
                @if($registration->how_did_you_hear)
                <div>
                    <p class="text-sm text-gray-600">How Did You Hear</p>
                    <p class="font-medium text-gray-900">{{ $registration->how_did_you_hear }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        @include('admin.partials.custom-form-responses', [
            'customFormResponses' => $registration->customFormResponses,
        ])

        @include('admin.registrations.partials.payment-history')

        @include('admin.registrations.partials.notes')
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6 order-1 lg:order-2">
        @if($registration->profile_picture)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Profile Picture</h2>
            <div class="flex justify-center">
                <img src="{{ storage_public_url($registration->profile_picture) }}"
                     alt="{{ $registration->full_name }}"
                     class="w-48 h-48 rounded-full object-cover border-4 border-gray-200">
            </div>
        </div>
        @endif

        @if($registration->qr_code)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Badge QR Code</h2>
            <div class="flex justify-center">
                <img src="data:image/png;base64,{{ $registration->qr_code }}"
                     alt="QR Code"
                     class="w-32 h-32">
            </div>
            @if($registration->badge_number)
                <p class="text-center text-sm text-gray-600 mt-2">Badge: {{ $registration->badge_number }}</p>
            @endif
            @if(!$registration->badge_printed)
            <form action="{{ route('admin.registrations.print-badge', $registration) }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="w-full px-3 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition">
                    Mark as Printed
                </button>
            </form>
            @endif
        </div>
        @endif

        <!-- Registration Details -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Registration Details</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Category</p>
                    <p class="font-medium text-gray-900">{{ $registration->registrationCategory->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Type</p>
                    <p class="font-medium text-gray-900">{{ ucfirst($registration->registration_type) }}</p>
                </div>
                @if($registration->exhibitor)
                <div>
                    <p class="text-sm text-gray-600">Exhibitor</p>
                    <p class="font-medium text-gray-900">{{ $registration->exhibitor->company_name }}</p>
                </div>
                @endif
                @if($registration->group)
                <div>
                    <p class="text-sm text-gray-600">Group</p>
                    <p class="font-medium text-gray-900">{{ $registration->group->group_name }}</p>
                </div>
                @endif
                <div>
                    <p class="text-sm text-gray-600">Registered On</p>
                    <p class="font-medium text-gray-900">{{ $registration->created_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
        </div>

        @include('admin.registrations.partials.status-management')

        @include('admin.registrations.partials.payment-summary')

        @if($registration->checked_in)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Check-In</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Checked In At</p>
                    <p class="font-medium text-gray-900">{{ $registration->checked_in_at->format('M d, Y g:i A') }}</p>
                </div>
                @if($registration->checked_in_by)
                <div>
                    <p class="text-sm text-gray-600">Checked In By</p>
                    <p class="font-medium text-gray-900">{{ $registration->checked_in_by }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@include('admin.registrations.partials.payment-modals')

<div id="deleteRegistrationModal"
     class="{{ $errors->has('confirmation') ? '' : 'hidden' }} fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 p-4 backdrop-blur-sm sm:p-6"
     role="dialog"
     aria-modal="true"
     aria-labelledby="delete-registration-title"
     data-testid="delete-registration-modal">
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
                        <h2 id="delete-registration-title" class="text-lg font-semibold text-slate-900">
                            Permanently delete registration?
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            This action cannot be undone.
                        </p>
                    </div>
                    <button type="button"
                            onclick="document.getElementById('deleteRegistrationModal').classList.add('hidden')"
                            class="rounded-lg p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                            aria-label="Close delete dialog">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.registrations.destroy', $registration) }}"
                  method="POST"
                  class="space-y-5 px-5 py-5 sm:px-6"
                  data-testid="delete-registration-form">
                @csrf
                @method('DELETE')

                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-semibold text-red-900">The following data will be removed:</p>
                    <ul class="mt-2 space-y-1.5 text-sm text-red-800">
                        <li>• Registration profile and uploaded documents</li>
                        <li>• {{ $registration->paymentEntries->count() }} payment ledger {{ \Illuminate\Support\Str::plural('entry', $registration->paymentEntries->count()) }}</li>
                        <li>• Attendee connections, messages, favorites, and wall activity</li>
                        <li>• Hashed links and check-in information</li>
                    </ul>
                </div>

                <div>
                    <label for="delete_confirmation" class="block text-sm font-medium text-slate-700">
                        Enter <span class="font-mono font-semibold text-slate-950">{{ $registration->registration_number }}</span> to confirm
                    </label>
                    <input id="delete_confirmation"
                           name="confirmation"
                           type="text"
                           value="{{ old('confirmation') }}"
                           autocomplete="off"
                           required
                           class="mt-2 w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 {{ $errors->has('confirmation') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-red-500 focus:ring-red-200' }}"
                           data-testid="delete-registration-confirmation">
                    @error('confirmation')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button"
                            onclick="document.getElementById('deleteRegistrationModal').classList.add('hidden')"
                            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            data-testid="delete-registration-submit">
                        Permanently Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
