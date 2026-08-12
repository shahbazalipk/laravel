<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupType;
use App\Models\Registration;
use App\Payments\Models\GroupPaymentEntry;
use App\Services\AuditService;
use App\Services\HashService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminGroupRegistrationAndPaymentTest extends TestCase
{
    private Group $group;

    private Registration $registration;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
            'modules.finance.enabled' => false,
            'modules.projects.enabled' => false,
        ]);

        Schema::dropIfExists('group_payment_entries');
        Schema::dropIfExists('custom_form_responses');
        Schema::dropIfExists('custom_form_conditions');
        Schema::dropIfExists('custom_form_question_options');
        Schema::dropIfExists('custom_form_questions');
        Schema::dropIfExists('custom_forms');
        Schema::dropIfExists('event_group_tag');
        Schema::dropIfExists('exhibitor_tags');
        Schema::dropIfExists('industries');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('event_groups');
        Schema::dropIfExists('group_types');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('activity_logs');

        Schema::create('hash_mappings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('hash', 64)->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();
        });

        Schema::create('group_types', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('registration_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->decimal('price', 12, 2)->default(500);
            $table->string('currency', 10)->default('AED');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('event_groups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('group_name');
            $table->unsignedBigInteger('group_type_id');
            $table->integer('allowed_attendees');
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('payment_status', 32)->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->string('primary_contact_name');
            $table->string('primary_contact_email');
            $table->string('primary_contact_phone', 50);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_vip')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('registration_category_id')->nullable();
            $table->string('registration_type')->default('individual');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('exhibitor_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->decimal('total_amount', 12, 2)->default(500);
            $table->string('currency', 10)->default('AED');
            $table->string('payment_status', 32)->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('industries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exhibitor_tags', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_group_tag', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_group_id');
            $table->unsignedBigInteger('exhibitor_tag_id');
            $table->timestamps();
        });

        Schema::create('custom_forms', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('slug');
            $table->string('audience', 32);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_form_questions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('custom_form_id');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_form_question_options', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('custom_form_question_id');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_form_conditions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('custom_form_id');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_form_responses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('respondent_type')->nullable();
            $table->unsignedBigInteger('respondent_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('group_payment_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('event_group_id');
            $table->string('type', 32);
            $table->string('status', 32);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10);
            $table->string('method', 100)->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('occurred_at');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reverses_entry_id')->nullable();
            $table->string('recorded_by_type')->nullable();
            $table->unsignedBigInteger('recorded_by_id')->nullable();
            $table->string('recorded_by_name')->nullable();
            $table->string('recorded_by_email')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });

        $audit = Mockery::mock(AuditService::class);
        $audit->shouldReceive('log')->andReturnNull();
        $this->app->instance(AuditService::class, $audit);

        GroupType::create(['event_id' => 1, 'org_id' => 1, 'name' => 'Delegation']);

        \DB::table('registration_categories')->insert([
            'id' => 1, 'event_id' => 1, 'org_id' => 1, 'name' => 'Standard', 'price' => 500, 'currency' => 'AED', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->group = Group::create([
            'event_id' => 1,
            'org_id' => 1,
            'group_name' => 'VIP Delegation',
            'group_type_id' => 1,
            'allowed_attendees' => 5,
            'total_amount' => 2500,
            'currency' => 'AED',
            'primary_contact_name' => 'Lead',
            'primary_contact_email' => 'lead@example.com',
            'primary_contact_phone' => '0501111111',
        ]);

        app(HashService::class)->generateHash($this->group);
        $this->group->refresh();

        $this->registration = Registration::create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => 1,
            'registration_number' => 'REG-G1',
            'first_name' => 'Ali',
            'last_name' => 'Khan',
            'email' => 'ali@example.com',
            'phone' => '0502222222',
            'company_name' => 'Acme',
            'total_amount' => 500,
            'currency' => 'AED',
        ]);

        app(HashService::class)->generateHash($this->registration);
        $this->registration->refresh();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('group_payment_entries');
        Schema::dropIfExists('custom_form_responses');
        Schema::dropIfExists('custom_form_conditions');
        Schema::dropIfExists('custom_form_question_options');
        Schema::dropIfExists('custom_form_questions');
        Schema::dropIfExists('custom_forms');
        Schema::dropIfExists('event_group_tag');
        Schema::dropIfExists('exhibitor_tags');
        Schema::dropIfExists('industries');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('event_groups');
        Schema::dropIfExists('group_types');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('activity_logs');

        parent::tearDown();
    }

    private function actingAsAdmin()
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_name' => 'Test Admin',
            'admin_email' => 'admin@test.com',
            'admin_type' => 'event_admin',
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    #[Test]
    public function admin_can_link_registration_to_group(): void
    {
        $response = $this->actingAsAdmin()->post(
            route('admin.groups.registrations.store', $this->group),
            ['registration_id' => $this->registration->id]
        );

        $response->assertRedirect(route('admin.groups.show', $this->group));

        $this->registration->refresh();
        $this->assertSame('group', $this->registration->registration_type);
        $this->assertSame($this->group->id, $this->registration->group_id);
    }

    #[Test]
    public function admin_can_record_group_payment(): void
    {
        $response = $this->actingAsAdmin()->post(
            route('admin.groups.payments.store', $this->group),
            [
                'amount' => 1000,
                'currency' => 'AED',
                'method' => 'Bank Transfer',
                'status' => 'succeeded',
            ]
        );

        $response->assertRedirect(route('admin.groups.show', $this->group));

        $this->assertDatabaseHas('group_payment_entries', [
            'event_group_id' => $this->group->id,
            'amount' => 1000,
            'type' => 'payment',
        ]);

        $this->group->refresh();
        $this->assertSame('partially_paid', $this->group->payment_status);
    }

    #[Test]
    public function group_show_displays_members_and_payment_sections(): void
    {
        $this->actingAsAdmin()->post(
            route('admin.groups.registrations.store', $this->group),
            ['registration_id' => $this->registration->id]
        );

        $response = $this->actingAsAdmin()->get(route('admin.groups.show', $this->group));

        $response->assertOk();
        $response->assertSee('data-testid="group-members-card"', false);
        $response->assertSee('data-testid="group-payment-summary-card"', false);
        $response->assertSee('REG-G1');
    }
}
