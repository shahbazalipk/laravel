<div class="space-y-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Landing Page Template</h3>
        <p class="text-gray-600 mb-6">Choose a template design for your event landing page</p>
    </div>

    @if($templates->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($templates as $template)
                <label class="cursor-pointer">
                    <input type="radio" 
                           name="landing_page_template_id" 
                           value="{{ $template->id }}"
                           {{ old('landing_page_template_id', $event->landing_page_template_id) == $template->id ? 'checked' : '' }}
                           class="sr-only peer">
                    
                    <div class="border-2 border-gray-200 rounded-lg overflow-hidden transition peer-checked:border-indigo-600 peer-checked:ring-2 peer-checked:ring-indigo-500 hover:border-gray-300">
                        <!-- Preview Image -->
                        <div class="h-40 bg-gray-100 relative">
                            @if($template->preview_image)
                                <img src="{{ asset('storage/' . $template->preview_image) }}" 
                                     alt="{{ $template->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Selected Badge -->
                            <div class="absolute top-2 right-2 hidden peer-checked:block">
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-600 text-white">
                                    Selected
                                </span>
                            </div>
                        </div>
                        
                        <!-- Template Info -->
                        <div class="p-4">
                            <h4 class="font-semibold text-gray-900 mb-1">{{ $template->name }}</h4>
                            <p class="text-sm text-gray-600 line-clamp-2">
                                {{ $template->description ?? 'No description' }}
                            </p>
                            <a href="{{ route('admin.landing-page-templates.preview', $template) }}" 
                               target="_blank"
                               class="text-sm text-indigo-600 hover:text-indigo-800 mt-2 inline-block"
                               onclick="event.stopPropagation()">
                                Preview Template →
                            </a>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
        
        <!-- No Template Option -->
        <div class="mt-4">
            <label class="cursor-pointer flex items-center">
                <input type="radio" 
                       name="landing_page_template_id" 
                       value=""
                       {{ old('landing_page_template_id', $event->landing_page_template_id) == '' ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <span class="ml-2 text-sm text-gray-700">Use default landing page (no template)</span>
            </label>
        </div>
    @else
        <div class="text-center py-8 bg-gray-50 rounded-lg">
            <p class="text-gray-600 mb-4">No templates available yet.</p>
            <a href="{{ route('admin.landing-page-templates.index') }}" 
               class="text-indigo-600 hover:text-indigo-800 font-medium">
                Create your first template →
            </a>
        </div>
    @endif

    @error('landing_page_template_id')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <!-- Template Customization Note -->
    @if($event->landing_page_template_id)
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h4 class="font-semibold text-blue-900 mb-2">Template Customization</h4>
            <p class="text-sm text-blue-800">
                The selected template will use your event's branding (logo, colors, content) automatically. 
                You can further customize the template CSS in the 
                <a href="{{ route('admin.landing-page-templates.index') }}" class="underline font-medium">Templates Manager</a>.
            </p>
        </div>
    @endif
</div>
