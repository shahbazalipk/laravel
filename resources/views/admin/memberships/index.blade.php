@extends('admin.layout')

@section('title', 'Membership Lists')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" data-testid="memberships-page">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Membership Lists</h1>
        <p class="mt-1 text-gray-600">
            Manage Membership ID, Email ID, or Student ID lists via CSV upload or third-party API for future registration checks.
        </p>
    </div>
    <a href="{{ route('admin.memberships.create') }}"
       class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-white transition hover:bg-indigo-700"
       data-testid="memberships-create">
        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add List
    </a>
</div>

@if($memberships->isEmpty())
    <div class="rounded-lg bg-white p-12 text-center shadow-sm" data-testid="memberships-empty">
        <h3 class="mb-2 text-lg font-semibold text-gray-800">No membership lists yet</h3>
        <p class="mb-4 text-gray-600">Create a list and upload CSV identifiers, or connect a verification API.</p>
        <a href="{{ route('admin.memberships.create') }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Add First List</a>
    </div>
@else
    <div class="overflow-hidden rounded-lg bg-white shadow-sm" data-testid="memberships-table">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Identifier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Records</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($memberships as $membership)
                        <tr class="transition hover:bg-gray-50" data-testid="membership-row-{{ $membership->id }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="mr-3 h-3 w-3 rounded-full" style="background-color: {{ $membership->color }}"></div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $membership->name }}</div>
                                        @if($membership->description)
                                            <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($membership->description, 40) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $membership->identifierType()->label() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($membership->isUploadFile())
                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">CSV / TXT list</span>
                                @else
                                    <span class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-800">Third-party API</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $membership->codes_count ?? $membership->codes()->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form action="{{ route('admin.memberships.toggle', $membership) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="focus:outline-none">
                                        @if($membership->is_active)
                                            <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 hover:bg-green-200">Active</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800 hover:bg-gray-200">Inactive</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.memberships.show', $membership) }}" class="text-indigo-600 hover:text-indigo-900" title="View list">View</a>
                                    <a href="{{ route('admin.memberships.edit', $membership) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit">Edit</a>
                                    <form action="{{ route('admin.memberships.destroy', $membership) }}" method="POST" class="inline" onsubmit="return confirm('Delete this membership list?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
