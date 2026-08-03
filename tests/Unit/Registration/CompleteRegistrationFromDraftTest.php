<?php

namespace Tests\Unit\Registration;

use App\Forms\Services\FormResponseService;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Models\RegistrationStatus;
use App\Payments\Services\RecordRegistrationPayment;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use App\Registration\Services\CompleteRegistrationFromDraft;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use App\Services\EventUrlAnalyticsService;
use App\Services\PromoCodeService;
use App\Services\RegistrationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompleteRegistrationFromDraftTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
        ]);

        Schema::dropIfExists('registration_drafts');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('events');

        Schema::create('hash_mappings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('hash', 64)->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('title')->nullable();
            $table->boolean('registration_form_active')->default(true);
            $table->boolean('email_verification_required')->default(false);
            $table->decimal('vat_percentage', 5, 2)->default(0);
            $table->boolean('tax_inclusive')->default(false);
            $table->string('currency', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('registration_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->nullable();
            $table->decimal('vat_percentage', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });

        Schema::create('registration_statuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('email')->nullable();
            $table->string('payment_status')->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('registration_drafts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('event_url_id')->nullable();
            $table->string('email');
            $table->string('resume_token_hash', 64)->unique();
            $table->text('payload')->nullable();
            $table->string('current_step', 32)->default('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registration_drafts');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('events');
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_returns_existing_registration_when_draft_already_completed(): void
    {
        $event = Event::query()->create([
            'organization_id' => 1,
            'title' => 'Event',
            'registration_form_active' => true,
            'currency' => 'PKR',
        ]);

        $registration = Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'done@example.com',
            'payment_status' => 'paid',
            'total_amount' => 0,
        ]);

        $draft = RegistrationDraft::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'done@example.com',
            'resume_token_hash' => hash('sha256', 'token'),
            'payload' => ['terms_accepted' => true],
            'current_step' => RegistrationWizardStep::Confirmation,
            'email_verified_at' => now(),
            'registration_id' => $registration->id,
            'completed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $service = new CompleteRegistrationFromDraft(
            Mockery::mock(RegistrationService::class),
            Mockery::mock(OnlineRegistrationContext::class),
            Mockery::mock(RegistrationDraftService::class),
            Mockery::mock(RecordRegistrationPayment::class),
            Mockery::mock(FormResponseService::class),
            Mockery::mock(PromoCodeService::class),
            Mockery::mock(EventUrlAnalyticsService::class),
        );

        $result = $service->execute($draft, $event, Request::create('/'));

        $this->assertTrue($registration->is($result));
    }

    #[Test]
    public function wizard_step_enum_enforces_forward_only_access(): void
    {
        $this->assertTrue(RegistrationWizardStep::Email->canAccessFrom(RegistrationWizardStep::Confirmation));
        $this->assertTrue(RegistrationWizardStep::Information->canAccessFrom(RegistrationWizardStep::Information));
        // Category comes before Information, so revisiting Category from Information is allowed.
        $this->assertTrue(RegistrationWizardStep::Category->canAccessFrom(RegistrationWizardStep::Information));
        $this->assertFalse(RegistrationWizardStep::Confirmation->canAccessFrom(RegistrationWizardStep::Category));
        $this->assertSame(RegistrationWizardStep::Information, RegistrationWizardStep::Category->next());
    }
}
