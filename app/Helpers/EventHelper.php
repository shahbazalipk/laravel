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
