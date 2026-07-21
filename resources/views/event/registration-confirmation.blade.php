<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $isPaymentPending = $registration->payment_status === 'pending';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isPaymentPending ? 'Payment Verification Pending' : 'Registration Confirmed' }} - {{ $event->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Event Header with Branding -->
            <div class="bg-white shadow-lg rounded-lg mb-6 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 text-white">
                    <h1 class="text-3xl font-bold">{{ $event->title }}</h1>
                    <p class="mt-2 text-indigo-100">{{ $event->seo_description ?? 'Join us for an amazing event experience' }}</p>
                </div>
                
                <div class="px-8 py-6 bg-gray-50 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Event Dates -->
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Event Date</p>
                                <p class="text-sm text-gray-900 font-semibold">
                                    {{ $event->start_date->format('M d, Y') }}
                                    @if($event->end_date && !$event->start_date->isSameDay($event->end_date))
                                        - {{ $event->end_date->format('M d, Y') }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-1">{{ $event->start_date->format('g:i A') }}</p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Location</p>
                                <p class="text-sm text-gray-900 font-semibold">{{ $event->location }}</p>
                                @if($event->country)
                                    <p class="text-xs text-gray-500 mt-1">{{ $event->country }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Event Type -->
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Event Format</p>
                                <p class="text-sm text-gray-900 font-semibold capitalize">{{ $event->format ?? $event->event_mode }}</p>
                                @if($event->type)
                                    <p class="text-xs text-gray-500 mt-1 capitalize">{{ $event->type }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Header -->
            <div class="text-center mb-8" data-testid="registration-result">
                @if($isPaymentPending)
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-100 rounded-full mb-4">
                        <svg class="w-12 h-12 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-amber-900 mb-2" data-testid="payment-pending-heading">Payment Verification Pending</h1>
                    <p class="text-lg font-medium text-amber-800">We received your registration and payment screenshot. Your attendance will be confirmed after verification.</p>
                @else
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Registration Confirmed!</h1>
                    <p class="text-lg text-gray-600">Thank you for registering</p>
                @endif
            </div>

            @if($isPaymentPending)
                <div class="mb-6 rounded-xl border-2 border-amber-300 bg-amber-50 p-5 shadow-sm sm:p-6"
                     role="alert"
                     data-testid="payment-pending-alert">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-amber-700">Under review</p>
                            <h2 class="mt-1 text-xl font-bold text-amber-950">We’ll verify your payment and update you</h2>
                            <p class="mt-1 text-sm text-amber-800">Our event team will review your payment screenshot. We’ll email you as soon as your registration is confirmed.</p>
                        </div>
                        <div class="shrink-0 rounded-lg bg-white px-5 py-3 text-left ring-1 ring-amber-200 sm:text-right">
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Amount submitted</p>
                            <p class="mt-1 text-2xl font-bold text-amber-950">
                                {{ number_format($registration->total_amount, 2) }} {{ $registration->currency }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Registration Details Card -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Your Registration Details</h2>

                <!-- Registration Number -->
                <div class="mb-6 p-4 bg-indigo-50 border-2 border-indigo-200 rounded-lg">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-1">{{ $isPaymentPending ? 'Registration Reference (verification pending)' : 'Registration Number' }}</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ $registration->registration_number }}</p>
                    </div>
                </div>

                <!-- QR Code -->
                @if($registration->qr_code && !$isPaymentPending)
                <div class="mb-6 text-center">
                    <p class="text-sm text-gray-600 mb-3">Your Badge QR Code</p>
                    <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg">
                        <img src="data:image/png;base64,{{ $registration->qr_code }}" alt="QR Code" class="w-48 h-48">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Badge Number: {{ $registration->badge_number }}</p>
                </div>
                @endif

                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
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
                    <div>
                        <p class="text-sm text-gray-600">Company</p>
                        <p class="font-medium text-gray-900">{{ $registration->company_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Category</p>
                        <p class="font-medium text-gray-900">{{ $registration->registrationCategory->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Registration Type</p>
                        <p class="font-medium text-gray-900">{{ ucfirst($registration->registration_type) }}</p>
                    </div>
                </div>

                <!-- Price Information -->
                @if($registration->total_amount > 0)
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Information</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Base Price:</span>
                            <span class="font-medium">{{ number_format($registration->base_price, 2) }} {{ $registration->currency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">VAT:</span>
                            <span class="font-medium">{{ number_format($registration->tax_amount, 2) }} {{ $registration->currency }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total Amount:</span>
                            <span class="text-indigo-600">{{ number_format($registration->total_amount, 2) }} {{ $registration->currency }}</span>
                        </div>
                        <div class="mt-4">
                            <span class="text-gray-600">Payment Status:</span>
                            <span class="ml-2 px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $registration->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($registration->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
                @else
                <div class="border-t pt-6">
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <svg class="w-12 h-12 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-lg font-semibold text-green-800">Free Registration</p>
                        <p class="text-sm text-green-600 mt-1">No payment required</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Email Verification Notice -->
            @if($event->email_verification_required && !$registration->email_verified)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-6 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Email Verification Required</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>We've sent a verification email to <strong>{{ $registration->email }}</strong>.</p>
                            <p class="mt-1">Please check your inbox and click the verification link to continue your registration.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Code Verification Notice -->
            @if($event->code_verification_required && !$registration->code_verified)
            <div class="bg-blue-50 border-l-4 border-blue-400 p-6 mb-6 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Verification Code</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>A verification code was generated for check-in. It will be provided by the event team when needed.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment Verification -->
            @if($registration->payment_status === 'pending')
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Verification</h3>
                <div class="prose prose-sm text-gray-600">
                    <p>Your payment screenshot has been submitted successfully and is awaiting review by the event team.</p>
                    <p class="mt-2">Submitted Amount: <strong>{{ number_format($registration->total_amount, 2) }} {{ $registration->currency }}</strong></p>
                    <p class="mt-2">No further action is required right now. We’ll notify you by email when your payment and registration are confirmed.</p>
                </div>
            </div>
            @endif

            <!-- Next Steps -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Next Steps</h3>
                <ul class="space-y-3">
                    @if($event->email_verification_required && !$registration->email_verified)
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">Verify your email address by clicking the link sent to your inbox</span>
                    </li>
                    @endif
                    @if($registration->payment_status === 'pending')
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">Wait for the event team to verify your submitted payment screenshot</span>
                    </li>
                    @endif
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">Save your registration number and QR code for check-in</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">
                            {{ $isPaymentPending ? 'Check your email for the payment verification result and registration confirmation' : 'Check your email for confirmation and event details' }}
                        </span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">
                            {{ $isPaymentPending ? 'Your QR code will be available after payment verification' : 'Bring your QR code or registration number on the event day' }}
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Event Information -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Event Information</h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">Date</p>
                            <p class="font-medium text-gray-900">
                                {{ $event->start_date?->format('F j, Y') }} - {{ $event->end_date?->format('F j, Y') }}
                            </p>
                        </div>
                    </div>
                    @if($event->address_line1 || $event->city)
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">Location</p>
                            <p class="font-medium text-gray-900">
                                {{ $event->address_line1 }}
                                @if($event->address_line2), {{ $event->address_line2 }}@endif
                                @if($event->city)<br>{{ $event->city }}@endif
                                @if($event->state), {{ $event->state }}@endif
                                @if($event->country), {{ $event->country }}@endif
                            </p>
                        </div>
                    </div>
                    @endif
                    @if($event->manager_email)
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">Contact</p>
                            <p class="font-medium text-gray-900">{{ $event->manager_email }}</p>
                            @if($event->manager_phone)
                                <p class="text-sm text-gray-600">{{ $event->manager_phone }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="no-print flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center" data-testid="confirmation-actions">
                @if($onlineRegistrationSlug)
                    <a href="{{ route('online.registration.new', ['slug' => $onlineRegistrationSlug]) }}"
                       class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                       data-testid="start-new-registration">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Start new registration
                    </a>
                @endif
                <button onclick="window.print()" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                        {{ $isPaymentPending ? 'Print Registration Details' : 'Print Confirmation' }}
                </button>
                @if($event->website_url)
                    <a href="{{ $event->website_url }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:bg-slate-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visit Event Website
                    </a>
                @endif
            </div>

            <!-- Event Contact Information Footer -->
            @include('online.partials.contact-footer')
        </div>
    </div>

    <style>
        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</body>
</html>
