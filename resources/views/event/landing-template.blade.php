<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->event_name ?? 'Event' }}</title>
    
    @if($event->seo_description)
        <meta name="description" content="{{ $event->seo_description }}">
    @endif
    
    @if($event->seo_keywords)
        <meta name="keywords" content="{{ $event->seo_keywords }}">
    @endif
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Template CSS -->
    <style>
        {!! $event->landingPageTemplate->css_content !!}
        
        /* Custom Event Branding Overrides */
        @if($event->template_settings && isset($event->template_settings['custom_css']))
            {!! $event->template_settings['custom_css'] !!}
        @endif
    </style>
</head>
<body>
    <!-- Render Template HTML -->
    @php
        // Replace placeholders in template HTML
        $html = $event->landingPageTemplate->html_content;
        
        // Process Blade syntax in template
        $html = \Blade::render($html, [
            'event' => $event,
            'speakers' => $speakers,
            'sponsors' => $sponsors,
            'partners' => $partners,
            'tracks' => $tracks,
            'agendaItems' => $agendaItems,
            'registrationCategories' => $registrationCategories,
            'exhibitors' => $exhibitors,
            'sessions' => $sessions,
        ]);
    @endphp
    
    {!! $html !!}
    
    <!-- Template JavaScript -->
    @if($event->landingPageTemplate->js_content)
        <script>
            {!! $event->landingPageTemplate->js_content !!}
        </script>
    @endif
    
    <!-- Custom Event JavaScript -->
    @if($event->template_settings && isset($event->template_settings['custom_js']))
        <script>
            {!! $event->template_settings['custom_js'] !!}
        </script>
    @endif
</body>
</html>
