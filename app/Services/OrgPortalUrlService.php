<?php

namespace App\Services;

class OrgPortalUrlService
{
    public function baseUrl(): string
    {
        if ($url = config('event.org_portal_url')) {
            return rtrim((string) $url, '/');
        }

        if (app()->environment('local')) {
            return 'http://127.0.0.1:8000';
        }

        $scheme = config('event.org_portal_scheme', 'https');
        $domain = config('event.event_domain', 'glimzo.ai');

        return "{$scheme}://app.{$domain}";
    }
}
