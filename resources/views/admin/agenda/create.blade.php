@extends('admin.layout')

@section('title', 'Create Session')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Create Session</h1>
    <p class="mt-2 text-gray-600">Add a new session to the event agenda</p>
</div>

<div class="bg-white shadow rounded-lg p-6 max-w-3xl">
    <form action="{{ route('admin.agenda.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                <input type="text" name="title" id="title" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       value="{{ old('title') }}">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="track_id" class="block text-sm font-medium text-gray-700">Category/Track</label>
                <select name="track_id" id="track_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('track_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('track_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="session_type" class="block text-sm font-medium text-gray-700">Session Type *</label>
                <select name="session_type" id="session_type" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="presentation" {{ old('session_type') == 'presentation' ? 'selected' : '' }}>Presentation</option>
                    <option value="keynote" {{ old('session_type') == 'keynote' ? 'selected' : '' }}>Keynote</option>
                    <option value="workshop" {{ old('session_type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                    <option value="panel" {{ old('session_type') == 'panel' ? 'selected' : '' }}>Panel</option>
                    <option value="breakout" {{ old('session_type') == 'breakout' ? 'selected' : '' }}>Breakout</option>
                    <option value="networking" {{ old('session_type') == 'networking' ? 'selected' : '' }}>Networking</option>
                    <option value="other" {{ old('session_type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('session_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time *</label>
                <input type="datetime-local" name="start_time" id="start_time" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       value="{{ old('start_time') }}">
                @error('start_time')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time *</label>
                <input type="datetime-local" name="end_time" id="end_time" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       value="{{ old('end_time') }}">
                @error('end_time')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="duration" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                <input type="number" name="duration" id="duration"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       value="{{ old('duration') }}">
                @error('duration')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                <input type="number" name="capacity" id="capacity"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       value="{{ old('capacity') }}">
                @error('capacity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                <select name="level" id="level"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Select Level --</option>
                    <option value="Beginner" {{ old('level') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                    <option value="Intermediate" {{ old('level') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                    <option value="Advanced" {{ old('level') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                </select>
                @error('level')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2 space-y-3">
                <div class="flex items-center">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                           {{ old('is_featured') ? 'checked' : '' }}>
                    <label for="is_featured" class="ml-2 block text-sm text-gray-900">
                        Featured Session
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_break" id="is_break" value="1"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                           {{ old('is_break') ? 'checked' : '' }}>
                    <label for="is_break" class="ml-2 block text-sm text-gray-900">
                        This is a break/networking session
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('admin.agenda.index') }}" 
               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Create Session
            </button>
        </div>
    </form>
</div>
@endsection
