@extends('admin.layout')

@section('title', 'Edit Session')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.sessions.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Session</h1>
            <p class="text-gray-600 mt-1">Update session details</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.sessions.update', $session) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $session->title) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('title') border-red-500 @enderror" required>
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $session->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type <span class="text-red-500">*</span></label>
                    <select name="type" id="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('type') border-red-500 @enderror" required>
                        <option value="talk" {{ old('type', $session->type) === 'talk' ? 'selected' : '' }}>Talk</option>
                        <option value="panel" {{ old('type', $session->type) === 'panel' ? 'selected' : '' }}>Panel</option>
                        <option value="workshop" {{ old('type', $session->type) === 'workshop' ? 'selected' : '' }}>Workshop</option>
                        <option value="break" {{ old('type', $session->type) === 'break' ? 'selected' : '' }}>Break</option>
                        <option value="networking" {{ old('type', $session->type) === 'networking' ? 'selected' : '' }}>Networking</option>
                        <option value="keynote" {{ old('type', $session->type) === 'keynote' ? 'selected' : '' }}>Keynote</option>
                    </select>
                    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="agenda_id" class="block text-sm font-medium text-gray-700 mb-2">Agenda <span class="text-red-500">*</span></label>
                    <select name="agenda_id" id="agenda_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('agenda_id') border-red-500 @enderror" required>
                        @foreach($agendas as $agenda)
                            <option value="{{ $agenda->id }}" {{ old('agenda_id', $session->agenda_id) == $agenda->id ? 'selected' : '' }}>{{ $agenda->title }}</option>
                        @endforeach
                    </select>
                    @error('agenda_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="track_id" class="block text-sm font-medium text-gray-700 mb-2">Track</label>
                    <select name="track_id" id="track_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('track_id') border-red-500 @enderror">
                        <option value="">No track</option>
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}" {{ old('track_id', $session->track_id) == $track->id ? 'selected' : '' }}>{{ $track->name }}</option>
                        @endforeach
                    </select>
                    @error('track_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">Location <span class="text-red-500">*</span></label>
                    <select name="location_id" id="location_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('location_id') border-red-500 @enderror" required>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id', $session->location_id) == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Start Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time', $session->start_time->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('start_time') border-red-500 @enderror" required>
                    @error('start_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">End Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time', $session->end_time->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('end_time') border-red-500 @enderror" required>
                    @error('end_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="max_attendees" class="block text-sm font-medium text-gray-700 mb-2">Maximum Attendees</label>
                <input type="number" name="max_attendees" id="max_attendees" value="{{ old('max_attendees', $session->max_attendees) }}" min="1"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('max_attendees') border-red-500 @enderror">
                @error('max_attendees')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Speakers</label>
                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-4">
                    @foreach($speakers as $speaker)
                        <label class="flex items-center">
                            <input type="checkbox" name="speaker_ids[]" value="{{ $speaker->id }}"
                                   {{ in_array($speaker->id, old('speaker_ids', $session->speakers->pluck('id')->toArray())) ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $speaker->full_name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('speaker_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.sessions.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Update Session</button>
        </div>
    </form>
</div>
@endsection
