<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Event Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .dropdown-menu { 
            display: none;
        }
        .dropdown.active .dropdown-menu { 
            display: block; 
        }
        .mobile-menu { display: none; }
        @media (max-width: 1024px) {
            .desktop-menu { display: none; }
            .mobile-menu.active { display: block; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-indigo-900 text-white shadow-lg">
        <div class="px-6">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 hover:opacity-80 transition">
                        @if(config('event.logo'))
                            <img src="{{ asset('storage/' . config('event.logo')) }}" 
                                 alt="{{ config('event.name', 'Event') }}"
                                 class="h-10 w-auto">
                        @endif
                        <div class="flex-shrink-0">
                            <h1 class="text-xl font-bold">{{ config('event.name', 'Event Manager') }}</h1>
                            <p class="text-xs text-indigo-300">Admin Panel</p>
                        </div>
                    </a>

                    <!-- Desktop Menu -->
                    <div class="hidden lg:flex items-center space-x-1 desktop-menu">
                        <!-- Registrations -->
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/registrations*') || request()->is('admin/groups*') ? 'bg-indigo-800' : '' }}">
                                Registrations
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="{{ route('admin.registrations.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">All Registrations</a>
                                <a href="{{ route('admin.groups.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Groups</a>
                                <a href="{{ route('admin.registrations.checkin') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Check-In</a>
                                <a href="{{ route('admin.registrations.badges') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Badge Printing</a>
                                <a href="{{ route('admin.registrations.export-page') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Export</a>
                            </div>
                        </div>

                        <!-- Sales -->
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/sales*') ? 'bg-indigo-800' : '' }}">
                                Sales
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Pipelines</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Deals</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Website Forms</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Form Submissions</a>
                            </div>
                        </div>

                        <!-- Marketing -->
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/email-campaigns*') ? 'bg-indigo-800' : '' }}">
                                Marketing
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50">
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Email Campaigns</div>
                                <a href="{{ route('admin.email-campaigns.email-campaigns.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Campaigns</a>
                                <a href="{{ route('admin.email-campaigns.email-templates.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Templates</a>
                                <a href="{{ route('admin.email-campaigns.provider-configs.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Email Providers</a>
                            </div>
                        </div>

                        <!-- Exhibitors -->
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/exhibitors*') || request()->is('admin/exhibitor-products*') || request()->is('admin/exhibitor-jobs*') ? 'bg-indigo-800' : '' }}">
                                Exhibitors
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="{{ route('admin.exhibitors.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">List</a>
                                <a href="{{ route('admin.exhibitor-products.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Products</a>
                                <a href="{{ route('admin.exhibitor-jobs.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Jobs</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Leads</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Floor Plans</a>
                            </div>
                        </div>

                        <!-- Agenda -->
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/agenda*') || request()->is('admin/tracks*') || request()->is('admin/locations*') || request()->is('admin/sessions*') || request()->is('admin/lectures*') || request()->is('admin/speakers*') ? 'bg-indigo-800' : '' }}">
                                Agenda
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="{{ route('admin.agenda-management.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Agendas</a>
                                <a href="{{ route('admin.tracks.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Tracks</a>
                                <a href="{{ route('admin.locations.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Locations</a>
                                <a href="{{ route('admin.speakers.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Speakers</a>
                                <a href="{{ route('admin.sessions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Sessions</a>
                                <a href="{{ route('admin.lectures.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Lectures</a>
                            </div>
                        </div>

                        <!-- Abstracts -->
                        <a href="#" class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm">
                            Abstracts
                        </a>

                        <!-- Categories -->
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/registration-categories*') ? 'bg-indigo-800' : '' }}">
                                Categories
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="{{ route('admin.registration-categories.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">List</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Items</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Options</a>
                            </div>
                        </div>

                        <!-- Parameters -->
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/sponsors*') || request()->is('admin/partners*') || request()->is('admin/registration-statuses*') || request()->is('admin/personas*') || request()->is('admin/category-types*') || request()->is('admin/product-types*') || request()->is('admin/exhibitor-tags*') || request()->is('admin/booth-types*') || request()->is('admin/exhibitor-types*') || request()->is('admin/industries*') || request()->is('admin/business-activities*') || request()->is('admin/group-types*') ? 'bg-indigo-800' : '' }}">
                                Parameters
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50 max-h-96 overflow-y-auto">
                                <a href="{{ route('admin.sponsors.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Sponsors</a>
                                <a href="{{ route('admin.partners.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Partners</a>
                                <a href="{{ route('admin.registration-statuses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Registration Statuses</a>
                                <a href="{{ route('admin.personas.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Personas</a>
                                <a href="{{ route('admin.category-types.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Category Types</a>
                                <a href="{{ route('admin.product-types.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Product Types</a>
                                <a href="{{ route('admin.exhibitor-tags.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Exhibitor Tags</a>
                                <a href="{{ route('admin.booth-types.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Booth Types</a>
                                <a href="{{ route('admin.exhibitor-types.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Exhibitor Types</a>
                                <a href="{{ route('admin.industries.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Industries</a>
                                <a href="{{ route('admin.business-activities.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Business Activities</a>
                                <a href="{{ route('admin.group-types.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Group Types</a>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/event-settings*') || request()->is('admin/files*') || request()->is('admin/memberships*') || request()->is('admin/event-urls*') || request()->is('admin/badge-designs*') || request()->is('admin/gallery*') || request()->is('admin/marketing-assets*') ? 'bg-indigo-800' : '' }}">
                                Settings
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="{{ route('admin.event-settings.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Event Settings</a>
                                <a href="{{ route('admin.landing-page-templates.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Landing Page Templates</a>
                                <a href="{{ route('admin.event-urls.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">URLs</a>
                                <a href="{{ route('admin.badge-designs.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Badge Designs</a>
                                <a href="{{ route('admin.gallery.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Gallery</a>
                                <a href="{{ route('admin.marketing-assets.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Marketing Assets</a>
                                <a href="{{ route('admin.ads.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Ads Management</a>
                                <a href="{{ route('admin.email-campaigns.email-templates.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Email Templates</a>
                                <a href="{{ route('admin.files.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">File Manager</a>
                                <a href="{{ route('admin.memberships.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Memberships</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side Menu -->
                <div class="flex items-center space-x-4">
                    <!-- User Profile Dropdown -->
                    <div class="dropdown relative">
                        <button class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="hidden md:inline">{{ session('admin_email') }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <p class="text-sm text-gray-500">Signed in as</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ session('admin_email') }}</p>
                            </div>
                            <a href="{{ route('event.landing') }}" target="_blank" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                View Event
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="lg:hidden text-white p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu lg:hidden bg-indigo-800 border-t border-indigo-700">
            <div class="px-4 py-3 space-y-2 max-h-96 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Dashboard</a>
                
                <div class="border-t border-indigo-700 my-2"></div>
                <p class="px-3 py-1 text-xs text-indigo-300 uppercase">Registrations</p>
                <a href="{{ route('admin.registrations.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">All Registrations</a>
                <a href="{{ route('admin.groups.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Groups</a>
                <a href="{{ route('admin.registrations.checkin') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Check-In</a>
                <a href="{{ route('admin.registrations.badges') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Badge Printing</a>
                <a href="{{ route('admin.registrations.export-page') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Export</a>
                
                <div class="border-t border-indigo-700 my-2"></div>
                <p class="px-3 py-1 text-xs text-indigo-300 uppercase">Marketing</p>
                <a href="{{ route('admin.email-campaigns.email-campaigns.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Email Campaigns</a>
                <a href="{{ route('admin.email-campaigns.email-templates.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Email Templates</a>
                <a href="{{ route('admin.email-campaigns.provider-configs.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Email Providers</a>
                
                <div class="border-t border-indigo-700 my-2"></div>
                <p class="px-3 py-1 text-xs text-indigo-300 uppercase">Exhibitors</p>
                <a href="{{ route('admin.exhibitors.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">List</a>
                <a href="{{ route('admin.exhibitor-products.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Products</a>
                <a href="{{ route('admin.exhibitor-jobs.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Jobs</a>
                
                <div class="border-t border-indigo-700 my-2"></div>
                <p class="px-3 py-1 text-xs text-indigo-300 uppercase">Agenda</p>
                <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Tracks</a>
                <a href="{{ route('admin.agenda.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Sessions</a>
                
                <div class="border-t border-indigo-700 my-2"></div>
                <p class="px-3 py-1 text-xs text-indigo-300 uppercase">Settings</p>
                <a href="{{ route('admin.event-settings.edit') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Event Settings</a>
                <a href="{{ route('admin.landing-page-templates.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Landing Page Templates</a>
                <a href="{{ route('admin.event-urls.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">URLs</a>
                <a href="{{ route('admin.badge-designs.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Badge Designs</a>
                <a href="{{ route('admin.gallery.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Gallery</a>
                <a href="{{ route('admin.marketing-assets.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Marketing Assets</a>
                <a href="{{ route('admin.ads.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Ads Management</a>
                <a href="{{ route('admin.email-campaigns.email-templates.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Email Templates</a>
                <a href="{{ route('admin.files.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">File Manager</a>
                
                <div class="border-t border-indigo-700 my-2"></div>
                <a href="{{ route('event.landing') }}" target="_blank" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">View Event</a>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded text-sm hover:bg-indigo-700">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="min-h-screen">
        <div class="max-w-7xl mx-auto px-6 py-6">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded" data-testid="flash-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded" data-testid="flash-error">
                    {{ session('error') }}
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <script>
        // Toggle mobile menu
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('active');
        });

        // Handle dropdown menus - click to toggle
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            const button = dropdown.querySelector('button');
            
            if (button) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Close other dropdowns
                    document.querySelectorAll('.dropdown').forEach(d => {
                        if (d !== dropdown) {
                            d.classList.remove('active');
                        }
                    });
                    
                    // Toggle current dropdown
                    dropdown.classList.toggle('active');
                });
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });

        // Prevent dropdown from closing when clicking inside it
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
