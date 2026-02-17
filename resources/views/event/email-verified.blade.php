<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - {{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <!-- Success Icon -->
            <div class="text-center mb-8">
                @if($event->logo)
                    <img src="{{ asset('storage/' . $event->logo) }}" alt="{{ $event->title }}" class="h-16 mx-auto mb-6">
                @endif
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Email Verified!</h1>
                <p class="text-gray-600">Your email address has been successfully verified.</p>
            </div>

            <!-- Registration Details Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Registration Confirmed</h2>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Registration Number</p>
                        <p class="text-xl font-bold text-indigo-600">{{ $registration->registration_number }}</p>
                    </div>
                    
                    <div class="border-t pt-3">
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium text-gray-900">{{ $registration->full_name }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium text-gray-900">{{ $registration->email }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Category</p>
                        <p class="font-medium text-gray-900">{{ $registration->registrationCategory->name }}</p>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">What's Next?</h3>
                <ul class="space-y-3">
                    @if($registration->payment_status === 'pending')
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">Complete your payment to finalize your registration</span>
                    </li>
                    @endif
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">Check your email for event details and updates</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">Save your registration number for check-in</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">We look forward to seeing you at the event!</span>
                    </li>
                </ul>
            </div>

            <!-- Event Info -->
            @if($event->start_date)
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-indigo-900 font-medium">Event Date</p>
                        <p class="text-sm text-indigo-700">
                            {{ $event->start_date->format('F j, Y') }}
                            @if($event->end_date && !$event->start_date->isSameDay($event->end_date))
                                - {{ $event->end_date->format('F j, Y') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="text-center space-y-3">
                @if($event->website)
                <a href="{{ $event->website }}" class="block w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-center">
                    Visit Event Website
                </a>
                @endif
                
                @if($event->manager_email)
                <p class="text-sm text-gray-600">
                    Questions? Contact us at 
                    <a href="mailto:{{ $event->manager_email }}" class="text-indigo-600 hover:text-indigo-800">
                        {{ $event->manager_email }}
                    </a>
                </p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
