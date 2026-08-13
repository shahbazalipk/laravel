<?php

namespace Tests\Unit\Registration;

use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use App\Registration\Services\AdminRegistrationListingService;
use App\Services\RegistrationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminRegistrationListingServiceTest extends TestCase
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
            $table->timestamps();
        });

        Schema::create('registration_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('registration_category_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('payment_status')->nullable();
            $table->boolean('checked_in')->default(false);
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

        Event::query()->create([
            'organization_id' => 1,
            'title' => 'Event',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registration_drafts');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    #[Test]
    public function it_lists_incomplete_drafts_and_filters_by_draft_stage(): void
    {
        Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_number' => 'REG-DONE',
            'first_name' => 'Done',
            'last_name' => 'User',
            'email' => 'done@example.com',
            'registration_type' => 'individual',
            'payment_status' => 'paid',
        ]);

        RegistrationDraft::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'draft@example.com',
            'resume_token_hash' => hash('sha256', 'token-a'),
            'payload' => [
                'first_name' => 'Left',
                'last_name' => 'Early',
                'company_name' => 'Acme',
            ],
            'current_step' => RegistrationWizardStep::Information,
            'email_verified_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        RegistrationDraft::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'completed-draft@example.com',
            'resume_token_hash' => hash('sha256', 'token-b'),
            'payload' => ['first_name' => 'Done'],
            'current_step' => RegistrationWizardStep::Confirmation,
            'email_verified_at' => now(),
            'registration_id' => 1,
            'completed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $service = new AdminRegistrationListingService(app(RegistrationService::class));

        $all = $service->paginate(['stage' => 'all']);
        $this->assertSame(2, $all->total());
        $this->assertTrue($all->getCollection()->contains(fn ($row) => $row->kind === 'draft' && $row->email === 'draft@example.com'));
        $this->assertTrue($all->getCollection()->contains(fn ($row) => $row->kind === 'registration'));

        $drafts = $service->paginate(['stage' => 'draft']);
        $this->assertSame(1, $drafts->total());
        $this->assertSame('Draft', $drafts->first()->stage);
        $this->assertSame('Step 3: Information', $drafts->first()->stepLabel);

        $registered = $service->paginate(['stage' => 'registered']);
        $this->assertSame(1, $registered->total());
        $this->assertSame('Registered', $registered->first()->stage);

        $stats = $service->statistics();
        $this->assertSame(1, $stats['drafts']);

        $unpaged = $service->all(['stage' => 'all']);
        $this->assertCount(2, $unpaged);
        $this->assertTrue($unpaged->contains(fn ($row) => $row->kind === 'draft'));
        $this->assertTrue($unpaged->contains(fn ($row) => $row->kind === 'registration'));
    }

    #[Test]
    public function it_filters_drafts_by_the_step_where_registration_stopped(): void
    {
        foreach ([
            ['email' => 'information@example.com', 'step' => RegistrationWizardStep::Information],
            ['email' => 'category@example.com', 'step' => RegistrationWizardStep::Category],
        ] as $index => $data) {
            RegistrationDraft::query()->create([
                'event_id' => 1,
                'org_id' => 1,
                'email' => $data['email'],
                'resume_token_hash' => hash('sha256', 'step-token-'.$index),
                'payload' => ['first_name' => 'Draft'],
                'current_step' => $data['step'],
                'email_verified_at' => now(),
                'expires_at' => now()->addDay(),
            ]);
        }

        $service = new AdminRegistrationListingService(app(RegistrationService::class));
        $results = $service->paginate([
            'stage' => 'all',
            'abandoned_step' => RegistrationWizardStep::Category->value,
        ]);

        $this->assertSame(1, $results->total());
        $this->assertSame('category@example.com', $results->first()->email);
        $this->assertSame('Step 2: Category', $results->first()->stepLabel);
        $this->assertTrue($results->getCollection()->every(
            fn ($row) => $row->kind === 'draft'
        ));
    }

    #[Test]
    public function it_filters_registrations_by_partially_paid_status(): void
    {
        Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_number' => 'REG-PAID',
            'first_name' => 'Fully',
            'last_name' => 'Paid',
            'email' => 'paid@example.com',
            'registration_type' => 'individual',
            'payment_status' => 'paid',
        ]);
        Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_number' => 'REG-PARTIAL',
            'first_name' => 'Partial',
            'last_name' => 'Paid',
            'email' => 'partial@example.com',
            'registration_type' => 'individual',
            'payment_status' => 'partially_paid',
        ]);

        $service = new AdminRegistrationListingService(app(RegistrationService::class));
        $results = $service->paginate([
            'stage' => 'registered',
            'payment_status' => 'partially_paid',
        ]);

        $this->assertSame(1, $results->total());
        $this->assertSame('partial@example.com', $results->first()->email);
        $this->assertSame('partially_paid', $results->first()->paymentStatus);
    }
}
