<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - {{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Event Header with Branding -->
            <div class="bg-white shadow-lg rounded-lg mb-6 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 text-white">
                    <h1 class="text-3xl font-bold">{{ $event->title }}</h1>
                    <p class="mt-2 text-indigo-100">{{ $event->seo_description ?? 'Join us for an amazing event experience' }}</p>
                </div>
                
                <div class="px-8 py-6 bg-gray-50 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Event Dates -->
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Event Date</p>
                                <p class="text-sm text-gray-900 font-semibold">
                                    {{ $event->start_date->format('M d, Y') }}
                                    @if($event->end_date && !$event->start_date->isSameDay($event->end_date))
                                        - {{ $event->end_date->format('M d, Y') }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-1">{{ $event->start_date->format('g:i A') }}</p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Location</p>
                                <p class="text-sm text-gray-900 font-semibold">{{ $event->location }}</p>
                                @if($event->country)
                                    <p class="text-xs text-gray-500 mt-1">{{ $event->country }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Event Type -->
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Event Format</p>
                                <p class="text-sm text-gray-900 font-semibold capitalize">{{ $event->format ?? $event->event_mode }}</p>
                                @if($event->type)
                                    <p class="text-xs text-gray-500 mt-1 capitalize">{{ $event->type }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($eventUrl && $eventUrl->description)
                    <div class="px-8 py-4 bg-indigo-50 border-b border-indigo-100">
                        <p class="text-sm text-indigo-900">
                            <span class="font-semibold">{{ $eventUrl->name }}:</span> {{ $eventUrl->description }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Registration Form -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <form action="{{ url()->current() }}" method="POST" enctype="multipart/form-data" id="registrationForm">
                    @csrf

                    <!-- Email Address Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Contact Information</h2>
                        
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Profile Picture Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Profile Picture</h2>
                        
                        <div class="flex flex-col items-center space-y-4">
                            <!-- Preview -->
                            <div id="preview" class="hidden">
                                <img id="previewImage" class="w-32 h-32 rounded-full object-cover border-4 border-gray-200" alt="Preview">
                            </div>
                            
                            <!-- Camera Capture -->
                            <div id="cameraSection" class="hidden w-full">
                                <video id="video" class="w-full max-w-md mx-auto rounded-lg" autoplay></video>
                                <div class="flex justify-center gap-4 mt-4">
                                    <button type="button" id="captureBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                        Capture Photo
                                    </button>
                                    <button type="button" id="closeCameraBtn" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                        Close Camera
                                    </button>
                                </div>
                                <canvas id="canvas" class="hidden"></canvas>
                            </div>
                            
                            <!-- Upload Options -->
                            <div id="uploadOptions" class="flex gap-4">
                                <button type="button" id="openCameraBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    Take Photo
                                </button>
                                <label class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 cursor-pointer">
                                    Upload Photo
                                    <input type="file" name="profile_picture" id="fileInput" accept="image/*" class="hidden">
                                </label>
                            </div>
                            
                            <input type="hidden" name="profile_picture_data" id="profilePictureData">
                        </div>
                    </div>

                    <!-- Personal Information Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Personal Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    First Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="first_name" 
                                       id="first_name" 
                                       value="{{ old('first_name') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       required>
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Last Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="last_name" 
                                       id="last_name" 
                                       value="{{ old('last_name') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       required>
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       required>
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="job_title" class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Title
                                </label>
                                <input type="text" 
                                       name="job_title" 
                                       id="job_title" 
                                       value="{{ old('job_title') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('job_title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Company Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="company_name" 
                                       id="company_name" 
                                       value="{{ old('company_name') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       required>
                                @error('company_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="industry_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Industry <span class="text-red-500">*</span>
                                </label>
                                <select name="industry_id" 
                                        id="industry_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                    <option value="">Select Industry</option>
                                    @foreach($industries as $industry)
                                        <option value="{{ $industry->id }}" {{ old('industry_id') == $industry->id ? 'selected' : '' }}>
                                            {{ $industry->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('industry_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Category Selection Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Registration Category</h2>
                        
                        @if($categories->isEmpty())
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-yellow-800">No registration categories available for this URL.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($categories as $category)
                                    <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition">
                                        <input type="radio" 
                                               name="registration_category_id" 
                                               value="{{ $category->id }}"
                                               {{ old('registration_category_id') == $category->id ? 'checked' : '' }}
                                               class="mt-1 mr-4"
                                               required>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h3>
                                                @if($category->price == 0)
                                                    <span class="px-4 py-1 bg-green-100 text-green-800 text-sm font-bold rounded-full">
                                                        FREE
                                                    </span>
                                                @else
                                                    <span class="text-xl font-bold text-indigo-600">
                                                        {{ $category->currency }} {{ number_format($category->price, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                            @include('online.partials.category-copy', ['category' => $category])
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('registration_category_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-8">
                        <label class="flex items-start">
                            <input type="checkbox" 
                                   name="terms_accepted" 
                                   value="1"
                                   {{ old('terms_accepted') ? 'checked' : '' }}
                                   class="mt-1 mr-3"
                                   required>
                            <span class="text-sm text-gray-700">
                                I accept the terms and conditions <span class="text-red-500">*</span>
                            </span>
                        </label>
                        @error('terms_accepted')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                            Complete Registration
                        </button>
                    </div>
                </form>
            </div>

            <!-- Event Information Footer -->
            <div class="mt-6 bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Need Help?</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($event->manager_name)
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-2">Event Manager</p>
                            <p class="text-sm text-gray-900 font-semibold">{{ $event->manager_name }}</p>
                        </div>
                    @endif

                    @if($event->manager_email)
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-2">Contact Email</p>
                            <a href="mailto:{{ $event->manager_email }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                                {{ $event->manager_email }}
                            </a>
                        </div>
                    @endif

                    @if($event->manager_phone)
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-2">Contact Phone</p>
                            <a href="tel:{{ $event->manager_phone }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                                {{ $event->manager_phone }}
                            </a>
                        </div>
                    @endif

                    @if($event->website_url)
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-2">Event Website</p>
                            <a href="{{ $event->website_url }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                                Visit Website →
                            </a>
                        </div>
                    @endif
                </div>

                @if($event->registration_instructions)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm font-medium text-gray-500 mb-2">Registration Instructions</p>
                        <p class="text-sm text-gray-700">{{ $event->registration_instructions }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Camera and file upload handling
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('captureBtn');
        const openCameraBtn = document.getElementById('openCameraBtn');
        const closeCameraBtn = document.getElementById('closeCameraBtn');
        const fileInput = document.getElementById('fileInput');
        const preview = document.getElementById('preview');
        const previewImage = document.getElementById('previewImage');
        const cameraSection = document.getElementById('cameraSection');
        const uploadOptions = document.getElementById('uploadOptions');
        const profilePictureData = document.getElementById('profilePictureData');
        
        let stream = null;

        // Open camera
        openCameraBtn.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                cameraSection.classList.remove('hidden');
                uploadOptions.classList.add('hidden');
            } catch (err) {
                alert('Unable to access camera: ' + err.message);
            }
        });

        // Close camera
        closeCameraBtn.addEventListener('click', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            cameraSection.classList.add('hidden');
            uploadOptions.classList.remove('hidden');
        });

        // Capture photo
        captureBtn.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            const dataUrl = canvas.toDataURL('image/png');
            profilePictureData.value = dataUrl;
            
            previewImage.src = dataUrl;
            preview.classList.remove('hidden');
            
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            cameraSection.classList.add('hidden');
            uploadOptions.classList.remove('hidden');
        });

        // File upload
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
