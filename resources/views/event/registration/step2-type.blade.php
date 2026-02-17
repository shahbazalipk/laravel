<h2 class="text-2xl font-bold text-gray-900 mb-6">Registration Type</h2>

<div class="space-y-6">
    <p class="text-gray-600">Please select how you would like to register for this event.</p>

    <!-- Registration Type Selection -->
    <div class="space-y-4">
        <!-- Individual Registration -->
        <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition">
            <input type="radio" 
                   name="registration_type" 
                   value="individual" 
                   {{ old('registration_type', $formData['registration_type'] ?? 'individual') == 'individual' ? 'checked' : '' }}
                   class="mt-1 w-5 h-5 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                   onchange="updateRegistrationType('individual')">
            <div class="ml-4 flex-1">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="font-semibold text-gray-900">Individual Registration</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">Register as an individual attendee</p>
            </div>
        </label>

        <!-- Exhibitor Registration -->
        @if($exhibitors->isNotEmpty())
        <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition">
            <input type="radio" 
                   name="registration_type" 
                   value="exhibitor" 
                   {{ old('registration_type', $formData['registration_type'] ?? '') == 'exhibitor' ? 'checked' : '' }}
                   class="mt-1 w-5 h-5 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                   onchange="updateRegistrationType('exhibitor')">
            <div class="ml-4 flex-1">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="font-semibold text-gray-900">Exhibitor Registration</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">Register as part of an exhibiting company</p>
            </div>
        </label>
        @endif

        <!-- Group Registration -->
        @if($groups->isNotEmpty())
        <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition">
            <input type="radio" 
                   name="registration_type" 
                   value="group" 
                   {{ old('registration_type', $formData['registration_type'] ?? '') == 'group' ? 'checked' : '' }}
                   class="mt-1 w-5 h-5 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                   onchange="updateRegistrationType('group')">
            <div class="ml-4 flex-1">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="font-semibold text-gray-900">Group Registration</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">Register as part of a group or organization</p>
            </div>
        </label>
        @endif
    </div>

    <!-- Exhibitor Selection (conditional) -->
    @if($exhibitors->isNotEmpty())
    <div id="exhibitor_selection" class="hidden">
        <label for="exhibitor_id" class="block text-sm font-medium text-gray-700 mb-2">
            Select Exhibitor <span class="text-red-500">*</span>
        </label>
        <select name="exhibitor_id" 
                id="exhibitor_id"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                onchange="loadExhibitorData(this.value)">
            <option value="">Select an exhibitor</option>
            @foreach($exhibitors as $exhibitor)
                <option value="{{ $exhibitor->id }}" 
                        {{ old('exhibitor_id', $formData['exhibitor_id'] ?? '') == $exhibitor->id ? 'selected' : '' }}>
                    {{ $exhibitor->company_name }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Your company information will be pre-filled based on the exhibitor profile.</p>
        @error('exhibitor_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endif

    <!-- Group Selection (conditional) -->
    @if($groups->isNotEmpty())
    <div id="group_selection" class="hidden">
        <label for="group_id" class="block text-sm font-medium text-gray-700 mb-2">
            Select Group <span class="text-red-500">*</span>
        </label>
        <select name="group_id" 
                id="group_id"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                onchange="loadGroupData(this.value)">
            <option value="">Select a group</option>
            @foreach($groups as $group)
                <option value="{{ $group->id }}" 
                        {{ old('group_id', $formData['group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                    {{ $group->group_name }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Your organization information will be pre-filled based on the group profile.</p>
        @error('group_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endif
</div>

<script>
    function updateRegistrationType(type) {
        // Hide all conditional sections
        const exhibitorSection = document.getElementById('exhibitor_selection');
        const groupSection = document.getElementById('group_selection');
        
        if (exhibitorSection) exhibitorSection.classList.add('hidden');
        if (groupSection) groupSection.classList.add('hidden');
        
        // Clear selections
        const exhibitorSelect = document.getElementById('exhibitor_id');
        const groupSelect = document.getElementById('group_id');
        
        if (exhibitorSelect) {
            exhibitorSelect.value = '';
            exhibitorSelect.required = false;
        }
        if (groupSelect) {
            groupSelect.value = '';
            groupSelect.required = false;
        }
        
        // Show relevant section
        if (type === 'exhibitor' && exhibitorSection) {
            exhibitorSection.classList.remove('hidden');
            if (exhibitorSelect) exhibitorSelect.required = true;
        } else if (type === 'group' && groupSection) {
            groupSection.classList.remove('hidden');
            if (groupSelect) groupSelect.required = true;
        }
    }
    
    function loadExhibitorData(exhibitorId) {
        if (!exhibitorId) return;
        
        // TODO: Fetch exhibitor data and pre-fill company fields in step 4
        console.log('Loading exhibitor data:', exhibitorId);
    }
    
    function loadGroupData(groupId) {
        if (!groupId) return;
        
        // TODO: Fetch group data and pre-fill company fields in step 4
        console.log('Loading group data:', groupId);
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const selectedType = document.querySelector('input[name="registration_type"]:checked');
        if (selectedType) {
            updateRegistrationType(selectedType.value);
        }
    });
</script>
