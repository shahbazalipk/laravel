<?php

namespace Tests\Feature;

use Tests\TestCase;

class EventAdminAuthenticationTest extends TestCase
{
    public function test_login_page_requires_event_context(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertRedirect(route('event.landing'));
    }

    public function test_login_page_does_not_show_demo_credentials_with_valid_context(): void
    {
        config(['event.sso_secret' => 'test-secret']);

        $payload = base64_encode(json_encode([
            'event_id' => 30,
            'organization_id' => 8,
            'subdomain' => 'techtrip',
            'exp' => now()->addMinutes(10)->timestamp,
        ]));
        $signature = hash_hmac('sha256', $payload, 'test-secret');
        $ctx = $payload . '.' . $signature;

        $response = $this->get(route('admin.login', ['ctx' => $ctx]));

        $response->assertOk();
        $response->assertDontSee('Demo credentials', false);
        $response->assertDontSee('admin@event.com', false);
    }
}
