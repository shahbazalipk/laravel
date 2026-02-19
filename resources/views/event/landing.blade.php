<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->seo_title ?? $event->title }}</title>
    @if($event->seo_description)
        <meta name="description" content="{{ $event->seo_description }}">
    @endif
    @if($event->seo_keywords)
        <meta name="keywords" content="{{ $event->seo_keywords }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: {{ $event->primary_color ?? '#3B82F6' }};
            --secondary-color: {{ $event->secondary_color ?? '#10B981' }};
        }
        .bg-primary { background-color: var(--primary-color); }
        .text-primary { color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
        .bg-secondary { background-color: var(--secondary-color); }
        .text-secondary { color: var(--secondary-color); }
        .hover\:bg-primary:hover { background-color: var(--primary-color); }
        .gradient-primary { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    @if($event->logo)
                        <img src="{{ $event->logo }}" alt="{{ $event->title }}" class="h-10">
                    @else
                        <span class="text-xl font-bold text-primary">{{ $event->title }}</span>
                    @endif
                </div>
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="#about" class="text-gray-700 hover:text-primary transition">About</a>
                    @if($agendaItems->isNotEmpty())
                        <a href="#agenda" class="text-gray-700 hover:text-primary transition">Agenda</a>
                    @endif
                    @if($speakers->isNotEmpty())
                        <a href="#speakers" class="text-gray-700 hover:text-primary transition">Speakers</a>
                    @endif
                    @if($sponsors->isNotEmpty())
                        <a href="#sponsors" class="text-gray-700 hover:text-primary transition">Sponsors</a>
                    @endif
                    <a href="{{ route('attendee.login') }}" class="text-gray-700 hover:text-primary transition font-medium">
                        Attendee Login
                    </a>
                    <a href="#register" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition">Register Now</a>
                </div>
                <button class="md:hidden text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section class="gradient-primary text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-5xl md:text-6xl font-bold mb-6">{{ $event->title }}</h1>
                <p class="text-xl md:text-2xl mb-8 text-white/90">{{ $event->seo_description ?? 'Join us for an unforgettable experience' }}</p>
                <div class="flex flex-wrap justify-center gap-6 mb-8 text-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $event->start_date->format('M d') }} - {{ $event->end_date->format('M d, Y') }}
                    </div>
                    @if($event->location)
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $event->location }}
                        </div>
                    @endif
                    <div class="flex items-center">
                        <span class="px-4 py-1 bg-white/20 rounded-full text-sm font-medium">
                            {{ ucfirst($event->format) }}
                        </span>
                    </div>
                </div>
                <div class="flex justify-center gap-4">
                    <a href="#register" class="bg-white text-primary px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition shadow-lg">
                        Register Now
                    </a>
                    @if($agendaItems->isNotEmpty())
                        <a href="#agenda" class="bg-white/10 backdrop-blur-sm text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white/20 transition border-2 border-white/30">
                            View Agenda
                        </a>
                    @endif
                </div>
                <div class="mt-6">
                    <a href="{{ route('attendee.login') }}" class="text-white/90 hover:text-white text-sm font-medium underline">
                        Already registered? Login to your account
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">About the Event</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ $event->seo_description ?? 'Join industry leaders, innovators, and professionals for an inspiring event experience.' }}
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $event->expected_attendees ?? 'TBA' }}</h3>
                    <p class="text-gray-600">Expected Attendees</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $agendaItems->count() }}</h3>
                    <p class="text-gray-600">Sessions & Workshops</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $event->start_date->diffInDays($event->end_date) + 1 }}</h3>
                    <p class="text-gray-600">Days of Content</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Event Tracks -->
    @if($tracks->isNotEmpty())
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Event Tracks</h2>
                <p class="text-xl text-gray-600">Explore diverse topics across multiple tracks</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($tracks as $track)
                    <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 hover:shadow-xl transition" style="border-color: {{ $track->color ?? $event->primary_color }}">
                        <div class="w-12 h-12 rounded-lg mb-4 flex items-center justify-center" style="background-color: {{ $track->color ?? $event->primary_color }}20">
                            <svg class="w-6 h-6" style="color: {{ $track->color ?? $event->primary_color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ $track->name }}</h3>
                        @if($track->description)
                            <p class="text-gray-600 mb-4">{{ $track->description }}</p>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium" style="color: {{ $track->color ?? $event->primary_color }}">
                                {{ $agendaItems->where('track_id', $track->id)->count() }} Sessions
                            </span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Agenda Section -->
    @if($agendaItems->isNotEmpty())
    <section id="agenda" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Event Agenda</h2>
                <p class="text-xl text-gray-600">Detailed schedule of sessions and activities</p>
            </div>
            <div class="space-y-4">
                @foreach($agendaItems as $item)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4" style="border-color: {{ $item->track->color ?? $event->primary_color }}">
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="text-sm font-semibold text-gray-500 bg-gray-100 px-3 py-1 rounded">
                                            {{ $item->start_time->format('g:i A') }}
                                        </span>
                                        @if($item->type === 'break')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                ☕ Break
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $item->title }}</h3>
                                    @if($item->description)
                                        <p class="text-gray-600 mb-3">{{ $item->description }}</p>
                                    @endif
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $item->start_time->diffInMinutes($item->end_time) }} min
                                        </div>
                                        @if($item->track)
                                            <div class="flex items-center">
                                                <span class="w-3 h-3 rounded-full mr-1" style="background-color: {{ $item->track->color }}"></span>
                                                {{ $item->track->name }}
                                            </div>
                                        @endif
                                        @if($item->location)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                </svg>
                                                {{ $item->location->name }}
                                            </div>
                                        @endif
                                        @if($item->max_attendees)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                {{ $item->max_attendees }} seats
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4 md:mt-0 md:ml-6">
                                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-primary/10 text-primary">
                                        {{ ucfirst($item->type) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Speakers Section -->
    @if($speakers->isNotEmpty())
    <section id="speakers" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Featured Speakers</h2>
                <p class="text-xl text-gray-600">Learn from industry experts and thought leaders</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                @foreach($speakers as $speaker)
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition overflow-hidden">
                        @if($speaker->profile_image)
                            <div class="aspect-square bg-cover bg-center" style="background-image: url('{{ $speaker->profile_image }}')"></div>
                        @else
                            <div class="aspect-square bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="p-6 text-center">
                            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $speaker->full_name }}</h3>
                            @if($speaker->job_title || $speaker->company)
                                <p class="text-sm text-gray-600 mb-2">
                                    @if($speaker->job_title){{ $speaker->job_title }}@endif
                                    @if($speaker->job_title && $speaker->company) at @endif
                                    @if($speaker->company){{ $speaker->company }}@endif
                                </p>
                            @endif
                            @if($speaker->bio)
                                <p class="text-sm text-gray-500 line-clamp-2">{{ $speaker->bio }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Registration CTA -->
    <section id="register" class="py-20 gradient-primary text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">Ready to Join Us?</h2>
            <p class="text-xl mb-8 text-white/90">Secure your spot at {{ $event->title }} today!</p>
            <a href="#" class="inline-block bg-white text-primary px-10 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition shadow-lg">
                Register Now →
            </a>
            <p class="mt-6 text-white/80">Limited seats available • Early bird pricing ends soon</p>
        </div>
    </section>

    <!-- Sponsors Section -->
    @if($sponsors->isNotEmpty())
    <section id="sponsors" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Our Sponsors</h2>
                <p class="text-xl text-gray-600">Supported by leading organizations</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                @foreach($sponsors as $sponsor)
                    <div class="bg-gray-50 rounded-lg p-8 flex items-center justify-center hover:shadow-md transition">
                        @if($sponsor->logo_thumbnail || $sponsor->logo_defined_size)
                            <img src="{{ $sponsor->logo_thumbnail ?? $sponsor->logo_defined_size }}" alt="{{ $sponsor->name }}" class="max-h-16 max-w-full object-contain">
                        @else
                            <div class="text-gray-400 font-bold text-lg text-center">{{ $sponsor->name }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">{{ $event->title }}</h3>
                    <p class="text-gray-400 text-sm">{{ $event->seo_description }}</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#about" class="hover:text-white transition">About</a></li>
                        @if($agendaItems->isNotEmpty())
                            <li><a href="#agenda" class="hover:text-white transition">Agenda</a></li>
                        @endif
                        @if($speakers->isNotEmpty())
                            <li><a href="#speakers" class="hover:text-white transition">Speakers</a></li>
                        @endif
                        <li><a href="#register" class="hover:text-white transition">Register</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        @if($event->manager_email)
                            <li><a href="mailto:{{ $event->manager_email }}" class="hover:text-white transition">{{ $event->manager_email }}</a></li>
                        @endif
                        @if($event->manager_phone)
                            <li><a href="tel:{{ $event->manager_phone }}" class="hover:text-white transition">{{ $event->manager_phone }}</a></li>
                        @endif
                        @if($event->location)
                            <li>{{ $event->location }}</li>
                        @endif
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} {{ $event->title }}. All rights reserved.</p>
                @if($event->terms_url || $event->privacy_url)
                    <div class="mt-2 space-x-4">
                        @if($event->terms_url)
                            <a href="{{ $event->terms_url }}" class="hover:text-white transition">Terms of Service</a>
                        @endif
                        @if($event->privacy_url)
                            <a href="{{ $event->privacy_url }}" class="hover:text-white transition">Privacy Policy</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
