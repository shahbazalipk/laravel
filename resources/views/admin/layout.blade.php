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
<body class="overflow-x-hidden bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-indigo-900 text-white shadow-lg">
        <div class="px-6">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 hover:opacity-80 transition">
                        @if(config('event.logo'))
                            <img src="{{ storage_public_url(config('event.logo')) }}" 
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
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="{{ route('admin.sales.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Dashboard</a>
                                <a href="{{ route('admin.sales.pipeline-types.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Pipeline Types</a>
                                <a href="{{ route('admin.sales.pipelines.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Pipelines</a>
                                <a href="{{ route('admin.sales.deals.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Deals</a>
                                <a href="{{ route('admin.sales.inquiry-forms.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Inquiry Forms</a>
                                <a href="{{ route('admin.sales.submissions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Form Submissions</a>
                            </div>
                        </div>

                        @if(config('modules.finance.enabled') && app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::VIEW))
                            <!-- Finance -->
                            <div class="dropdown relative">
                                <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/finance*') ? 'bg-indigo-800' : '' }}">
                                    Finance
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div class="dropdown-menu hidden absolute left-0 mt-2 w-52 bg-white rounded-lg shadow-lg py-2 z-50">
                                    <a href="{{ route('admin.finance.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Overview</a>
                                    <a href="{{ route('admin.finance.transactions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Transactions</a>
                                    <a href="{{ route('admin.finance.income.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Income</a>
                                    <a href="{{ route('admin.finance.expenses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Expenses</a>
                                    <a href="{{ route('admin.finance.invoices.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Invoices</a>
                                    <a href="{{ route('admin.finance.bills.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Bills</a>
                                    <a href="{{ route('admin.finance.payments.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Payments</a>
                                    <a href="{{ route('admin.finance.refunds.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Refunds</a>
                                    <a href="{{ route('admin.finance.budgets.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Budgets</a>
                                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::APPROVE))
                                        <a href="{{ route('admin.finance.approvals.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Approvals</a>
                                    @endif
                                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::RECONCILE))
                                        <a href="{{ route('admin.finance.reconciliation.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Reconciliation</a>
                                    @endif
                                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::VIEW_REPORTS))
                                        <a href="{{ route('admin.finance.reports.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Reports</a>
                                    @endif
                                    <a href="{{ route('admin.finance.accounts.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Accounts</a>
                                    <a href="{{ route('admin.finance.vendors.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Vendors</a>
                                    <a href="{{ route('admin.finance.categories.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Categories</a>
                                </div>
                            </div>
                        @endif

                        @if(config('modules.projects.enabled') && app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::VIEW))
                            <!-- Projects -->
                            <div class="dropdown relative">
                                <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/projects*') ? 'bg-indigo-800' : '' }}">
                                    Projects
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div class="dropdown-menu hidden absolute left-0 mt-2 w-52 bg-white rounded-lg shadow-lg py-2 z-50">
                                    <a href="{{ route('admin.projects.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Overview</a>
                                    <a href="{{ route('admin.projects.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">All Projects</a>
                                    <a href="{{ route('admin.projects.calendar') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Calendar</a>
                                    <a href="{{ route('admin.projects.timeline') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Timeline</a>
                                    <a href="{{ route('admin.projects.templates.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Templates</a>
                                    <a href="{{ route('admin.projects.teams.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Teams</a>
                                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::MANAGE_MEMBERS))
                                        <a href="{{ route('admin.projects.guests.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Guests</a>
                                    @endif
                                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::VIEW_WORKLOAD))
                                        <a href="{{ route('admin.projects.controls.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Controls</a>
                                    @endif
                                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::VIEW_REPORTS))
                                        <a href="{{ route('admin.projects.reports.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Reports</a>
                                    @endif
                                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CREATE))
                                        <a href="{{ route('admin.projects.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">New Project</a>
                                    @endif
                                </div>
                            </div>
                        @endif

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
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/submissions*') ? 'bg-indigo-800' : '' }}">
                                Abstracts
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="{{ route('admin.submissions.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Dashboard</a>
                                <a href="{{ route('admin.submissions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Submissions</a>
                                <a href="{{ route('admin.submissions.kanban') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Kanban</a>
                                <a href="{{ route('admin.submissions.types.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Submission Types</a>
                                <a href="{{ route('admin.submissions.reviewers.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Reviewers</a>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="dropdown relative">
                            <button class="px-3 py-2 rounded-lg hover:bg-indigo-800 transition text-sm flex items-center {{ request()->is('admin/event-settings*') || request()->is('admin/files*') || request()->is('admin/memberships*') || request()->is('admin/event-urls*') || request()->is('admin/badge-designs*') || request()->is('admin/gallery*') || request()->is('admin/marketing-assets*') || request()->is('admin/custom-forms*') || request()->is('admin/registration-categories*') || request()->is('admin/sponsors*') || request()->is('admin/partners*') || request()->is('admin/registration-statuses*') || request()->is('admin/personas*') || request()->is('admin/category-types*') || request()->is('admin/product-types*') || request()->is('admin/exhibitor-tags*') || request()->is('admin/booth-types*') || request()->is('admin/exhibitor-types*') || request()->is('admin/industries*') || request()->is('admin/business-activities*') || request()->is('admin/group-types*') ? 'bg-indigo-800' : '' }}">
                                Settings
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu absolute right-0 z-50 mt-2 hidden max-h-[calc(100vh-6rem)] w-72 overflow-y-auto rounded-xl bg-white py-2 shadow-xl ring-1 ring-black/5">
                                <a href="{{ route('admin.event-settings.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Event Settings</a>
                                <details class="group border-y border-gray-100">
                                    <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-indigo-50">
                                        Categories
                                        <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </summary>
                                    <div class="bg-gray-50 py-1">
                                        <a href="{{ route('admin.registration-categories.index') }}" class="block px-7 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-700">Registration Categories</a>
                                    </div>
                                </details>
                                <details class="group border-b border-gray-100">
                                    <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-indigo-50">
                                        Parameters
                                        <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </summary>
                                    <div class="bg-gray-50 py-1">
                                        @foreach([
                                            ['route' => 'admin.sponsors.index', 'label' => 'Sponsors'],
                                            ['route' => 'admin.partners.index', 'label' => 'Partners'],
                                            ['route' => 'admin.registration-statuses.index', 'label' => 'Registration Statuses'],
                                            ['route' => 'admin.personas.index', 'label' => 'Personas'],
                                            ['route' => 'admin.category-types.index', 'label' => 'Category Types'],
                                            ['route' => 'admin.product-types.index', 'label' => 'Product Types'],
                                            ['route' => 'admin.exhibitor-tags.index', 'label' => 'Exhibitor Tags'],
                                            ['route' => 'admin.booth-types.index', 'label' => 'Booth Types'],
                                            ['route' => 'admin.exhibitor-types.index', 'label' => 'Exhibitor Types'],
                                            ['route' => 'admin.industries.index', 'label' => 'Industries'],
                                            ['route' => 'admin.business-activities.index', 'label' => 'Business Activities'],
                                            ['route' => 'admin.group-types.index', 'label' => 'Group Types'],
                                        ] as $parameter)
                                            <a href="{{ route($parameter['route']) }}" class="block px-7 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-700">{{ $parameter['label'] }}</a>
                                        @endforeach
                                    </div>
                                </details>
                                <a href="{{ route('admin.custom-forms.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Custom Questions</a>
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
                <p class="px-3 py-1 text-xs text-indigo-300 uppercase">Sales</p>
                <a href="{{ route('admin.sales.dashboard') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Dashboard</a>
                <a href="{{ route('admin.sales.pipeline-types.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Pipeline Types</a>
                <a href="{{ route('admin.sales.pipelines.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Pipelines</a>
                <a href="{{ route('admin.sales.deals.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Deals</a>
                <a href="{{ route('admin.sales.inquiry-forms.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Inquiry Forms</a>
                <a href="{{ route('admin.sales.submissions.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Form Submissions</a>

                @if(config('modules.finance.enabled') && app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::VIEW))
                    <div class="border-t border-indigo-700 my-2"></div>
                    <p class="px-3 py-1 text-xs text-indigo-300 uppercase">Finance</p>
                    <a href="{{ route('admin.finance.dashboard') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Overview</a>
                    <a href="{{ route('admin.finance.transactions.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Transactions</a>
                    <a href="{{ route('admin.finance.income.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Income</a>
                    <a href="{{ route('admin.finance.expenses.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Expenses</a>
                    <a href="{{ route('admin.finance.invoices.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Invoices</a>
                    <a href="{{ route('admin.finance.bills.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Bills</a>
                    <a href="{{ route('admin.finance.payments.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Payments</a>
                    <a href="{{ route('admin.finance.refunds.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Refunds</a>
                    <a href="{{ route('admin.finance.budgets.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Budgets</a>
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::APPROVE))
                        <a href="{{ route('admin.finance.approvals.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Approvals</a>
                    @endif
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::RECONCILE))
                        <a href="{{ route('admin.finance.reconciliation.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Reconciliation</a>
                    @endif
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::VIEW_REPORTS))
                        <a href="{{ route('admin.finance.reports.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Reports</a>
                    @endif
                    <a href="{{ route('admin.finance.accounts.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Accounts</a>
                    <a href="{{ route('admin.finance.vendors.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Vendors</a>
                    <a href="{{ route('admin.finance.categories.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Categories</a>
                @endif

                @if(config('modules.projects.enabled') && app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::VIEW))
                    <div class="border-t border-indigo-700 my-2"></div>
                    <p class="px-3 py-1 text-xs text-indigo-300 uppercase">Projects</p>
                    <a href="{{ route('admin.projects.dashboard') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Overview</a>
                    <a href="{{ route('admin.projects.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">All Projects</a>
                    <a href="{{ route('admin.projects.calendar') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Calendar</a>
                    <a href="{{ route('admin.projects.timeline') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Timeline</a>
                    <a href="{{ route('admin.projects.templates.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Templates</a>
                    <a href="{{ route('admin.projects.teams.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Teams</a>
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::MANAGE_MEMBERS))
                        <a href="{{ route('admin.projects.guests.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Guests</a>
                    @endif
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::VIEW_WORKLOAD))
                        <a href="{{ route('admin.projects.controls.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Controls</a>
                    @endif
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::VIEW_REPORTS))
                        <a href="{{ route('admin.projects.reports.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Reports</a>
                    @endif
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('projects', \App\Projects\Enums\ProjectAbility::CREATE))
                        <a href="{{ route('admin.projects.create') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">New Project</a>
                    @endif
                @endif

                <div class="border-t border-indigo-700 my-2"></div>
                <p class="px-3 py-1 text-xs text-indigo-300 uppercase">Abstracts</p>
                <a href="{{ route('admin.submissions.dashboard') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Dashboard</a>
                <a href="{{ route('admin.submissions.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Submissions</a>
                <a href="{{ route('admin.submissions.types.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Submission Types</a>
                <a href="{{ route('admin.submissions.reviewers.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Reviewers</a>
                
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
                <details class="rounded-lg bg-indigo-900/30">
                    <summary class="flex cursor-pointer list-none items-center justify-between rounded px-3 py-2 text-sm font-semibold hover:bg-indigo-700">
                        Categories
                        <span aria-hidden="true">⌄</span>
                    </summary>
                    <a href="{{ route('admin.registration-categories.index') }}" class="block rounded px-6 py-2 text-sm text-indigo-100 hover:bg-indigo-700">Registration Categories</a>
                </details>
                <details class="rounded-lg bg-indigo-900/30">
                    <summary class="flex cursor-pointer list-none items-center justify-between rounded px-3 py-2 text-sm font-semibold hover:bg-indigo-700">
                        Parameters
                        <span aria-hidden="true">⌄</span>
                    </summary>
                    <div class="pb-1">
                        @foreach([
                            ['route' => 'admin.sponsors.index', 'label' => 'Sponsors'],
                            ['route' => 'admin.partners.index', 'label' => 'Partners'],
                            ['route' => 'admin.registration-statuses.index', 'label' => 'Registration Statuses'],
                            ['route' => 'admin.personas.index', 'label' => 'Personas'],
                            ['route' => 'admin.category-types.index', 'label' => 'Category Types'],
                            ['route' => 'admin.product-types.index', 'label' => 'Product Types'],
                            ['route' => 'admin.exhibitor-tags.index', 'label' => 'Exhibitor Tags'],
                            ['route' => 'admin.booth-types.index', 'label' => 'Booth Types'],
                            ['route' => 'admin.exhibitor-types.index', 'label' => 'Exhibitor Types'],
                            ['route' => 'admin.industries.index', 'label' => 'Industries'],
                            ['route' => 'admin.business-activities.index', 'label' => 'Business Activities'],
                            ['route' => 'admin.group-types.index', 'label' => 'Group Types'],
                        ] as $parameter)
                            <a href="{{ route($parameter['route']) }}" class="block rounded px-6 py-2 text-sm text-indigo-100 hover:bg-indigo-700">{{ $parameter['label'] }}</a>
                        @endforeach
                    </div>
                </details>
                <a href="{{ route('admin.custom-forms.index') }}" class="block px-3 py-2 rounded text-sm hover:bg-indigo-700">Custom Questions</a>
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
    <main class="min-h-screen min-w-0 overflow-x-hidden">
        <div class="mx-auto min-w-0 max-w-7xl px-4 py-6 sm:px-6">
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

    @stack('scripts')
    @yield('scripts')
</body>
</html>
