<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminGroupStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
        ]);

        Schema::dropIfExists('event_group_tag');
        Schema::dropIfExists('exhibitor_tags');
        Schema::dropIfExists('event_groups');
        Schema::dropIfExists('group_types');
        Schema::dropIfExists('custom_form_conditions');
        Schema::dropIfExists('custom_form_question_options');
        Schema::dropIfExists('custom_form_questions');
        Schema::dropIfExists('custom_forms');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('hash_mappings');

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
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_groups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('group_name');
            $table->unsignedBigInteger('group_type_id');
            $table->string('organization_name')->nullable();
            $table->unsignedBigInteger('industry_id')->nullable();
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->integer('allowed_attendees');
            $table->string('invoice_number')->nullable();
            $table->string('primary_contact_name');
            $table->string('primary_contact_email');
            $table->string('primary_contact_phone', 50);
            $table->string('secondary_contact_name')->nullable();
            $table->string('secondary_contact_email')->nullable();
            $table->string('secondary_contact_phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('special_requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_vip')->default(false);
            $table->integer('sort_order')->default(0);
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

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->json('properties')->nullable();
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
            $table->string('audience_unique', 32)->nullable();
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

        GroupType::create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Delegation',
            'slug' => 'delegation',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('event_group_tag');
        Schema::dropIfExists('exhibitor_tags');
        Schema::dropIfExists('event_groups');
        Schema::dropIfExists('group_types');
        Schema::dropIfExists('custom_form_conditions');
        Schema::dropIfExists('custom_form_question_options');
        Schema::dropIfExists('custom_form_questions');
        Schema::dropIfExists('custom_forms');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('hash_mappings');

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
            'admin_is_primary' => true,
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    #[Test]
    public function admin_can_create_a_group_without_exception(): void
    {
        $response = $this->actingAsAdmin()->post(route('admin.groups.store'), [
            'group_name' => 'Media Delegation',
            'group_type_id' => 1,
            'allowed_attendees' => 10,
            'primary_contact_name' => 'John Smith',
            'primary_contact_email' => 'john@example.com',
            'primary_contact_phone' => '0501234567',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $group = Group::first();
        $this->assertNotNull($group);

        $this->assertDatabaseHas('event_groups', [
            'group_name' => 'Media Delegation',
            'primary_contact_email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Group::class,
            'subject_id' => $group->id,
        ]);
    }
}
