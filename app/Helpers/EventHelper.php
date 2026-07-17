<?php

if (!function_exists('current_event')) {
    /**
     * Get the current event from the container
     *
     * @return \App\Models\Event|null
     */
    function current_event()
    {
        if (app()->has('current.event')) {
            return app('current.event');
        }
        
        // Fallback: try to get from session
        $eventId = session('event_id');
        if ($eventId) {
            return \App\Models\Event::find($eventId);
        }
        
        return null;
    }
}

if (!function_exists('current_event_id')) {
    /**
     * Get the current event ID
     *
     * @return int|null
     */
    function current_event_id()
    {
        return session('event_id') ?? config('event.event_id');
    }
}

if (!function_exists('current_org_id')) {
    /**
     * Get the current organization ID
     *
     * @return int|null
     */
    function current_org_id()
    {
        return session('org_id') ?? config('event.org_id');
    }
}

if (!function_exists('current_event_currency')) {
    /**
     * Get the current event's currency code (e.g. AED, USD).
     * Falls back to a provided default when no event/currency is available.
     */
    function current_event_currency(string $default = 'USD'): string
    {
        $event = current_event() ?? \App\Models\Event::getCurrentEvent();
        $currency = $event?->currency;

        return $currency !== null && $currency !== '' ? $currency : $default;
    }
}

if (!function_exists('format_money')) {
    /**
     * Format a monetary amount with the current event currency code.
     */
    function format_money(float|int|string|null $amount, int $decimals = 0, ?string $currency = null): string
    {
        $currency = $currency ?: current_event_currency();

        return $currency.' '.number_format((float) ($amount ?? 0), $decimals);
    }
}

if (!function_exists('storage_public_url')) {
    /**
     * Resolve a public disk path to a browser URL (local /storage or S3).
     */
    function storage_public_url(?string $path): ?string
    {
        if ($path === null || $path === '' || $path === '0') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }
}
