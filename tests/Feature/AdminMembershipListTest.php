<?php

namespace Tests\Feature;

use App\Enums\MembershipIdentifierType;
use App\Models\Event;
use App\Models\Membership;
use App\Models\MembershipCode;
use App\Services\AuditService;
use App\Services\HashService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminMembershipListTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);
        Storage::fake('public');

        Schema::dropIfExists('membership_codes');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('verification_type');
            $table->string('identifier_type', 32)->default('membership_id');
            $table->string('api_endpoint')->nullable();
            $table->string('api_method', 10)->nullable();
            $table->string('api_key')->nullable();
            $table->text('api_headers')->nullable();
            $table->longText('api_sample_request')->nullable();
            $table->longText('api_sample_response')->nullable();
            $table->string('file_path')->nullable();
            $table->string('color', 7)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('membership_codes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('membership_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('code');
            $table->integer('allowed_usage')->default(1);
            $table->integer('used')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('hash_mappings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('hash', 64)->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();
        });

        $this->event = Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'org_id' => 1,
            'name' => 'Membership Event',
        ]);
        app()->instance('current.event', $this->event);

        $audit = Mockery::mock(AuditService::class);
        $audit->shouldReceive('logCreated')->andReturnNull();
        $audit->shouldReceive('logUpdated')->andReturnNull();
        $audit->shouldReceive('logDeleted')->andReturnNull();
        $audit->shouldReceive('logToggled')->andReturnNull();
        $audit->shouldReceive('logImport')->andReturnNull();
        $this->app->instance(AuditService::class, $audit);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('membership_codes');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');
        Mockery::close();
        parent::tearDown();
    }

    private function actingAsAdmin()
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@test.com',
            'admin_is_primary' => true,
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    #[Test]
    public function index_lists_membership_lists(): void
    {
        $membership = Membership::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'IEEE Members',
            'verification_type' => 'upload_file',
            'identifier_type' => MembershipIdentifierType::MembershipId,
            'is_active' => true,
        ]);
        app(HashService::class)->generateHash($membership);

        $this->actingAsAdmin()
            ->get(route('admin.memberships.index'))
            ->assertOk()
            ->assertSee('data-testid="memberships-page"', false)
            ->assertSee('IEEE Members')
            ->assertSee('Membership ID');
    }

    #[Test]
    public function create_page_renders_api_placeholder_help_text(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.memberships.create'))
            ->assertOk()
            ->assertSee('{{identifier}}', false)
            ->assertSee('{{identifier_type}}', false)
            ->assertSee('data-testid="membership-api-sample-request"', false);
    }

    #[Test]
    public function it_creates_a_csv_email_list_and_imports_rows(): void
    {
        $csv = UploadedFile::fake()->createWithContent(
            'emails.csv',
            "email\nalice@example.com\nbob@example.com\n"
        );

        $this->actingAsAdmin()->post(route('admin.memberships.store'), [
            'name' => 'Approved Emails',
            'identifier_type' => 'email',
            'verification_type' => 'upload_file',
            'membership_file' => $csv,
            'is_active' => 1,
            'color' => '#6366f1',
            'sort_order' => 0,
        ])->assertRedirect(route('admin.memberships.index'));

        $membership = Membership::query()->where('name', 'Approved Emails')->first();
        $this->assertNotNull($membership);
        $this->assertSame(MembershipIdentifierType::Email, $membership->identifier_type);
        $this->assertSame(2, $membership->codes()->count());
        $this->assertTrue($membership->codes()->where('code', 'alice@example.com')->exists());
    }

    #[Test]
    public function it_creates_an_api_list_with_sample_request_and_response(): void
    {
        $this->actingAsAdmin()->post(route('admin.memberships.store'), [
            'name' => 'Student API',
            'identifier_type' => 'student_id',
            'verification_type' => 'third_party_api',
            'api_endpoint' => 'https://api.example.com/students/verify',
            'api_method' => 'POST',
            'api_key' => 'secret',
            'api_sample_request' => '{"student_id":"{{identifier}}"}',
            'api_sample_response' => '{"valid":true}',
            'is_active' => 1,
            'color' => '#22c55e',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.memberships.index'));

        $membership = Membership::query()->where('name', 'Student API')->first();
        $this->assertNotNull($membership);
        $this->assertSame('third_party_api', $membership->verification_type);
        $this->assertSame('POST', $membership->api_method);
        $this->assertStringContainsString('{{identifier}}', (string) $membership->api_sample_request);
        $this->assertStringContainsString('valid', (string) $membership->api_sample_response);

        $this->actingAsAdmin()
            ->get(route('admin.memberships.show', $membership))
            ->assertOk()
            ->assertSee('data-testid="membership-api-samples"', false)
            ->assertSee('Student IDs');
    }

    #[Test]
    public function csv_import_on_show_page_skips_duplicates_and_headers(): void
    {
        $membership = Membership::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Roster',
            'verification_type' => 'upload_file',
            'identifier_type' => MembershipIdentifierType::MembershipId,
            'is_active' => true,
        ]);
        app(HashService::class)->generateHash($membership);

        MembershipCode::query()->create([
            'membership_id' => $membership->id,
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'MEM-1',
            'allowed_usage' => 1,
            'used' => 0,
            'status' => 'active',
        ]);

        $csv = UploadedFile::fake()->createWithContent(
            'ids.csv',
            "membership_id\nMEM-1\nMEM-2\nMEM-3\n"
        );

        $this->actingAsAdmin()->post(route('admin.memberships.codes.import', $membership), [
            'import_file' => $csv,
            'allowed_usage' => 2,
        ])->assertRedirect(route('admin.memberships.show', $membership));

        $this->assertSame(3, $membership->codes()->count());
        $this->assertTrue($membership->codes()->where('code', 'MEM-2')->where('allowed_usage', 2)->exists());
    }

    #[Test]
    public function guests_cannot_access_membership_lists(): void
    {
        $this->get(route('admin.memberships.index'))->assertRedirect();
    }
}
