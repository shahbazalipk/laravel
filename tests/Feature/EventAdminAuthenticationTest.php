<?php

namespace Tests\Feature;

use Tests\TestCase;

class EventAdminAuthenticationTest extends TestCase
{
    public function test_login_page_does_not_show_demo_credentials(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertDontSee('Demo credentials', false);
        $response->assertDontSee('admin@event.com', false);
    }
}
