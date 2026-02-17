@extends('admin.layout')

@section('title', 'Agenda')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Agenda</h1>
        <p class="mt-2 text-gray-600">Manage event sessions and schedule</p>
    </div>
    <a href="{{ route('admin.agenda.create') }}" 
       class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
        Add Session
    </a>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="divide-y divide-gray-200">
        @forelse($agendaItems as $item)
            <div class="p-6 hover:bg-gray-50 transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm font-semibold text-gray-500 bg-gray-100 px-3 py-1 rounded">
                                {{ $item->start_time->format('M d, g:i A') }}
                            </span>
                            @if($item->is_featured)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                    Featured
                                </span>
                            @endif
                            @if($item->is_break)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                    Break
                                </span>
                            @endif
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $item->title }}</h3>
                        @if($item->description)
                            <p class="text-gray-600 mb-3">{{ Str::limit($item->description, 150) }}</p>
                        @endif
                        <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $item->duration }} min
                            </div>
                            @if($item->category)
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-1" style="background-color: {{ $item->category->color }}"></span>
                                    {{ $item->category->name }}
                                </div>
                            @endif
                            @if($item->capacity)
                                <div>Capacity: {{ $item->capacity }}</div>
                            @endif
                            @if($item->level)
                                <div>Level: {{ $item->level }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="ml-6 flex flex-col items-end space-y-2">
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                            {{ ucfirst($item->session_type) }}
                        </span>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.agenda.edit', $item) }}" 
                               class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                            <form action="{{ route('admin.agenda.destroy', $item) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Are you sure?')"
                                        class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-gray-500">
                No sessions found. <a href="{{ route('admin.agenda.create') }}" class="text-indigo-600 hover:text-indigo-800">Create one</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
