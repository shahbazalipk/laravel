<?php

namespace Tests\Unit;

use App\Services\OrgPortalUrlService;
use Tests\TestCase;

class OrgPortalUrlServiceTest extends TestCase
{
    public function test_uses_explicit_org_portal_url_when_configured(): void
    {
        config(['event.org_portal_url' => 'https://custom.example.com']);

        $service = new OrgPortalUrlService();

        $this->assertSame('https://custom.example.com', $service->baseUrl());
    }

    public function test_defaults_to_localhost_in_local_environment(): void
    {
        config(['event.org_portal_url' => null]);
        app()['env'] = 'local';

        $service = new OrgPortalUrlService();

        $this->assertSame('http://127.0.0.1:8000', $service->baseUrl());
    }

    public function test_defaults_to_app_subdomain_in_production(): void
    {
        config([
            'event.org_portal_url' => null,
            'event.org_portal_scheme' => 'https',
            'event.event_domain' => 'glimzo.ai',
        ]);
        app()['env'] = 'production';

        $service = new OrgPortalUrlService();

        $this->assertSame('https://app.glimzo.ai', $service->baseUrl());
    }
}
