<h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="event_name" class="block text-sm font-medium text-gray-700 mb-2">
            Event Name
        </label>
        <input type="text" 
               name="event_name" 
               id="event_name" 
               value="{{ old('event_name', $event->event_name) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('event_name') border-red-500 @enderror"
               placeholder="e.g., AEEDC 2020">
        @error('event_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="event_type" class="block text-sm font-medium text-gray-700 mb-2">
            Event Type
        </label>
        <select name="event_type" 
                id="event_type"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('event_type') border-red-500 @enderror">
            <option value="">Select Type</option>
            <option value="Conference" {{ old('event_type', $event->event_type) == 'Conference' ? 'selected' : '' }}>Conference</option>
            <option value="Exhibition" {{ old('event_type', $event->event_type) == 'Exhibition' ? 'selected' : '' }}>Exhibition</option>
            <option value="Seminar" {{ old('event_type', $event->event_type) == 'Seminar' ? 'selected' : '' }}>Seminar</option>
            <option value="Workshop" {{ old('event_type', $event->event_type) == 'Workshop' ? 'selected' : '' }}>Workshop</option>
            <option value="Webinar" {{ old('event_type', $event->event_type) == 'Webinar' ? 'selected' : '' }}>Webinar</option>
        </select>
        @error('event_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="event_mode" class="block text-sm font-medium text-gray-700 mb-2">
            Event Mode
        </label>
        <select name="event_mode" 
                id="event_mode"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('event_mode') border-red-500 @enderror">
            <option value="">Select Mode</option>
            <option value="In-Person" {{ old('event_mode', $event->event_mode) == 'In-Person' ? 'selected' : '' }}>In-Person</option>
            <option value="Virtual" {{ old('event_mode', $event->event_mode) == 'Virtual' ? 'selected' : '' }}>Virtual</option>
            <option value="Hybrid" {{ old('event_mode', $event->event_mode) == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
        </select>
        @error('event_mode')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="stage" class="block text-sm font-medium text-gray-700 mb-2">
            Stage
        </label>
        <select name="stage" 
                id="stage"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('stage') border-red-500 @enderror">
            <option value="">Select Stage</option>
            <option value="Planning" {{ old('stage', $event->stage) == 'Planning' ? 'selected' : '' }}>Planning</option>
            <option value="Live" {{ old('stage', $event->stage) == 'Live' ? 'selected' : '' }}>Live</option>
            <option value="Completed" {{ old('stage', $event->stage) == 'Completed' ? 'selected' : '' }}>Completed</option>
            <option value="Cancelled" {{ old('stage', $event->stage) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        @error('stage')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6">
    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
        Description
    </label>
    <textarea name="description" 
              id="description" 
              rows="4"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
              placeholder="Event description">{{ old('description', $event->description) }}</textarea>
    @error('description')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
