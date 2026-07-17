{{-- SEO / Open Graph / Twitter / JSON-LD for online registration pages --}}
@php
    $eventName = $event->event_name ?: $event->title ?: 'Event';
    $shareTitle = $event->seo_title ?: $eventName;
    $shareDescription = $event->social_media_description
        ?: $event->seo_description
        ?: ('Register for '.$eventName.($event->location ? ' in '.$event->location : '').'.');
    $shareDescription = \Illuminate\Support\Str::limit(trim(strip_tags($shareDescription)), 200, '');

    $absoluteMedia = function (?string $path): ?string {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relative = str_starts_with(ltrim($path, '/'), 'storage/')
            ? ltrim($path, '/')
            : 'storage/'.ltrim($path, '/');

        return url($relative);
    };

    $shareImageSource = $event->social_media_share_banner
        ?: $event->social_image
        ?: $event->header_image
        ?: $event->logo;
    $shareImage = $absoluteMedia($shareImageSource);
    $shareImageType = match (strtolower(pathinfo((string) $shareImageSource, PATHINFO_EXTENSION))) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        default => 'image/jpeg',
    };

    $canonicalUrl = route('online.registration.step.email', ['slug' => $slug], absolute: true);
    $currentUrl = url()->current();
    $isPersonalizedStep = filled($reg ?? null);
    $robots = $isPersonalizedStep ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

    $twitterHandle = $event->twitter_mention
        ? (str_starts_with(ltrim($event->twitter_mention), '@') ? $event->twitter_mention : '@'.ltrim($event->twitter_mention, '@'))
        : null;

    $locale = str_replace('-', '_', app()->getLocale() ?: 'en_US');
    if (! str_contains($locale, '_')) {
        $locale = $locale.'_'.strtoupper($locale);
    }

    $attendanceMode = match (strtolower((string) ($event->format ?? $event->event_mode ?? ''))) {
        'virtual', 'online' => 'https://schema.org/OnlineEventAttendanceMode',
        'hybrid' => 'https://schema.org/MixedEventAttendanceMode',
        default => 'https://schema.org/OfflineEventAttendanceMode',
    };

    $locationName = $event->location ?: ($event->city ?: $eventName);
    $favicon = $absoluteMedia($event->logo);
@endphp

{{-- Primary --}}
<title>@yield('title', 'Register') - {{ $shareTitle }}</title>
<meta name="title" content="{{ $shareTitle }}">
<meta name="description" content="{{ $shareDescription }}">
@if($event->seo_keywords)
    <meta name="keywords" content="{{ $event->seo_keywords }}">
@endif
<meta name="author" content="{{ $event->manager_name ?: $eventName }}">
<meta name="robots" content="{{ $robots }}">
<meta name="googlebot" content="{{ $robots }}">
<meta name="language" content="{{ str_replace('_', '-', $locale) }}">
<meta name="revisit-after" content="3 days">
<meta name="rating" content="general">
<meta name="referrer" content="strict-origin-when-cross-origin">

{{-- Canonical always points at the public registration entry, not draft URLs --}}
<link rel="canonical" href="{{ $canonicalUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">

{{-- Open Graph / Facebook / LinkedIn / WhatsApp --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $eventName }}">
<meta property="og:locale" content="{{ $locale }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:title" content="{{ $shareTitle }}">
<meta property="og:description" content="{{ $shareDescription }}">
@if($shareImage)
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:image:secure_url" content="{{ $shareImage }}">
    <meta property="og:image:type" content="{{ $shareImageType }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $shareTitle }}">
@endif

{{-- Twitter / X --}}
<meta name="twitter:card" content="{{ $shareImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:url" content="{{ $canonicalUrl }}">
<meta name="twitter:title" content="{{ $shareTitle }}">
<meta name="twitter:description" content="{{ $shareDescription }}">
@if($shareImage)
    <meta name="twitter:image" content="{{ $shareImage }}">
    <meta name="twitter:image:alt" content="{{ $shareTitle }}">
@endif
@if($twitterHandle)
    <meta name="twitter:site" content="{{ $twitterHandle }}">
    <meta name="twitter:creator" content="{{ $twitterHandle }}">
@endif

{{-- App / PWA presentation --}}
<meta name="theme-color" content="#4f46e5">
<meta name="msapplication-TileColor" content="#4f46e5">
<meta name="application-name" content="{{ $eventName }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{ \Illuminate\Support\Str::limit($eventName, 12, '') }}">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="format-detection" content="telephone=yes">
@if($favicon)
    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $favicon }}">
@endif

{{-- Structured data for search + AI answer engines --}}
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => $shareTitle,
    'description' => $shareDescription,
    'url' => $canonicalUrl,
    'eventStatus' => 'https://schema.org/EventScheduled',
    'eventAttendanceMode' => $attendanceMode,
    'startDate' => $event->start_date?->toIso8601String(),
    'endDate' => $event->end_date?->toIso8601String(),
    'image' => $shareImage ? [$shareImage] : null,
    'location' => [
        '@type' => 'Place',
        'name' => $locationName,
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => trim(implode(', ', array_filter([
                $event->address_line1,
                $event->address_line2,
            ]))) ?: null,
            'addressLocality' => $event->city,
            'addressRegion' => $event->state,
            'addressCountry' => $event->country,
        ]),
    ],
    'organizer' => array_filter([
        '@type' => 'Organization',
        'name' => $event->manager_name ?: $eventName,
        'email' => $event->manager_email,
        'telephone' => $event->manager_phone,
        'url' => $event->website_url ?: $canonicalUrl,
    ]),
    'offers' => [
        '@type' => 'Offer',
        'url' => $canonicalUrl,
        'availability' => 'https://schema.org/InStock',
        'validFrom' => now()->toIso8601String(),
        'priceCurrency' => $event->currency ?: 'PKR',
    ],
    'keywords' => $event->seo_keywords,
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>

<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $shareTitle,
    'description' => $shareDescription,
    'url' => $canonicalUrl,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $eventName,
        'url' => $event->website_url ?: $canonicalUrl,
    ],
    'primaryImageOfPage' => $shareImage ? [
        '@type' => 'ImageObject',
        'url' => $shareImage,
        'width' => 1200,
        'height' => 630,
    ] : null,
    'about' => [
        '@type' => 'Event',
        'name' => $shareTitle,
        'url' => $canonicalUrl,
    ],
], fn ($value) => $value !== null), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
