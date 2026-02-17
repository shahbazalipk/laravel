<h2 class="text-lg font-semibold text-gray-800 mb-4">Dates & Times</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
            Start Date
        </label>
        <input type="datetime-local" 
               name="start_date" 
               id="start_date" 
               value="{{ old('start_date', $event->start_date?->format('Y-m-d\TH:i')) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('start_date') border-red-500 @enderror">
        @error('start_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
            End Date
        </label>
        <input type="datetime-local" 
               name="end_date" 
               id="end_date" 
               value="{{ old('end_date', $event->end_date?->format('Y-m-d\TH:i')) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('end_date') border-red-500 @enderror">
        @error('end_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="online_reg_close" class="block text-sm font-medium text-gray-700 mb-2">
            Online Registration Close
        </label>
        <input type="datetime-local" 
               name="online_reg_close" 
               id="online_reg_close" 
               value="{{ old('online_reg_close', $event->online_reg_close?->format('Y-m-d\TH:i')) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('online_reg_close') border-red-500 @enderror">
        @error('online_reg_close')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">
            Timezone
        </label>
        <input type="text" 
               name="timezone" 
               id="timezone" 
               value="{{ old('timezone', $event->timezone) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('timezone') border-red-500 @enderror"
               placeholder="e.g., Asia/Dubai">
        @error('timezone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
