<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Closed - {{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                @if($event->logo)
                    <img src="{{ storage_public_url($event->logo) }}" alt="{{ $event->title }}" class="h-20 mx-auto mb-6">
                @endif
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Registration Closed</h1>
                <p class="text-lg text-gray-600">{{ $event->title }}</p>
            </div>

            <!-- Closed Message Card -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
                @if($event->closed_message)
                    <div class="prose prose-lg max-w-none text-gray-700">
                        {!! nl2br(e($event->closed_message)) !!}
                    </div>
                @else
                    <div class="text-center">
                        <p class="text-lg text-gray-700 mb-4">
                            We're sorry, but registration for this event is currently closed.
                        </p>
                        <p class="text-gray-600">
                            Registration may have closed due to:
                        </p>
                        <ul class="mt-4 space-y-2 text-left max-w-md mx-auto">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span class="text-gray-600">The registration deadline has passed</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span class="text-gray-600">The event has reached maximum capacity</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span class="text-gray-600">The event has already concluded</span>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Event Information -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Event Information</h2>
                <div class="space-y-4">
                    @if($event->start_date)
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">Event Date</p>
                            <p class="font-medium text-gray-900">
                                {{ $event->start_date->format('F j, Y') }}
                                @if($event->end_date && !$event->start_date->isSameDay($event->end_date))
                                    - {{ $event->end_date->format('F j, Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif

                    @if($event->online_reg_close)
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">Registration Closed On</p>
                            <p class="font-medium text-gray-900">{{ $event->online_reg_close->format('F j, Y g:i A') }}</p>
                        </div>
                    </div>
                    @endif

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

                    @if($event->description)
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">About the Event</p>
                            <p class="text-gray-700 mt-1">{{ Str::limit($event->description, 200) }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Contact Information -->
            @if($event->manager_email || $event->manager_phone)
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-indigo-900 mb-3">Have Questions?</h3>
                <p class="text-sm text-indigo-800 mb-3">
                    If you have any questions or need assistance, please contact us:
                </p>
                <div class="space-y-2">
                    @if($event->manager_name)
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-indigo-900 font-medium">{{ $event->manager_name }}</span>
                    </div>
                    @endif
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
                    Visit Event Website
                </a>
                @endif
                
                @if($event->manager_email)
                <a href="mailto:{{ $event->manager_email }}" class="block w-full px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center">
                    Contact Event Organizer
                </a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
