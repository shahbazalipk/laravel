<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Attendee Portal') - {{ $registration->event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center gap-4">
                    @if($registration->event->logo)
                        <img src="{{ storage_public_url($registration->event->logo) }}" 
                             alt="{{ $registration->event->name }}" 
                             class="h-10">
                    @else
                        <span class="text-xl font-bold text-indigo-600">{{ $registration->event->name }}</span>
                    @endif
                </div>
                
                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-6">
                    <a href="{{ route('attendee.dashboard') }}" 
                       class="text-sm {{ request()->routeIs('attendee.dashboard') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Dashboard
                    </a>
                    <a href="{{ route('attendee.agenda') }}" 
                       class="text-sm {{ request()->routeIs('attendee.agenda') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Agenda
                    </a>
                    <a href="{{ route('attendee.sessions') }}" 
                       class="text-sm {{ request()->routeIs('attendee.sessions*') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Sessions
                    </a>
                    <a href="{{ route('attendee.speakers') }}" 
                       class="text-sm {{ request()->routeIs('attendee.speakers*') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Speakers
                    </a>
                    <a href="{{ route('attendee.exhibitors') }}" 
                       class="text-sm {{ request()->routeIs('attendee.exhibitors*') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Exhibitors
                    </a>
                    <a href="{{ route('attendee.jobs') }}" 
                       class="text-sm {{ request()->routeIs('attendee.jobs*') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Jobs
                    </a>
                    <a href="{{ route('attendee.products') }}" 
                       class="text-sm {{ request()->routeIs('attendee.products*') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Products
                    </a>
                    <a href="{{ route('attendee.event-wall') }}" 
                       class="text-sm {{ request()->routeIs('attendee.event-wall*') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Event Wall
                    </a>
                    <a href="{{ route('attendee.gallery') }}" 
                       class="text-sm {{ request()->routeIs('attendee.gallery*') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Gallery
                    </a>
                    <a href="{{ route('attendee.sponsors') }}" 
                       class="text-sm {{ request()->routeIs('attendee.sponsors') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Sponsors
                    </a>
                </nav>

                <!-- User Menu Dropdown -->
                <div class="relative">
                    <button onclick="toggleUserMenu()" 
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition">
                        @if($registration->profile_picture)
                            <img src="{{ storage_public_url($registration->profile_picture) }}" 
                                 alt="{{ $registration->full_name }}"
                                 class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center">
                                <span class="text-sm font-semibold text-white">
                                    {{ substr($registration->first_name, 0, 1) }}{{ substr($registration->last_name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <span class="hidden md:block text-sm font-medium text-gray-700">{{ $registration->first_name }}</span>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="userMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-semibold text-gray-900">{{ $registration->full_name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $registration->email }}</p>
                        </div>
                        
                        <!-- Profile & Settings -->
                        <a href="{{ route('attendee.profile') }}" 
                           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            My Profile
                        </a>
                        <a href="{{ route('attendee.favorites') }}" 
                           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            My Favorites
                        </a>
                        
                        <div class="border-t border-gray-200 my-1"></div>
                        
                        <!-- Networking -->
                        <div class="px-4 py-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Networking</p>
                        </div>
                        <a href="{{ route('attendee.attendees') }}" 
                           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Browse Attendees
                        </a>
                        <a href="{{ route('attendee.connections') }}" 
                           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            My Connections
                        </a>
                        <a href="{{ route('attendee.messages') }}" 
                           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            Messages
                        </a>
                        
                        <div class="border-t border-gray-200 my-1"></div>
                        
                        <!-- Resources -->
                        <div class="px-4 py-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Resources</p>
                        </div>
                        <a href="{{ route('attendee.marketing-hub') }}" 
                           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                            </svg>
                            Marketing Hub
                        </a>
                        <a href="{{ route('attendee.partners') }}" 
                           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Partners
                        </a>
                        
                        <div class="border-t border-gray-200 my-1"></div>
                        
                        <form method="POST" action="{{ route('attendee.logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="md:hidden border-t border-gray-200">
            <div class="px-4 py-3 flex gap-2 overflow-x-auto">
                <a href="{{ route('attendee.dashboard') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.dashboard') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Dashboard
                </a>
                <a href="{{ route('attendee.agenda') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.agenda') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Agenda
                </a>
                <a href="{{ route('attendee.sessions') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.sessions*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Sessions
                </a>
                <a href="{{ route('attendee.speakers') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.speakers*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Speakers
                </a>
                <a href="{{ route('attendee.exhibitors') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.exhibitors*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Exhibitors
                </a>
                <a href="{{ route('attendee.jobs') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.jobs*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Jobs
                </a>
                <a href="{{ route('attendee.products') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.products*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Products
                </a>
                <a href="{{ route('attendee.event-wall') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.event-wall*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Event Wall
                </a>
                <a href="{{ route('attendee.gallery') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.gallery*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Gallery
                </a>
                <a href="{{ route('attendee.sponsors') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.sponsors') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Sponsors
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script>
    // User Menu Dropdown Toggle
    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        menu.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('userMenu');
        const button = event.target.closest('button[onclick="toggleUserMenu()"]');
        
        if (!button && !menu.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });

    // Favorite Toggle
    function toggleFavorite(type, id, button) {
        const icon = button.querySelector('svg');
        const isFavorited = button.classList.contains('favorited');
        
        fetch('{{ route('attendee.favorites.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ type, id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.favorited) {
                button.classList.add('favorited', 'bg-red-100', 'text-red-600');
                button.classList.remove('bg-gray-100', 'text-gray-600');
                icon.setAttribute('fill', 'currentColor');
            } else {
                button.classList.remove('favorited', 'bg-red-100', 'text-red-600');
                button.classList.add('bg-gray-100', 'text-gray-600');
                icon.setAttribute('fill', 'none');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update favorite');
        });
    }
    </script>
</body>
</html>
