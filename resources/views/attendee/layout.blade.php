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
                        <img src="{{ asset('storage/' . $registration->event->logo) }}" 
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
                    <a href="{{ route('attendee.sponsors') }}" 
                       class="text-sm {{ request()->routeIs('attendee.sponsors') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Sponsors
                    </a>
                    <a href="{{ route('attendee.partners') }}" 
                       class="text-sm {{ request()->routeIs('attendee.partners') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Partners
                    </a>
                    <a href="{{ route('attendee.gallery') }}" 
                       class="text-sm {{ request()->routeIs('attendee.gallery*') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Gallery
                    </a>
                    <a href="{{ route('attendee.attendees') }}" 
                       class="text-sm {{ request()->routeIs('attendee.attendees*') ? 'text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }} transition">
                        Attendees
                    </a>
                </nav>

                <!-- User Menu Dropdown -->
                <div class="relative">
                    <button onclick="toggleUserMenu()" 
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition">
                        @if($registration->profile_picture)
                            <img src="{{ asset('storage/' . $registration->profile_picture) }}" 
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
                        <a href="{{ route('attendee.favorites') }}" 
                           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            My Favorites
                        </a>
                        <a href="{{ route('attendee.profile') }}" 
                           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            My Profile
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
                <a href="{{ route('attendee.sponsors') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.sponsors') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Sponsors
                </a>
                <a href="{{ route('attendee.partners') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.partners') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Partners
                </a>
                <a href="{{ route('attendee.gallery') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.gallery*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Gallery
                </a>
                <a href="{{ route('attendee.attendees') }}" 
                   class="px-3 py-1 text-xs {{ request()->routeIs('attendee.attendees*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded-full whitespace-nowrap">
                    Attendees
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
