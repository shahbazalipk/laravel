<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge - {{ $registration->full_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .badge-container {
                page-break-after: always;
            }
        }
        
        .badge-container {
            @if($badgeDesign && $badgeDesign->size == '4x6')
                @if($badgeDesign->orientation == 'landscape')
                    width: 6in;
                    height: 4in;
                @else
                    width: 4in;
                    height: 6in;
                @endif
            @elseif($badgeDesign && $badgeDesign->size == '3x4')
                @if($badgeDesign->orientation == 'landscape')
                    width: 4in;
                    height: 3in;
                @else
                    width: 3in;
                    height: 4in;
                @endif
            @else
                width: 4in;
                height: 6in;
            @endif
            margin: 0 auto;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="p-8">
        <!-- Print Button -->
        <div class="no-print mb-4 text-center">
            <button onclick="window.print()" 
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                Print Badge
            </button>
            <button onclick="window.close()" 
                    class="ml-3 px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition">
                Close
            </button>
        </div>

        <!-- Badge -->
        <div class="badge-container bg-white shadow-lg rounded-lg overflow-hidden {{ $badgeDesign && $badgeDesign->show_border ? 'border-' . $badgeDesign->border_width : '' }}" 
             style="{{ $badgeDesign && $badgeDesign->show_border ? 'border-color: ' . $badgeDesign->border_color . '; border-width: ' . $badgeDesign->border_width . 'px;' : '' }}">
            
            <!-- Event Header -->
            @if(!$badgeDesign || $badgeDesign->show_header)
            <div class="text-white p-6 text-center" 
                 style="background-color: {{ $badgeDesign ? $badgeDesign->header_bg_color : '#4F46E5' }}; color: {{ $badgeDesign ? $badgeDesign->header_text_color : '#FFFFFF' }};">
                @if(!$badgeDesign || $badgeDesign->show_event_logo)
                    @if($registration->event->logo)
                        <img src="{{ storage_public_url($registration->event->logo) }}" alt="{{ $registration->event->name }}" class="h-12 mx-auto mb-2">
                    @endif
                @endif
                
                @if(!$badgeDesign || $badgeDesign->show_event_name)
                    <h1 class="text-2xl font-bold">{{ $registration->event->name }}</h1>
                @endif
                
                @if((!$badgeDesign || $badgeDesign->show_event_dates) && $registration->event->start_date && $registration->event->end_date)
                    <p class="text-sm mt-1">
                        {{ \Carbon\Carbon::parse($registration->event->start_date)->format('M d') }} - 
                        {{ \Carbon\Carbon::parse($registration->event->end_date)->format('M d, Y') }}
                    </p>
                @endif
            </div>
            @endif

            <!-- Attendee Info -->
            <div class="p-6 text-center">
                <!-- Profile Picture -->
                @if(!$badgeDesign || $badgeDesign->show_profile_picture)
                    @if($registration->profile_picture)
                        <div class="mb-4">
                            <img src="{{ storage_public_url($registration->profile_picture) }}" 
                                 alt="{{ $registration->full_name }}"
                                 class="w-32 h-32 mx-auto object-cover border-4 border-gray-200 
                                 {{ $badgeDesign && $badgeDesign->profile_picture_shape == 'circle' ? 'rounded-full' : '' }}
                                 {{ $badgeDesign && $badgeDesign->profile_picture_shape == 'rounded' ? 'rounded-lg' : '' }}">
                        </div>
                    @else
                        <div class="mb-4">
                            <div class="w-32 h-32 mx-auto bg-gray-200 flex items-center justify-center border-4 border-gray-300
                                 {{ $badgeDesign && $badgeDesign->profile_picture_shape == 'circle' ? 'rounded-full' : '' }}
                                 {{ $badgeDesign && $badgeDesign->profile_picture_shape == 'rounded' ? 'rounded-lg' : '' }}">
                                <span class="text-5xl text-gray-500 font-bold">{{ substr($registration->first_name, 0, 1) }}</span>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Name -->
                @if(!$badgeDesign || $badgeDesign->show_name)
                    <h2 class="font-bold text-gray-900 mb-2 
                        {{ $badgeDesign && $badgeDesign->name_font_size == '2xl' ? 'text-2xl' : '' }}
                        {{ !$badgeDesign || $badgeDesign->name_font_size == '3xl' ? 'text-3xl' : '' }}
                        {{ $badgeDesign && $badgeDesign->name_font_size == '4xl' ? 'text-4xl' : '' }}">
                        {{ $registration->full_name }}
                    </h2>
                @endif
                
                <!-- Job Title & Company -->
                @if((!$badgeDesign || $badgeDesign->show_job_title) && $registration->job_title)
                    <p class="text-lg text-gray-700">{{ $registration->job_title }}</p>
                @endif
                
                @if((!$badgeDesign || $badgeDesign->show_company) && $registration->company_name)
                    <p class="text-lg font-semibold text-gray-800 mt-1">{{ $registration->company_name }}</p>
                @endif

                <!-- Category Badge -->
                @if(!$badgeDesign || $badgeDesign->show_category)
                    <div class="mt-4">
                        @if(!$badgeDesign || $badgeDesign->category_style == 'badge')
                            <span class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">
                                {{ $registration->registrationCategory->name ?? 'Attendee' }}
                            </span>
                        @else
                            <span class="text-gray-700 text-sm">
                                {{ $registration->registrationCategory->name ?? 'Attendee' }}
                            </span>
                        @endif
                    </div>
                @endif

                <!-- QR Code -->
                @if((!$badgeDesign || $badgeDesign->show_qr_code) && $registration->qr_code)
                    <div class="mt-6 
                        {{ !$badgeDesign || $badgeDesign->qr_code_position == 'center' ? 'text-center' : '' }}
                        {{ $badgeDesign && $badgeDesign->qr_code_position == 'left' ? 'text-left' : '' }}
                        {{ $badgeDesign && $badgeDesign->qr_code_position == 'right' ? 'text-right' : '' }}">
                        <img src="data:image/png;base64,{{ $registration->qr_code }}" 
                             alt="QR Code" 
                             class="mx-auto
                             {{ !$badgeDesign || $badgeDesign->qr_code_size == '32' ? 'w-32 h-32' : '' }}
                             {{ $badgeDesign && $badgeDesign->qr_code_size == '24' ? 'w-24 h-24' : '' }}
                             {{ $badgeDesign && $badgeDesign->qr_code_size == '40' ? 'w-40 h-40' : '' }}">
                    </div>
                @endif

                <!-- Registration Number -->
                @if(!$badgeDesign || $badgeDesign->show_registration_number)
                    <div class="mt-4">
                        <p class="text-xs text-gray-500 font-mono">{{ $registration->registration_number }}</p>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            @if(!$badgeDesign || $badgeDesign->show_footer)
            <div class="p-4 text-center border-t" 
                 style="background-color: {{ $badgeDesign ? $badgeDesign->footer_bg_color : '#F3F4F6' }};">
                @if((!$badgeDesign || $badgeDesign->show_location) && $registration->event->location)
                    <p class="text-xs text-gray-600">{{ $registration->event->location }}</p>
                @endif
                @if((!$badgeDesign || $badgeDesign->show_website) && $registration->event->website)
                    <p class="text-xs text-gray-600 mt-1">{{ $registration->event->website }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    <script>
        // Auto-print on load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
