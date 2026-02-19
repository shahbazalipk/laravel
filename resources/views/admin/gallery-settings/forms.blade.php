@extends('admin.layout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('admin.gallery-settings.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm mb-2 inline-block">
                ← Back to Gallery Settings
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Forms</h1>
            <p class="text-gray-600 mt-1">Manage forms for the online gallery</p>
        </div>

        <!-- Existing Forms -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Manage forms for the online gallery</h2>
            
            @if($forms->isEmpty())
                <p class="text-gray-500 text-center py-8">No forms created yet</p>
            @else
                <div class="space-y-3 mb-6">
                    @foreach($forms as $form)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900">{{ $form->name }}</h3>
                                @if($form->description)
                                    <p class="text-sm text-gray-500">{{ $form->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                @if($form->is_default)
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">Default</span>
                                @else
                                    <form action="{{ route('admin.gallery-forms.set-default', $form) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">Set as Default</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.gallery-forms.edit', $form) }}" class="text-gray-600 hover:text-gray-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <a href="{{ route('admin.gallery-forms.create') }}" 
               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-900 hover:bg-gray-800 text-white rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create form
            </a>
        </div>

        <!-- Default Behavior Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('admin.gallery-settings.forms.update') }}" method="POST">
                @csrf
                
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Set the default behaviour, that will be applied to newly created albums</h2>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-900 mb-3">When to display the form:</label>
                    
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="default_form_trigger" 
                                   value="on_open"
                                   {{ old('default_form_trigger', $settings->default_form_trigger) == 'on_open' ? 'checked' : '' }}
                                   class="w-5 h-5 text-indigo-600 focus:ring-indigo-500"
                                   onchange="toggleDelayInput(this)">
                            <span class="ml-3 text-gray-700">When the user opens the online gallery</span>
                        </label>

                        <label class="flex items-center">
                            <input type="radio" 
                                   name="default_form_trigger" 
                                   value="delayed"
                                   {{ old('default_form_trigger', $settings->default_form_trigger) == 'delayed' ? 'checked' : '' }}
                                   class="w-5 h-5 text-indigo-600 focus:ring-indigo-500"
                                   onchange="toggleDelayInput(this)">
                            <span class="ml-3 text-gray-700 flex items-center gap-2">
                                <input type="number" 
                                       name="default_form_delay_seconds" 
                                       id="delay_seconds"
                                       value="{{ old('default_form_delay_seconds', $settings->default_form_delay_seconds) }}"
                                       min="0"
                                       class="w-16 px-2 py-1 border border-gray-300 rounded text-center"
                                       {{ old('default_form_trigger', $settings->default_form_trigger) != 'delayed' ? 'disabled' : '' }}>
                                seconds after the user opens the online gallery
                            </span>
                        </label>

                        <label class="flex items-center">
                            <input type="radio" 
                                   name="default_form_trigger" 
                                   value="on_download"
                                   {{ old('default_form_trigger', $settings->default_form_trigger) == 'on_download' ? 'checked' : '' }}
                                   class="w-5 h-5 text-indigo-600 focus:ring-indigo-500"
                                   onchange="toggleDelayInput(this)">
                            <span class="ml-3 text-gray-700">When the user selects the download button to download photos</span>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-900 mb-3">Make it mandatory or optional:</label>
                    
                    <div class="space-y-3">
                        <label class="flex items-start">
                            <input type="radio" 
                                   name="default_form_requirement" 
                                   value="mandatory"
                                   {{ old('default_form_requirement', $settings->default_form_requirement) == 'mandatory' ? 'checked' : '' }}
                                   class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                            <div class="ml-3">
                                <div class="text-gray-900 font-medium">Mandatory</div>
                                <div class="text-sm text-gray-500">User must submit form to proceed</div>
                            </div>
                        </label>

                        <label class="flex items-start">
                            <input type="radio" 
                                   name="default_form_requirement" 
                                   value="optional"
                                   {{ old('default_form_requirement', $settings->default_form_requirement) == 'optional' ? 'checked' : '' }}
                                   class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                            <div class="ml-3">
                                <div class="text-gray-900 font-medium">Optional</div>
                                <div class="text-sm text-gray-500">User can dismiss form without submitting</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDelayInput(radio) {
    const delayInput = document.getElementById('delay_seconds');
    if (radio.value === 'delayed' && radio.checked) {
        delayInput.disabled = false;
    } else if (radio.checked) {
        delayInput.disabled = true;
    }
}
</script>
@endsection
