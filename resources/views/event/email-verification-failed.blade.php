<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Failed - {{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <!-- Error Icon -->
            <div class="text-center mb-8">
                @if($event->logo)
                    <img src="{{ storage_public_url($event->logo) }}" alt="{{ $event->title }}" class="h-16 mx-auto mb-6">
                @endif
                <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-4">
                    <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Verification Failed</h1>
                <p class="text-gray-600">We couldn't verify your email address.</p>
            </div>

            <!-- Error Details Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">What Happened?</h2>
                
                <div class="space-y-3 text-gray-700">
                    <p>The verification link you used may be:</p>
                    <ul class="list-disc list-inside space-y-2 ml-2">
                        <li>Expired or invalid</li>
                        <li>Already been used</li>
                        <li>Incorrect or incomplete</li>
                    </ul>
                </div>
            </div>

            <!-- Solutions Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">What Can You Do?</h3>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">Check your email for the most recent verification link</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">Make sure you're clicking the complete link (it may have wrapped to multiple lines)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-700">Contact us if you continue to experience issues</span>
                    </li>
                </ul>
            </div>

            <!-- Contact Information -->
            @if($event->manager_email || $event->manager_phone)
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-indigo-900 mb-3">Need Help?</h3>
                <p class="text-sm text-indigo-800 mb-3">Contact our support team for assistance:</p>
                <div class="space-y-2">
                    @if($event->manager_email)
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <a href="mailto:{{ $event->manager_email }}" class="text-indigo-700 hover:text-indigo-900 font-medium">
                            {{ $event->manager_email }}
                        </a>
                    </div>
                    @endif
                    @if($event->manager_phone)
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <a href="tel:{{ $event->manager_phone }}" class="text-indigo-700 hover:text-indigo-900 font-medium">
                            {{ $event->manager_phone }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="text-center space-y-3">
                @if($event->website)
                <a href="{{ $event->website }}" class="block w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-center">
                    Return to Event Website
                </a>
                @endif
                
                <a href="{{ route('register') }}" class="block w-full px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center">
                    Register Again
                </a>
            </div>
        </div>
    </div>
</body>
</html>
