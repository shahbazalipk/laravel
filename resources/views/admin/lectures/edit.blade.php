@extends('admin.layout')

@section('title', 'Edit Lecture')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.lectures.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Lecture</h1>
            <p class="text-gray-600 mt-1">Update lecture details</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.lectures.update', $lecture) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">
                    Topic <span class="text-red-500">*</span>
                </label>
                <input type="text" name="topic" id="topic" value="{{ old('topic', $lecture->topic) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('topic') border-red-500 @enderror" required>
                @error('topic')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $lecture->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="session_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Session <span class="text-red-500">*</span>
                    </label>
                    <select name="session_id" id="session_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('session_id') border-red-500 @enderror" required>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('session_id', $lecture->session_id) == $session->id ? 'selected' : '' }}>
                                {{ $session->title }} ({{ $session->start_time->format('M d, g:i A') }})
                            </option>
                        @endforeach
                    </select>
                    @error('session_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="speaker_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Speaker <span class="text-red-500">*</span>
                    </label>
                    <select name="speaker_id" id="speaker_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('speaker_id') border-red-500 @enderror" required>
                        @foreach($speakers as $speaker)
                            <option value="{{ $speaker->id }}" {{ old('speaker_id', $lecture->speaker_id) == $speaker->id ? 'selected' : '' }}>{{ $speaker->full_name }}</option>
                        @endforeach
                    </select>
                    @error('speaker_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Location <span class="text-red-500">*</span>
                </label>
                <select name="location_id" id="location_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('location_id') border-red-500 @enderror" required>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('location_id', $lecture->location_id) == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                    @endforeach
                </select>
                @error('location_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Start Time <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time', $lecture->start_time->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('start_time') border-red-500 @enderror" required>
                    @error('start_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                        End Time <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time', $lecture->end_time->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('end_time') border-red-500 @enderror" required>
                    @error('end_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.lectures.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Update Lecture</button>
        </div>
    </form>
</div>
@endsection
