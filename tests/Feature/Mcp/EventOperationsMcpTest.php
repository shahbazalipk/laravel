<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Auth\McpAccessContext;
use App\Mcp\Auth\McpAccessToken;
use App\Mcp\Auth\McpTokenService;
use App\Mcp\Servers\EventOperationsServer;
use App\Mcp\Tools\CheckInSummaryTool;
use App\Mcp\Tools\PaymentSummaryTool;
use App\Mcp\Tools\RecentRegistrationsTool;
use App\Mcp\Tools\RegistrationsSummaryTool;
use App\Mcp\Tools\RegistrationStatusTool;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventOperationsMcpTest extends TestCase
{
    private McpAccessToken $token;

    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 10, 'event.org_id' => 20]);

        $this->createRegistrationReferenceTables();
        (require database_path('migrations/2026_02_16_144835_create_registrations_table.php'))->up();
        (require database_path('migrations/2026_07_17_211600_create_registration_payment_entries_table.php'))->up();
        (require database_path('migrations/2026_07_18_059000_create_mcp_access_tables.php'))->up();

        $this->token = McpAccessToken::query()->create([
            'event_id' => 10,
            'org_id' => 20,
            'name' => 'Test integration',
            'token_hash' => hash('sha256', 'test-token'),
            'abilities' => ['registrations.read'],
        ]);
        app()->instance(McpAccessContext::class, new McpAccessContext($this->token));

        DB::table('registration_statuses')->insert([
            'id' => 1,
            'event_id' => 10,
            'org_id' => 20,
            'name' => 'Confirmed',
            'slug' => 'confirmed',
        ]);
        DB::table('registration_categories')->insert([
            'id' => 1,
            'event_id' => 10,
            'org_id' => 20,
            'name' => 'Delegate',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('mcp_tool_audit_logs');
        Schema::dropIfExists('mcp_access_tokens');
        Schema::dropIfExists('registration_payment_entries');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('registration_statuses');

        parent::tearDown();
    }

    #[Test]
    public function summary_is_tenant_scoped_and_audited(): void
    {
        $this->createRegistration('REG-001', 10, 20);
        $this->createRegistration('REG-OTHER', 99, 88);

        EventOperationsServer::tool(RegistrationsSummaryTool::class)
            ->assertOk()
            ->assertSee('"total":1')
            ->assertSee('"confirmed":1')
            ->assertSee('"Delegate":1');

        $this->assertDatabaseHas('mcp_tool_audit_logs', [
            'mcp_access_token_id' => $this->token->id,
            'event_id' => 10,
            'org_id' => 20,
            'tool' => 'registrations_summary',
            'status' => 'success',
        ]);
    }

    #[Test]
    public function registration_lookup_returns_status_without_contact_details(): void
    {
        $this->createRegistration('REG-PRIVATE', 10, 20);

        EventOperationsServer::tool(RegistrationStatusTool::class, [
            'registration_number' => 'REG-PRIVATE',
        ])
            ->assertOk()
            ->assertSee('REG-PRIVATE')
            ->assertSee('confirmed')
            ->assertDontSee('private@example.com');
    }

    #[Test]
    public function bearer_tokens_are_hashed_and_missing_tokens_are_rejected(): void
    {
        $issued = app(McpTokenService::class)->issue(10, 20, 'n8n');

        $this->assertStringStartsWith('evt_mcp_', $issued['plain_text_token']);
        $this->assertNotSame($issued['plain_text_token'], $issued['token']->getRawOriginal('token_hash'));
        $this->postJson('/mcp/event-operations', [])->assertUnauthorized();
    }

    #[Test]
    public function a_token_without_the_required_ability_is_denied_and_audited(): void
    {
        $this->token->update(['abilities' => ['finance.read']]);
        app()->instance(McpAccessContext::class, new McpAccessContext($this->token->fresh()));

        EventOperationsServer::tool(RegistrationsSummaryTool::class)
            ->assertHasErrors(['This token is not authorized to use this tool.']);

        $this->assertDatabaseHas('mcp_tool_audit_logs', [
            'mcp_access_token_id' => $this->token->id,
            'tool' => 'registrations_summary',
            'status' => 'denied',
        ]);
    }

    #[Test]
    public function payment_recent_and_checkin_tools_return_operational_summaries(): void
    {
        $paidId = $this->createRegistration('REG-PAID', 10, 20);
        $recentId = $this->createRegistration('REG-RECENT', 10, 20);

        DB::table('registration_payment_entries')->insert([
            'public_id' => fake()->uuid(),
            'event_id' => 10,
            'org_id' => 20,
            'registration_id' => $paidId,
            'type' => 'payment',
            'status' => 'succeeded',
            'amount' => 100,
            'currency' => 'AED',
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
        DB::table('registrations')->where('id', $recentId)->update([
            'checked_in' => true,
            'checked_in_at' => now(),
            'created_at' => now()->addSecond(),
        ]);

        EventOperationsServer::tool(PaymentSummaryTool::class)
            ->assertOk()
            ->assertSee('"paid":1')
            ->assertSee('"pending":1')
            ->assertSee('"net_paid":100');

        EventOperationsServer::tool(RecentRegistrationsTool::class, ['limit' => 1])
            ->assertOk()
            ->assertSee('REG-RECENT')
            ->assertDontSee('private@example.com');

        EventOperationsServer::tool(CheckInSummaryTool::class)
            ->assertOk()
            ->assertSee('"checked_in":1')
            ->assertSee('"not_checked_in":1')
            ->assertSee('"check_in_rate_percent":50');
    }

    private function createRegistration(string $number, int $eventId, int $orgId): int
    {
        return DB::table('registrations')->insertGetId([
            'event_id' => $eventId,
            'org_id' => $orgId,
            'registration_category_id' => 1,
            'registration_status_id' => 1,
            'registration_type' => 'individual',
            'first_name' => 'Private',
            'last_name' => 'Person',
            'email' => 'private@example.com',
            'phone' => '000',
            'company_name' => 'Example',
            'base_price' => 100,
            'tax_amount' => 0,
            'total_amount' => 100,
            'currency' => 'AED',
            'payment_status' => 'pending',
            'registration_number' => $number,
            'checked_in' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createRegistrationReferenceTables(): void
    {
        Schema::create('registration_statuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('registration_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->timestamps();
        });
    }
}
