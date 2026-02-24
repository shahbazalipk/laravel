<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class ResolveEventFromSubdomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        
        Log::info('ResolveEventFromSubdomain: Processing request', ['host' => $host]);
        
        // Skip for root domain and app subdomain
        if ($this->shouldSkipResolution($host)) {
            Log::info('ResolveEventFromSubdomain: Skipping resolution', ['host' => $host]);
            return $next($request);
        }
        
        // Extract subdomain
        $subdomain = $this->extractSubdomain($host);
        
        if (!$subdomain) {
            Log::warning('ResolveEventFromSubdomain: No subdomain found', ['host' => $host]);
            return redirect()->away('https://glimzo.ai');
        }
        
        Log::info('ResolveEventFromSubdomain: Extracted subdomain', ['subdomain' => $subdomain]);
        
        // Check if event is already resolved in session
        if (session('event_id') && session('event_subdomain') === $subdomain) {
            Log::info('ResolveEventFromSubdomain: Using cached event from session', [
                'event_id' => session('event_id'),
                'org_id' => session('org_id')
            ]);
            
            // Set config for this request
            config([
                'event.event_id' => session('event_id'),
                'event.org_id' => session('org_id'),
            ]);
            
            return $next($request);
        }
        
        // Lookup event by subdomain
        $event = Event::where('subdomain', $subdomain)->first();
        
        if (!$event) {
            Log::warning('ResolveEventFromSubdomain: Event not found', ['subdomain' => $subdomain]);
            return redirect()->away('https://glimzo.ai');
        }
        
        Log::info('ResolveEventFromSubdomain: Event found', [
            'event_id' => $event->id,
            'org_id' => $event->organization_id ?? $event->org_id,
            'event_name' => $event->event_name ?? $event->title
        ]);
        
        // Store in session
        session([
            'event_id' => $event->id,
            'org_id' => $event->organization_id ?? $event->org_id,
            'event_subdomain' => $subdomain,
            'event_name' => $event->event_name ?? $event->title,
        ]);
        
        // Set config for this request
        config([
            'event.event_id' => $event->id,
            'event.org_id' => $event->organization_id ?? $event->org_id,
        ]);
        
        // Make event globally accessible via container
        app()->instance('current.event', $event);
        
        return $next($request);
    }
    
    /**
     * Check if resolution should be skipped for this host
     *
     * @param string $host
     * @return bool
     */
    protected function shouldSkipResolution(string $host): bool
    {
        // Skip for localhost
        if (str_starts_with($host, 'localhost') || str_starts_with($host, '127.0.0.1')) {
            return true;
        }
        
        // Skip for root domain
        if ($host === 'glimzo.ai' || $host === 'www.glimzo.ai') {
            return true;
        }
        
        // Skip for app subdomain
        if ($host === 'app.glimzo.ai') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Extract subdomain from host
     *
     * @param string $host
     * @return string|null
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // Split by dots
        $parts = explode('.', $host);
        
        // Need at least 3 parts for subdomain (subdomain.glimzo.ai)
        if (count($parts) < 3) {
            return null;
        }
        
        // Return first part as subdomain
        return $parts[0];
    }
}
