<h2 class="text-2xl font-bold text-gray-900 mb-6">Select Registration Category</h2>

<div class="space-y-6">
    <!-- Category Selection -->
    <div>
        <label for="registration_category_id" class="block text-sm font-medium text-gray-700 mb-2">
            Registration Category <span class="text-red-500">*</span>
        </label>
        <select name="registration_category_id" 
                id="registration_category_id"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                required>
            <option value="">Select a category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" 
                        {{ old('registration_category_id', $formData['registration_category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }} - {{ number_format($category->price, 2) }} {{ $category->currency ?? $event->currency }}
                </option>
            @endforeach
        </select>
        @error('registration_category_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Category Details (shown after selection) -->
    <div id="category-details" class="hidden bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Category Details</h3>
        
        <div id="category-description" class="text-gray-700 mb-4"></div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Valid From:</span>
                <span id="category-valid-from" class="font-medium ml-2">-</span>
            </div>
            <div>
                <span class="text-gray-600">Valid To:</span>
                <span id="category-valid-to" class="font-medium ml-2">-</span>
            </div>
            <div>
                <span class="text-gray-600">Capacity:</span>
                <span id="category-capacity" class="font-medium ml-2">-</span>
            </div>
            <div>
                <span class="text-gray-600">Remaining:</span>
                <span id="category-remaining" class="font-medium ml-2">-</span>
            </div>
        </div>

        <!-- Capacity Warning -->
        <div id="capacity-warning" class="hidden mt-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
            <p class="text-sm">⚠️ Limited spots remaining! Register soon to secure your place.</p>
        </div>

        <!-- Full Warning -->
        <div id="full-warning" class="hidden mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
            <p class="text-sm">❌ This category is currently full. Please select another category.</p>
        </div>
    </div>

    <!-- Category Password (conditional) -->
    <div id="category_password_field" class="hidden">
        <label for="category_password" class="block text-sm font-medium text-gray-700 mb-2">
            Category Password <span class="text-red-500">*</span>
        </label>
        <input type="password" 
               name="category_password" 
               id="category_password" 
               value="{{ old('category_password', $formData['category_password'] ?? '') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
               placeholder="Enter category password">
        <p class="mt-1 text-xs text-gray-500">This category requires a password to register.</p>
        @error('category_password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Membership ID (conditional) -->
    <div id="membership_id_field" class="hidden">
        <label for="membership_id" class="block text-sm font-medium text-gray-700 mb-2">
            Membership ID <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               name="membership_id" 
               id="membership_id" 
               value="{{ old('membership_id', $formData['membership_id'] ?? '') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
               placeholder="Enter your membership ID">
        <p class="mt-1 text-xs text-gray-500">This category requires a valid membership ID.</p>
        @error('membership_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Professional/Student ID (conditional) -->
    <div id="professional_student_id_field" class="hidden">
        <label for="professional_student_id" class="block text-sm font-medium text-gray-700 mb-2">
            Professional/Student ID <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               name="professional_student_id" 
               id="professional_student_id" 
               value="{{ old('professional_student_id', $formData['professional_student_id'] ?? '') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
               placeholder="Enter your professional or student ID">
        <p class="mt-1 text-xs text-gray-500" id="professional_id_message">This category requires a professional or student ID.</p>
        @error('professional_student_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
    // Update category details display
    document.getElementById('registration_category_id').addEventListener('change', function(e) {
        const categoryId = e.target.value;
        if (!categoryId) {
            document.getElementById('category-details').classList.add('hidden');
            return;
        }
        
        // This will be handled by the main form's loadCategoryDetails method
        // But we also update the UI here
        fetch(`{{ url('register/category') }}/${categoryId}`)
            .then(response => response.json())
            .then(data => {
                const details = document.getElementById('category-details');
                details.classList.remove('hidden');
                
                // Update description
                document.getElementById('category-description').textContent = data.category.description || 'No description available.';
                
                // Update dates
                document.getElementById('category-valid-from').textContent = data.category.valid_from || 'N/A';
                document.getElementById('category-valid-to').textContent = data.category.valid_to || 'N/A';
                
                // Update capacity
                document.getElementById('category-capacity').textContent = data.category.capacity || 'Unlimited';
                document.getElementById('category-remaining').textContent = data.remaining_capacity !== null ? data.remaining_capacity : 'Unlimited';
                
                // Show warnings
                const warningDiv = document.getElementById('capacity-warning');
                const fullDiv = document.getElementById('full-warning');
                
                warningDiv.classList.add('hidden');
                fullDiv.classList.add('hidden');
                
                if (data.is_full) {
                    fullDiv.classList.remove('hidden');
                    document.getElementById('btn-next').disabled = true;
                } else {
                    document.getElementById('btn-next').disabled = false;
                    if (data.remaining_capacity !== null && data.remaining_capacity <= 10) {
                        warningDiv.classList.remove('hidden');
                    }
                }
                
                // Update conditional field messages
                if (data.category.professional_student_id_message) {
                    document.getElementById('professional_id_message').textContent = data.category.professional_student_id_message;
                }
            })
            .catch(error => {
                console.error('Error loading category details:', error);
            });
    });
</script>
