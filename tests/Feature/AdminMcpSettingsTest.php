<?php

namespace Tests\Feature;

use App\Mcp\Auth\McpAccessToken;
use App\Models\Event;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminMcpSettingsTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'mcp.event_operations.path' => '/mcp/event-operations',
        ]);

        Schema::dropIfExists('mcp_tool_audit_logs');
        Schema::dropIfExists('mcp_access_tokens');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('mcp_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('token_hash', 64)->unique();
            $table->json('abilities');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        $this->event = Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'org_id' => 1,
            'name' => 'MCP Test Event',
            'title' => 'MCP Test Event',
        ]);

        app()->instance('current.event', $this->event);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('mcp_tool_audit_logs');
        Schema::dropIfExists('mcp_access_tokens');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    private function actingAsAdmin(array $overrides = [])
    {
        return $this->withSession(array_merge([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@test.com',
            'admin_is_primary' => true,
            'event_id' => 1,
            'org_id' => 1,
        ], $overrides));
    }

    #[Test]
    public function mcp_settings_page_shows_url_for_current_event(): void
    {
        $response = $this->actingAsAdmin()->get(route('admin.mcp.index'));

        $response->assertOk();
        $response->assertSee('data-testid="mcp-settings-page"', false);
        $response->assertSee('data-testid="mcp-url-input"', false);
        $response->assertSee(url('/mcp/event-operations'), false);
        $response->assertSee('data-testid="mcp-generate-form"', false);
        $response->assertSee('event #1', false);
    }

    #[Test]
    public function it_generates_an_event_scoped_mcp_token_once(): void
    {
        $response = $this->actingAsAdmin()->post(route('admin.mcp.tokens.store'), [
            'name' => 'n8n production',
            'abilities' => ['registrations.read'],
            'expires_in_days' => 30,
        ]);

        $response->assertRedirect(route('admin.mcp.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('mcp_plain_text_token');

        $plain = session('mcp_plain_text_token');
        $this->assertIsString($plain);
        $this->assertStringStartsWith('evt_mcp_', $plain);

        $this->assertDatabaseHas('mcp_access_tokens', [
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'n8n production',
            'token_hash' => hash('sha256', $plain),
        ]);

        $follow = $this->actingAsAdmin()->get(route('admin.mcp.index'));
        $follow->assertOk();
        $follow->assertSee('data-testid="mcp-new-token-alert"', false);
        $follow->assertSee($plain, false);
        $follow->assertSee('n8n production');
    }

    #[Test]
    public function it_revokes_tokens_for_the_current_event_only(): void
    {
        $token = McpAccessToken::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Revoke me',
            'token_hash' => hash('sha256', 'plain-token'),
            'abilities' => ['registrations.read'],
        ]);

        $other = McpAccessToken::query()->create([
            'event_id' => 99,
            'org_id' => 88,
            'name' => 'Other event',
            'token_hash' => hash('sha256', 'other-token'),
            'abilities' => ['registrations.read'],
        ]);

        $this->actingAsAdmin()
            ->delete(route('admin.mcp.tokens.revoke', $token))
            ->assertRedirect(route('admin.mcp.index'));

        $this->assertNotNull($token->fresh()->revoked_at);

        $this->actingAsAdmin()
            ->delete(route('admin.mcp.tokens.revoke', $other))
            ->assertNotFound();

        $this->assertNull($other->fresh()->revoked_at);
    }

    #[Test]
    public function guests_cannot_open_mcp_settings(): void
    {
        $this->get(route('admin.mcp.index'))->assertRedirect();
    }
}
