@extends('attendee.layout')

@section('title', $exhibitor->company_name)

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('attendee.exhibitors') }}" 
       class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Exhibitors
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Company Logo & Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
            @if($exhibitor->logo)
                <div class="mb-6">
                    <img src="{{ storage_public_url($exhibitor->logo) }}" 
                         alt="{{ $exhibitor->company_name }}"
                         class="w-full h-auto max-h-64 object-contain">
                </div>
            @endif
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $exhibitor->company_name }}</h1>
            
            @if($exhibitor->tagline)
                <p class="text-gray-600 mb-4">{{ $exhibitor->tagline }}</p>
            @endif

            <div class="space-y-3 mb-6">
                @if($exhibitor->booth_number)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                        <span class="text-gray-700">Booth {{ $exhibitor->booth_number }}</span>
                    </div>
                @endif

                @if($exhibitor->website)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        <a href="{{ $exhibitor->website }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 break-all">
                            {{ str_replace(['http://', 'https://'], '', $exhibitor->website) }}
                        </a>
                    </div>
                @endif

                @if($exhibitor->email)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <a href="mailto:{{ $exhibitor->email }}" class="text-gray-700 hover:text-indigo-600 break-all">
                            {{ $exhibitor->email }}
                        </a>
                    </div>
                @endif

                @if($exhibitor->phone)
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <a href="tel:{{ $exhibitor->phone }}" class="text-gray-700 hover:text-indigo-600">
                            {{ $exhibitor->phone }}
                        </a>
                    </div>
                @endif
            </div>

            <!-- Favorite Button -->
            <button onclick="toggleFavorite('App\\Models\\Exhibitor', {{ $exhibitor->id }}, this)" 
                    class="w-full px-4 py-3 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                Add to Favorites
            </button>
        </div>
    </div>

    <!-- Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- About -->
        @if($exhibitor->description)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">About</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $exhibitor->description }}</p>
        </div>
        @endif

        <!-- Company Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Company Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($exhibitor->exhibitorType)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Exhibitor Type</label>
                        <p class="text-base text-gray-900 mt-1">{{ $exhibitor->exhibitorType->name }}</p>
                    </div>
                @endif

                @if($exhibitor->industry)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Industry</label>
                        <p class="text-base text-gray-900 mt-1">{{ $exhibitor->industry->name }}</p>
                    </div>
                @endif

                @if($exhibitor->boothType)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Booth Type</label>
                        <p class="text-base text-gray-900 mt-1">{{ $exhibitor->boothType->name }}</p>
                    </div>
                @endif

                @if($exhibitor->booth_size)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Booth Size</label>
                        <p class="text-base text-gray-900 mt-1">{{ $exhibitor->booth_size }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Products & Services -->
        @if($exhibitor->productTypes && $exhibitor->productTypes->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Products & Services</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($exhibitor->productTypes as $productType)
                    <span class="px-3 py-2 bg-indigo-50 text-indigo-700 text-sm rounded-lg">
                        {{ $productType->name }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Business Activities -->
        @if($exhibitor->businessActivities && $exhibitor->businessActivities->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Business Activities</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($exhibitor->businessActivities as $activity)
                    <span class="px-3 py-2 bg-green-50 text-green-700 text-sm rounded-lg">
                        {{ $activity->name }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Social Media -->
        @if($exhibitor->social_media_links)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Connect With Us</h2>
            <div class="flex gap-3">
                @foreach($exhibitor->social_media_links as $platform => $url)
                    <a href="{{ $url }}" target="_blank" 
                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                        {{ ucfirst($platform) }}
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Job Openings -->
        @if($exhibitor->jobs && $exhibitor->jobs->where('is_active', true)->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Job Openings
            </h2>
            <div class="space-y-4">
                @foreach($exhibitor->jobs->where('is_active', true) as $job)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition">
                        <h3 class="font-semibold text-gray-900 mb-2">{{ $job->title }}</h3>
                        <div class="flex flex-wrap gap-2 mb-3">
                            @if($job->job_type)
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-lg">{{ $job->job_type }}</span>
                            @endif
                            @if($job->experience_level)
                                <span class="px-2 py-1 bg-purple-50 text-purple-700 text-xs rounded-lg">{{ $job->experience_level }}</span>
                            @endif
                            @if($job->location)
                                <span class="px-2 py-1 bg-gray-50 text-gray-700 text-xs rounded-lg flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                    {{ $job->location }}
                                </span>
                            @endif
                            @if($job->salary_range)
                                <span class="px-2 py-1 bg-green-50 text-green-700 text-xs rounded-lg">{{ $job->salary_range }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($job->description, 200) }}</p>
                        @if($job->requirements)
                            <div class="mb-3">
                                <p class="text-xs font-medium text-gray-700 mb-1">Requirements:</p>
                                <p class="text-xs text-gray-600">{{ Str::limit($job->requirements, 150) }}</p>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <div class="flex gap-4 text-xs text-gray-500">
                                @if($job->deadline)
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Apply by {{ $job->deadline->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                            @if($job->application_email)
                                <a href="mailto:{{ $job->application_email }}" 
                                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition">
                                    Apply Now
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Products & Services -->
        @if($exhibitor->products && $exhibitor->products->where('is_active', true)->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                Products & Services
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($exhibitor->products->where('is_active', true) as $product)
                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:border-indigo-300 transition">
                        @if($product->image)
                            <img src="{{ storage_public_url($product->image) }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        @endif
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="font-semibold text-gray-900">{{ $product->name }}</h3>
                                <div class="flex gap-1">
                                    @if($product->is_featured)
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">Featured</span>
                                    @endif
                                    @if($product->is_new)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">New</span>
                                    @endif
                                </div>
                            </div>
                            @if($product->category)
                                <p class="text-xs text-gray-500 mb-2">{{ $product->category }}</p>
                            @endif
                            @if($product->price_text)
                                <p class="text-indigo-600 font-semibold mb-2">{{ $product->price_text }}</p>
                            @elseif($product->price)
                                <p class="text-indigo-600 font-semibold mb-2">${{ number_format($product->price, 2) }}</p>
                            @endif
                            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($product->description, 120) }}</p>
                            @if($product->features)
                                <div class="mb-3">
                                    <p class="text-xs font-medium text-gray-700 mb-1">Key Features:</p>
                                    <p class="text-xs text-gray-600">{{ Str::limit($product->features, 100) }}</p>
                                </div>
                            @endif
                            @if($exhibitor->contact_email)
                                <a href="mailto:{{ $exhibitor->contact_email }}?subject=Inquiry about {{ $product->name }}" 
                                   class="block w-full text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition">
                                    Inquire Now
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
