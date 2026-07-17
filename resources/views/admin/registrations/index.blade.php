@extends('admin.layout')

@section('title', 'Registrations')

@section('content')
<!-- Header Section -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Registrations</h1>
        <p class="text-gray-600 mt-1">Manage event registrations and incomplete online drafts</p>
    </div>
    <a href="{{ route('admin.registrations.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Registration
    </a>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Registrations</p>
                <p class="text-2xl font-bold text-gray-900">{{ $statistics['total'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6" data-testid="drafts-stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Drafts started</p>
                <p class="text-2xl font-bold text-violet-600">{{ $statistics['drafts'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-violet-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Paid</p>
                <p class="text-2xl font-bold text-green-600">{{ $statistics['paid'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $statistics['pending'] }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Checked In</p>
                <p class="text-2xl font-bold text-blue-600">{{ $statistics['checked_in'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('admin.registrations.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7" data-testid="registrations-filters">
        <div class="sm:col-span-2 lg:col-span-1">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text"
                   name="search"
                   id="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="Name, email, company..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        <div>
            <label for="stage" class="block text-sm font-medium text-gray-700 mb-2">Stage</label>
            <select name="stage"
                    id="stage"
                    data-testid="stage-filter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="all" {{ ($filters['stage'] ?? 'all') == 'all' ? 'selected' : '' }}>All</option>
                <option value="draft" {{ ($filters['stage'] ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="registered" {{ ($filters['stage'] ?? '') == 'registered' ? 'selected' : '' }}>Registered</option>
            </select>
        </div>

        <div>
            <label for="abandoned_step" class="block text-sm font-medium text-gray-700 mb-2">Left at step</label>
            <select name="abandoned_step"
                    id="abandoned_step"
                    data-testid="abandoned-step-filter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All Steps</option>
                @foreach($registrationSteps as $step)
                    <option value="{{ $step->value }}" {{ ($filters['abandoned_step'] ?? '') === $step->value ? 'selected' : '' }}>
                        Step {{ $step->number() }}: {{ $step->label() }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Applies to incomplete drafts</p>
        </div>

        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category_id"
                    id="category_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
            <select name="payment_status"
                    id="payment_status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All Statuses</option>
                <option value="paid" {{ ($filters['payment_status'] ?? '') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ ($filters['payment_status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ ($filters['payment_status'] ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="refunded" {{ ($filters['payment_status'] ?? '') == 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
        </div>

        <div>
            <label for="registration_type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select name="registration_type"
                    id="registration_type"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All Types</option>
                <option value="individual" {{ ($filters['registration_type'] ?? '') == 'individual' ? 'selected' : '' }}>Individual</option>
                <option value="exhibitor" {{ ($filters['registration_type'] ?? '') == 'exhibitor' ? 'selected' : '' }}>Exhibitor</option>
                <option value="group" {{ ($filters['registration_type'] ?? '') == 'group' ? 'selected' : '' }}>Group</option>
            </select>
        </div>

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
            <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Apply
            </button>
            <a href="{{ route('admin.registrations.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
               aria-label="Clear filters">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Registrations Table -->
@if($registrations->isEmpty())
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Registrations Found</h3>
        <p class="text-gray-600">No registrations match your current filters.</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow-sm overflow-hidden" data-testid="registrations-table">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($registrations as $row)
                    <tr class="hover:bg-gray-50 transition" data-testid="registration-row-{{ $row->kind }}" data-stage="{{ $row->stage }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $row->reference }}</div>
                            <div class="text-xs text-gray-500">{{ $row->createdAt->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $row->name }}</div>
                            <div class="text-xs text-gray-500">{{ $row->company ?: '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $row->email }}</div>
                            <div class="text-xs text-gray-500">{{ $row->phone ?: '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $row->categoryName ?: '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($row->kind === 'draft')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-violet-100 text-violet-800" data-testid="draft-stage-badge">
                                    Draft
                                </span>
                                @if($row->isExpired)
                                    <span class="mt-1 block px-2 py-0.5 inline-flex text-[11px] leading-4 font-semibold rounded-full bg-red-50 text-red-700">
                                        Expired
                                    </span>
                                @endif
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                    Registered
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($row->paymentStatus)
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $row->paymentStatus === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $row->paymentStatus === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $row->paymentStatus === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $row->paymentStatus === 'refunded' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst($row->paymentStatus) }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($row->stepLabel)
                                <span class="text-sm text-violet-700">{{ $row->stepLabel }}</span>
                            @elseif($row->checkInLabel)
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $row->checkInLabel === 'Checked In' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $row->checkInLabel }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ $row->showUrl }}"
                                   class="text-indigo-600 hover:text-indigo-900 transition"
                                   title="View"
                                   data-testid="view-{{ $row->kind }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                @if($row->editUrl)
                                    <a href="{{ $row->editUrl }}"
                                       class="text-indigo-600 hover:text-indigo-900 transition"
                                       title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $registrations->withQueryString()->links() }}
    </div>

    <div class="mt-4 text-sm text-gray-600">
        Total: {{ $registrations->total() }} record{{ $registrations->total() !== 1 ? 's' : '' }}
    </div>
@endif
@endsection
