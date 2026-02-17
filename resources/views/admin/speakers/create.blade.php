@extends('admin.layout')

@section('title', 'Create Speaker')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.speakers.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create Speaker</h1>
            <p class="text-gray-600 mt-1">Add a new speaker or presenter</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.speakers.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('full_name') border-red-500 @enderror"
                       placeholder="e.g., John Doe" required>
                @error('full_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="job_title" class="block text-sm font-medium text-gray-700 mb-2">Job Title</label>
                    <input type="text" name="job_title" id="job_title" value="{{ old('job_title') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('job_title') border-red-500 @enderror"
                           placeholder="e.g., CEO">
                    @error('job_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                    <input type="text" name="company" id="company" value="{{ old('company') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('company') border-red-500 @enderror"
                           placeholder="e.g., Tech Corp">
                    @error('company')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Biography</label>
                <textarea name="bio" id="bio" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('bio') border-red-500 @enderror"
                          placeholder="Speaker's biography and background">{{ old('bio') }}</textarea>
                @error('bio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-500 @enderror"
                           placeholder="speaker@example.com">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                           placeholder="+1 234 567 8900">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-2">Profile Image URL</label>
                <input type="text" name="profile_image" id="profile_image" value="{{ old('profile_image') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('profile_image') border-red-500 @enderror"
                       placeholder="https://example.com/image.jpg">
                @error('profile_image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-400">URL to speaker's profile photo</p>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.speakers.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Create Speaker</button>
        </div>
    </form>
</div>
@endsection
