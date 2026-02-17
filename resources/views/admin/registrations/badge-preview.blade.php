<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge Preview - {{ $registration->first_name }} {{ $registration->last_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
        .badge {
            width: 4in;
            height: 6in;
            page-break-after: always;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Print Button -->
    <div class="no-print fixed top-4 right-4 z-50">
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg shadow-lg transition">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print Badge
        </button>
    </div>

    <!-- Badge -->
    <div class="flex items-center justify-center min-h-screen p-8">
        <div class="badge bg-white rounded-lg shadow-2xl overflow-hidden border-4 border-indigo-600">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 text-center">
                <h1 class="text-2xl font-bold mb-1">{{ config('app.name', 'Event Manager') }}</h1>
                <p class="text-sm opacity-90">{{ \App\Models\Event::getCurrentEvent()->name ?? 'Event Name' }}</p>
            </div>

            <!-- Photo Placeholder -->
            <div class="flex justify-center -mt-12 mb-4">
                <div class="w-24 h-24 bg-gray-200 rounded-full border-4 border-white shadow-lg flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>

            <!-- Attendee Info -->
            <div class="px-6 text-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800 mb-1">
                    {{ $registration->first_name }} {{ $registration->last_name }}
                </h2>
                @if($registration->job_title)
                    <p class="text-lg text-gray-600 mb-1">{{ $registration->job_title }}</p>
                @endif
                @if($registration->company_name)
                    <p class="text-md text-gray-500 font-semibold">{{ $registration->company_name }}</p>
                @endif
            </div>

            <!-- Category Badge -->
            <div class="px-6 mb-6">
                <div class="bg-indigo-100 text-indigo-800 text-center py-3 rounded-lg">
                    <p class="text-sm font-medium uppercase tracking-wide">
                        {{ $registration->registrationCategory->name ?? 'Attendee' }}
                    </p>
                </div>
            </div>

            <!-- QR Code -->
            <div class="px-6 mb-6">
                <div class="bg-white border-2 border-gray-200 rounded-lg p-4 flex justify-center">
                    {!! QrCode::size(200)->generate($registration->qr_code) !!}
                </div>
                <p class="text-center text-xs text-gray-500 mt-2">{{ $registration->registration_number }}</p>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 text-center border-t border-gray-200">
                <p class="text-xs text-gray-600">
                    Please wear this badge at all times during the event
                </p>
            </div>
        </div>
    </div>
</body>
</html>
