<div class="space-y-6">
    <!-- Name -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Template Name <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               name="name" 
               id="name" 
               value="{{ old('name', $template->name) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
               required>
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Description -->
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Description
        </label>
        <textarea name="description" 
                  id="description" 
                  rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('description', $template->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Preview Image -->
    <div>
        <label for="preview_image" class="block text-sm font-medium text-gray-700 mb-2">
            Preview Image
        </label>
        @if($template->preview_image)
            <div class="mb-3">
                <img src="{{ asset('storage/' . $template->preview_image) }}" 
                     alt="Current preview" 
                     class="h-32 rounded border border-gray-300">
            </div>
        @endif
        <input type="file" 
               name="preview_image" 
               id="preview_image" 
               accept="image/*"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        <p class="mt-1 text-sm text-gray-500">Recommended: 1200x800px, JPG or PNG</p>
        @error('preview_image')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- HTML Content -->
    <div>
        <div class="flex justify-between items-center mb-2">
            <label for="html_content" class="block text-sm font-medium text-gray-700">
                HTML Content <span class="text-red-500">*</span>
            </label>
            <button type="button" 
                    id="generate-with-ai"
                    class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition">
                ✨ Generate with AI
            </button>
        </div>
        <textarea name="html_content" 
                  id="html_content" 
                  rows="15"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                  required>{{ old('html_content', $template->html_content) }}</textarea>
        <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
            <p class="font-semibold text-blue-900 mb-1">Available Variables:</p>
            <p class="text-blue-800">
                <code>$event</code>, <code>$speakers</code>, <code>$sponsors</code>, <code>$partners</code>, 
                <code>$sessions</code>, <code>$tracks</code>, <code>$registrationCategories</code>, <code>$exhibitors</code>
            </p>
            <p class="font-semibold text-blue-900 mt-2 mb-1">Example Usage:</p>
            <p class="text-blue-800 font-mono text-xs">
                @verbatim{{ $event->event_name }}@endverbatim<br>
                @verbatim@foreach($speakers as $speaker)@endverbatim<br>
                @verbatim&nbsp;&nbsp;{{ $speaker->full_name }}@endverbatim<br>
                @verbatim@endforeach@endverbatim
            </p>
        </div>
        @error('html_content')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- CSS Content -->
    <div>
        <label for="css_content" class="block text-sm font-medium text-gray-700 mb-2">
            CSS Content
        </label>
        <textarea name="css_content" 
                  id="css_content" 
                  rows="10"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm">{{ old('css_content', $template->css_content) }}</textarea>
        <p class="mt-1 text-sm text-gray-500">Custom CSS for this template</p>
        @error('css_content')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- JS Content -->
    <div>
        <label for="js_content" class="block text-sm font-medium text-gray-700 mb-2">
            JavaScript Content
        </label>
        <textarea name="js_content" 
                  id="js_content" 
                  rows="10"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm">{{ old('js_content', $template->js_content) }}</textarea>
        <p class="mt-1 text-sm text-gray-500">Custom JavaScript for this template</p>
        @error('js_content')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Active Status -->
    <div class="flex items-center">
        <input type="checkbox" 
               name="is_active" 
               id="is_active" 
               value="1"
               {{ old('is_active', $template->is_active) ? 'checked' : '' }}
               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
        <label for="is_active" class="ml-2 block text-sm text-gray-700">
            Active (available for selection)
        </label>
    </div>
</div>

<!-- AI Generation Modal -->
<div id="ai-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Generate Template with AI</h3>
            <button type="button" 
                    id="close-ai-modal"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                ×
            </button>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Prompt Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Describe your template</label>
                <textarea id="ai-prompt" 
                          rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          placeholder="Example: Create a modern landing page for a tech conference with a gradient hero section, speaker cards, and registration form"></textarea>
            </div>
            
            <!-- Sample Prompts -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Or choose from sample prompts:</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto pr-2">
                    <!-- Medical & Healthcare -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create a professional medical conference landing page with a clean white and blue color scheme. Include sections for keynote speakers, medical sessions, CME credits information, and registration. Use medical imagery and professional typography."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🏥 Medical Conference</div>
                        <div class="text-sm text-gray-600 mt-1">Professional healthcare event with CME credits</div>
                    </button>
                    
                    <!-- Tech Conference -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design a cutting-edge tech conference landing page with a dark theme and neon accents. Feature sections for tech speakers, workshop schedules, sponsor logos, and early bird registration. Include animated elements and modern gradients."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">💻 Tech Conference</div>
                        <div class="text-sm text-gray-600 mt-1">Modern tech event with dark theme</div>
                    </button>
                    
                    <!-- Food & Beverage -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create a vibrant food and beverage expo landing page with warm colors and appetizing imagery. Include sections for exhibitors, cooking demonstrations, tasting sessions, and vendor registration. Use food photography and elegant typography."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🍽️ Food & Beverage Expo</div>
                        <div class="text-sm text-gray-600 mt-1">Culinary event with vibrant design</div>
                    </button>
                    
                    <!-- Job Fair -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design a professional job fair landing page with corporate colors. Include sections for participating companies, job categories, career workshops, resume tips, and attendee registration. Use professional imagery and clear call-to-actions."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">💼 Job Fair</div>
                        <div class="text-sm text-gray-600 mt-1">Career event with professional layout</div>
                    </button>
                    
                    <!-- Trade Show -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create a dynamic trade show landing page with bold colors and large imagery. Feature exhibitor booths, product showcases, networking events, and floor plan. Include countdown timer and prominent registration button."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🏢 Trade Show</div>
                        <div class="text-sm text-gray-600 mt-1">Business exhibition with dynamic design</div>
                    </button>
                    
                    <!-- Art Exhibition -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design an elegant art exhibition landing page with minimalist design and gallery-style layout. Include artist profiles, artwork galleries, exhibition schedule, and ticket booking. Use sophisticated typography and ample white space."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🎨 Art Exhibition</div>
                        <div class="text-sm text-gray-600 mt-1">Gallery event with minimalist elegance</div>
                    </button>
                    
                    <!-- Music Festival -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create an energetic music festival landing page with vibrant gradients and bold typography. Feature artist lineup, stage schedules, ticket tiers, and venue map. Include video backgrounds and animated elements."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🎵 Music Festival</div>
                        <div class="text-sm text-gray-600 mt-1">Concert event with energetic vibe</div>
                    </button>
                    
                    <!-- Education Summit -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design an educational summit landing page with academic colors and professional layout. Include keynote educators, workshop sessions, certification programs, and student registration. Use educational imagery and clear information hierarchy."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">📚 Education Summit</div>
                        <div class="text-sm text-gray-600 mt-1">Academic conference with professional design</div>
                    </button>
                    
                    <!-- Startup Pitch -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create a modern startup pitch event landing page with innovative design and startup culture vibes. Feature investor panels, pitch schedules, networking sessions, and startup registration. Use bold colors and contemporary design elements."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🚀 Startup Pitch Event</div>
                        <div class="text-sm text-gray-600 mt-1">Innovation event with modern design</div>
                    </button>
                    
                    <!-- Fashion Show -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design a glamorous fashion show landing page with high-fashion aesthetics. Include designer profiles, runway schedule, collection previews, and VIP ticket options. Use elegant typography, black and gold accents, and fashion photography."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">👗 Fashion Show</div>
                        <div class="text-sm text-gray-600 mt-1">Runway event with glamorous design</div>
                    </button>
                    
                    <!-- Sports Event -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create an energetic sports event landing page with dynamic design and action imagery. Feature tournament brackets, team profiles, match schedules, and ticket sales. Use bold colors and sports-themed graphics."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">⚽ Sports Tournament</div>
                        <div class="text-sm text-gray-600 mt-1">Athletic event with dynamic energy</div>
                    </button>
                    
                    <!-- Charity Gala -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design an elegant charity gala landing page with sophisticated design and emotional appeal. Include cause information, donation tiers, event schedule, and sponsor recognition. Use warm colors and inspiring imagery."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">❤️ Charity Gala</div>
                        <div class="text-sm text-gray-600 mt-1">Fundraising event with elegant appeal</div>
                    </button>
                    
                    <!-- Gaming Convention -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create an exciting gaming convention landing page with gaming aesthetics and neon colors. Feature game demos, esports tournaments, cosplay contests, and gamer registration. Use gaming-inspired design elements and bold typography."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🎮 Gaming Convention</div>
                        <div class="text-sm text-gray-600 mt-1">Gamer event with exciting design</div>
                    </button>
                    
                    <!-- Real Estate Expo -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design a professional real estate expo landing page with luxury aesthetics. Include property showcases, developer profiles, investment seminars, and buyer registration. Use premium imagery and sophisticated color palette."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🏠 Real Estate Expo</div>
                        <div class="text-sm text-gray-600 mt-1">Property event with luxury design</div>
                    </button>
                    
                    <!-- Wellness Retreat -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create a calming wellness retreat landing page with natural colors and serene imagery. Feature wellness programs, meditation sessions, spa treatments, and retreat booking. Use soft colors, nature photography, and peaceful design elements."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🧘 Wellness Retreat</div>
                        <div class="text-sm text-gray-600 mt-1">Health event with calming design</div>
                    </button>
                    
                    <!-- Auto Show -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design a sleek auto show landing page with automotive aesthetics and metallic accents. Feature vehicle showcases, manufacturer booths, test drive bookings, and visitor registration. Use high-quality car photography and modern design."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🚗 Auto Show</div>
                        <div class="text-sm text-gray-600 mt-1">Automotive event with sleek design</div>
                    </button>
                    
                    <!-- Book Fair -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create a literary book fair landing page with classic design and bookish aesthetics. Include author signings, book launches, reading sessions, and visitor registration. Use book-themed imagery and elegant typography."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">📖 Book Fair</div>
                        <div class="text-sm text-gray-600 mt-1">Literary event with classic elegance</div>
                    </button>
                    
                    <!-- Film Festival -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design a cinematic film festival landing page with movie theater aesthetics. Feature film screenings, director panels, award ceremonies, and ticket booking. Use dark theme with gold accents and cinematic imagery."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🎬 Film Festival</div>
                        <div class="text-sm text-gray-600 mt-1">Cinema event with theatrical design</div>
                    </button>
                    
                    <!-- Green Energy Summit -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Create an eco-friendly green energy summit landing page with sustainable design and earth tones. Feature renewable energy sessions, sustainability workshops, green exhibitors, and registration. Use nature imagery and eco-conscious design elements."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">🌱 Green Energy Summit</div>
                        <div class="text-sm text-gray-600 mt-1">Sustainability event with eco design</div>
                    </button>
                    
                    <!-- Wedding Expo -->
                    <button type="button" onclick="setAIPrompt(this.getAttribute('data-prompt'))" 
                            data-prompt="Design a romantic wedding expo landing page with elegant and dreamy aesthetics. Include vendor showcases, bridal fashion shows, planning workshops, and couple registration. Use soft pastels, floral elements, and romantic typography."
                            class="text-left p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition group">
                        <div class="font-semibold text-gray-900 group-hover:text-purple-700">💍 Wedding Expo</div>
                        <div class="text-sm text-gray-600 mt-1">Bridal event with romantic design</div>
                    </button>
                </div>
            </div>
            
            <!-- Style and Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Style</label>
                    <select id="ai-style" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="modern">Modern</option>
                        <option value="classic">Classic</option>
                        <option value="vibrant">Vibrant</option>
                        <option value="minimal">Minimal</option>
                        <option value="elegant">Elegant</option>
                        <option value="bold">Bold</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Include Sections</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center text-sm">
                            <input type="checkbox" value="hero" checked class="ai-section mr-2 rounded"> Hero
                        </label>
                        <label class="flex items-center text-sm">
                            <input type="checkbox" value="speakers" checked class="ai-section mr-2 rounded"> Speakers
                        </label>
                        <label class="flex items-center text-sm">
                            <input type="checkbox" value="agenda" class="ai-section mr-2 rounded"> Agenda
                        </label>
                        <label class="flex items-center text-sm">
                            <input type="checkbox" value="sponsors" class="ai-section mr-2 rounded"> Sponsors
                        </label>
                        <label class="flex items-center text-sm">
                            <input type="checkbox" value="registration" checked class="ai-section mr-2 rounded"> Registration
                        </label>
                        <label class="flex items-center text-sm">
                            <input type="checkbox" value="exhibitors" class="ai-section mr-2 rounded"> Exhibitors
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="sticky bottom-0 bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
            <button type="button" 
                    id="close-ai-modal-footer"
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                Cancel
            </button>
            <button type="button" 
                    id="generate-ai-content"
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                Generate Template
            </button>
        </div>
        
        <div id="ai-loading" class="hidden absolute inset-0 bg-white bg-opacity-95 flex items-center justify-center">
            <div class="text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
                <p class="mt-4 text-gray-700 font-medium">Generating your template...</p>
                <p class="mt-2 text-sm text-gray-500">This may take 30-60 seconds</p>
            </div>
        </div>
    </div>
</div>

<script>
function setAIPrompt(prompt) {
    document.getElementById('ai-prompt').value = prompt;
}

// AI Generation
document.getElementById('generate-with-ai')?.addEventListener('click', function() {
    document.getElementById('ai-modal').classList.remove('hidden');
});

document.getElementById('close-ai-modal')?.addEventListener('click', function() {
    document.getElementById('ai-modal').classList.add('hidden');
});

document.getElementById('close-ai-modal-footer')?.addEventListener('click', function() {
    document.getElementById('ai-modal').classList.add('hidden');
});
                Cancel
            </button>
            <button type="button" 
                    id="generate-ai-content"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Generate
            </button>
        </div>
        
        <div id="ai-loading" class="hidden mt-4 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
            <p class="mt-2 text-gray-600">Generating template...</p>
        </div>
    </div>
</div>

<script>
// AI Generation
document.getElementById('generate-with-ai')?.addEventListener('click', function() {
    document.getElementById('ai-modal').classList.remove('hidden');
});

document.getElementById('close-ai-modal')?.addEventListener('click', function() {
    document.getElementById('ai-modal').classList.add('hidden');
});

document.getElementById('generate-ai-content')?.addEventListener('click', function() {
    const prompt = document.getElementById('ai-prompt').value;
    const style = document.getElementById('ai-style').value;
    const sections = Array.from(document.querySelectorAll('.ai-section:checked')).map(cb => cb.value);
    
    if (!prompt) {
        alert('Please describe your template');
        return;
    }
    
    document.getElementById('ai-loading').classList.remove('hidden');
    this.disabled = true;
    
    fetch('/admin/llm/generate-template', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ prompt, style, sections })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('html_content').value = data.html;
            document.getElementById('ai-modal').classList.add('hidden');
            alert('Template generated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        document.getElementById('ai-loading').classList.add('hidden');
        this.disabled = false;
    });
});
</script>
