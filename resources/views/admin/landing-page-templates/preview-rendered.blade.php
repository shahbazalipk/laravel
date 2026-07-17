<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
        $event = \App\Models\Event::getCurrentEvent();
    @endphp
    
    <!-- Primary Meta Tags -->
    <title>{{ $event->seo_title ?? $event->event_name ?? $template->name }}</title>
    <meta name="title" content="{{ $event->seo_title ?? $event->event_name ?? $template->name }}">
    <meta name="description" content="{{ $event->seo_description ?? $event->description ?? 'Join us for an amazing event experience.' }}">
    @if($event->seo_keywords)
        <meta name="keywords" content="{{ $event->seo_keywords }}">
    @endif
    <meta name="author" content="{{ $event->organizer_name ?? config('app.name') }}">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://{{ $event->subdomain ?? 'event' }}.glimzo.ai">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://{{ $event->subdomain ?? 'event' }}.glimzo.ai">
    <meta property="og:title" content="{{ $event->seo_title ?? $event->event_name ?? $template->name }}">
    <meta property="og:description" content="{{ $event->seo_description ?? $event->description ?? 'Join us for an amazing event experience.' }}">
    @if($event->social_media_share_banner)
        <meta property="og:image" content="{{ storage_public_url($event->social_media_share_banner) }}">
    @elseif($event->logo)
        <meta property="og:image" content="{{ storage_public_url($event->logo) }}">
    @endif
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="{{ $event->event_name ?? config('app.name') }}">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://{{ $event->subdomain ?? 'event' }}.glimzo.ai">
    <meta property="twitter:title" content="{{ $event->seo_title ?? $event->event_name ?? $template->name }}">
    <meta property="twitter:description" content="{{ $event->seo_description ?? $event->description ?? 'Join us for an amazing event experience.' }}">
    @if($event->social_media_share_banner)
        <meta property="twitter:image" content="{{ storage_public_url($event->social_media_share_banner) }}">
    @elseif($event->logo)
        <meta property="twitter:image" content="{{ storage_public_url($event->logo) }}">
    @endif
    @if($event->twitter_mention)
        <meta name="twitter:site" content="{{ $event->twitter_mention }}">
        <meta name="twitter:creator" content="{{ $event->twitter_mention }}">
    @endif
    
    <!-- LinkedIn -->
    <meta property="og:image:alt" content="{{ $event->event_name ?? $template->name }} - Event Banner">
    
    <!-- Additional Meta Tags -->
    <meta name="theme-color" content="#6366f1">
    <meta name="msapplication-TileColor" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Structured Data / Schema.org -->
    @if($event->start_date)
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Event",
        "name": "{{ $event->event_name ?? $template->name }}",
        "description": "{{ $event->seo_description ?? $event->description ?? 'Join us for an amazing event experience.' }}",
        "startDate": "{{ $event->start_date->toIso8601String() }}",
        @if($event->end_date)
        "endDate": "{{ $event->end_date->toIso8601String() }}",
        @endif
        "eventStatus": "https://schema.org/EventScheduled",
        "eventAttendanceMode": "{{ $event->event_type === 'virtual' ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode' }}",
        @if($event->venue_name || $event->address)
        "location": {
            "@type": "Place",
            "name": "{{ $event->venue_name ?? 'Event Venue' }}",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "{{ $event->address ?? '' }}",
                "addressLocality": "{{ $event->city ?? '' }}",
                "addressRegion": "{{ $event->state ?? '' }}",
                "postalCode": "{{ $event->zip_code ?? '' }}",
                "addressCountry": "{{ $event->country ?? '' }}"
            }
        },
        @endif
        "image": [
            @if($event->social_media_share_banner)
                "{{ storage_public_url($event->social_media_share_banner) }}"
            @elseif($event->logo)
                "{{ storage_public_url($event->logo) }}"
            @endif
        ],
        @if($event->organizer_name)
        "organizer": {
            "@type": "Organization",
            "name": "{{ $event->organizer_name }}",
            "url": "https://{{ $event->subdomain ?? 'event' }}.glimzo.ai"
        },
        @endif
        "offers": {
            "@type": "Offer",
            "url": "https://{{ $event->subdomain ?? 'event' }}.glimzo.ai",
            "availability": "https://schema.org/InStock",
            "validFrom": "{{ now()->toIso8601String() }}"
        }
    }
    </script>
    @endif
    
    <!-- Favicon -->
    @if($event->logo)
        <link rel="icon" type="image/png" href="{{ storage_public_url($event->logo) }}">
        <link rel="apple-touch-icon" href="{{ storage_public_url($event->logo) }}">
    @endif
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Template CSS -->
    <style>
        {!! $template->css_content !!}
    </style>
    
    <!-- Preview Banner & AI Assistant Styles -->
    <style>
        .preview-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1f2937;
            color: white;
            padding: 10px 20px;
            text-align: center;
            z-index: 9999;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .preview-banner a {
            color: #60a5fa;
            text-decoration: none;
            margin-left: 15px;
        }
        .preview-banner a:hover {
            text-decoration: underline;
        }
        body {
            padding-top: 50px;
        }
        
        /* AI Assistant Button */
        .ai-assistant-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s ease;
        }
        .ai-assistant-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 30px rgba(102, 126, 234, 0.6);
        }
        .ai-assistant-btn.processing {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* AI Modal */
        .ai-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .ai-modal.active {
            display: flex;
        }
        .ai-modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .ai-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .ai-modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ai-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .ai-modal-close:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        .ai-modal-body {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
        }
        .ai-prompt-input {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.2s;
        }
        .ai-prompt-input:focus {
            outline: none;
            border-color: #667eea;
        }
        .ai-suggestions {
            margin-top: 16px;
        }
        .ai-suggestions-title {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .ai-suggestion-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .ai-suggestion-chip {
            padding: 6px 12px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            font-size: 13px;
            color: #4b5563;
            cursor: pointer;
            transition: all 0.2s;
        }
        .ai-suggestion-chip:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .ai-modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        .ai-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .ai-btn-secondary {
            background: #f3f4f6;
            color: #4b5563;
        }
        .ai-btn-secondary:hover {
            background: #e5e7eb;
        }
        .ai-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .ai-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .ai-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .ai-status {
            margin-top: 16px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            display: none;
        }
        .ai-status.active {
            display: block;
        }
        .ai-status.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .ai-status.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .ai-status.processing {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .ai-config-warning {
            padding: 12px;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            color: #92400e;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .ai-config-warning a {
            color: #92400e;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Preview Banner -->
    <div class="preview-banner">
        <strong>Preview Mode:</strong> {{ $template->name }}
        <a href="{{ route('admin.landing-page-templates.edit', $template) }}">Edit Template</a>
        <a href="{{ route('admin.landing-page-templates.index') }}">Back to Templates</a>
    </div>
    
    <!-- AI Assistant Button -->
    @php
        $event = \App\Models\Event::getCurrentEvent();
        $aiEnabled = $event && $event->llm_enabled && $event->llm_provider && $event->llm_model;
    @endphp
    
    @if($aiEnabled)
    <button class="ai-assistant-btn" onclick="openAIModal()" title="AI Assistant">
        ✨
    </button>
    @endif
    
    <!-- Rendered Template Content -->
    {!! $renderedHtml !!}
    
    <!-- AI Modal -->
    @if($aiEnabled)
    <div class="ai-modal" id="aiModal">
        <div class="ai-modal-content">
            <div class="ai-modal-header">
                <h3>
                    <span>✨</span>
                    AI Page Editor
                </h3>
                <button class="ai-modal-close" onclick="closeAIModal()">×</button>
            </div>
            <div class="ai-modal-body">
                <div class="ai-config-info" style="margin-bottom: 16px; font-size: 13px; color: #6b7280;">
                    Using: <strong>{{ ucfirst($event->llm_provider) }}</strong> - <strong>{{ $event->llm_model }}</strong>
                </div>
                
                <label for="aiPrompt" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                    What would you like to change?
                </label>
                <textarea 
                    id="aiPrompt" 
                    class="ai-prompt-input" 
                    placeholder="Example: Make the hero section more vibrant with a gradient background, change the heading to be more engaging, add a call-to-action button..."
                ></textarea>
                
                <div class="ai-suggestions">
                    <div class="ai-suggestions-title">Quick Edit Suggestions</div>
                    <div class="ai-suggestion-chips">
                        <span class="ai-suggestion-chip" onclick="setPrompt('Make the design more modern and vibrant with gradient backgrounds')">
                            More Modern
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Improve the color scheme to be more professional and corporate')">
                            Professional Colors
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Add more spacing, improve typography, and enhance readability')">
                            Better Spacing
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Make it more mobile-friendly with responsive design improvements')">
                            Mobile Friendly
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Add smooth animations and transitions for better user experience')">
                            Add Animations
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Improve the call-to-action buttons to be more prominent and engaging')">
                            Better CTAs
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Make the hero section more impactful with larger text and better imagery')">
                            Enhance Hero
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Add a dark mode theme with elegant dark colors')">
                            Dark Theme
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Make it more minimalist with cleaner design and less clutter')">
                            Minimalist
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Add luxury and premium feel with gold accents and elegant fonts')">
                            Luxury Style
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Make it more playful and fun with bright colors and rounded elements')">
                            Playful Design
                        </span>
                        <span class="ai-suggestion-chip" onclick="setPrompt('Improve accessibility with better contrast and larger text')">
                            Better Accessibility
                        </span>
                    </div>
                </div>
                
                <div id="aiStatus" class="ai-status"></div>
            </div>
            <div class="ai-modal-footer">
                <button class="ai-btn ai-btn-secondary" onclick="closeAIModal()">
                    Cancel
                </button>
                <button id="aiGenerateBtn" class="ai-btn ai-btn-primary" onclick="generateAIEdit()">
                    Generate Changes
                </button>
            </div>
        </div>
    </div>
    @else
    <!-- AI Not Configured Warning -->
    <div class="ai-modal" id="aiModal">
        <div class="ai-modal-content">
            <div class="ai-modal-header">
                <h3>
                    <span>⚠️</span>
                    AI Not Configured
                </h3>
                <button class="ai-modal-close" onclick="closeAIModal()">×</button>
            </div>
            <div class="ai-modal-body">
                <div class="ai-config-warning">
                    AI features are not enabled for this event. Please configure your AI settings first.
                    <br><br>
                    <a href="{{ route('admin.event-settings.edit') }}#llm" target="_blank">
                        Go to Event Settings → AI Integration
                    </a>
                </div>
            </div>
            <div class="ai-modal-footer">
                <button class="ai-btn ai-btn-secondary" onclick="closeAIModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Template JavaScript -->
    @if($template->js_content)
        <script>
            {!! $template->js_content !!}
        </script>
    @endif
    
    <!-- AI Assistant JavaScript -->
    <script>
        const templateSlug = '{{ $template->slug }}';
        const aiEnabled = {{ $aiEnabled ? 'true' : 'false' }};
        
        function openAIModal() {
            document.getElementById('aiModal').classList.add('active');
            document.getElementById('aiPrompt').focus();
        }
        
        function closeAIModal() {
            document.getElementById('aiModal').classList.remove('active');
            document.getElementById('aiPrompt').value = '';
            hideStatus();
        }
        
        function setPrompt(text) {
            document.getElementById('aiPrompt').value = text;
        }
        
        function showStatus(message, type) {
            const status = document.getElementById('aiStatus');
            status.textContent = message;
            status.className = 'ai-status active ' + type;
        }
        
        function hideStatus() {
            const status = document.getElementById('aiStatus');
            status.className = 'ai-status';
        }
        
        async function generateAIEdit() {
            if (!aiEnabled) {
                showStatus('AI features are not enabled. Please configure AI settings first.', 'error');
                return;
            }
            
            const prompt = document.getElementById('aiPrompt').value.trim();
            
            if (!prompt) {
                showStatus('Please enter what you would like to change.', 'error');
                return;
            }
            
            const btn = document.getElementById('aiGenerateBtn');
            const assistantBtn = document.querySelector('.ai-assistant-btn');
            
            btn.disabled = true;
            btn.textContent = 'Generating...';
            assistantBtn.classList.add('processing');
            showStatus('AI is analyzing and improving your page...', 'processing');
            
            try {
                const response = await fetch(`/admin/landing-page-templates/${templateSlug}/ai-edit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ prompt })
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to generate changes');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    showStatus('✓ Changes generated successfully! Reloading preview...', 'success');
                    
                    // Reload the page after a short delay to show the success message
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Failed to generate changes');
                }
            } catch (error) {
                console.error('AI Edit Error:', error);
                showStatus('✗ Error: ' + error.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Generate Changes';
                assistantBtn.classList.remove('processing');
            }
        }
        
        // Keyboard shortcut: Ctrl/Cmd + K to open AI modal
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (aiEnabled) {
                    openAIModal();
                }
            }
            
            // ESC to close modal
            if (e.key === 'Escape') {
                closeAIModal();
            }
        });
        
        // Close modal when clicking outside
        document.getElementById('aiModal').addEventListener('click', (e) => {
            if (e.target.id === 'aiModal') {
                closeAIModal();
            }
        });
    </script>
</body>
</html>
