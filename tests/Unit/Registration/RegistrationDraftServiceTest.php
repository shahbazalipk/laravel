<?php

namespace Tests\Unit\Registration;

use App\Models\Event;
use App\Models\EventUrl;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use App\Registration\Services\RegistrationOtpService;
use App\Services\ProviderManager;
use App\Services\RegistrationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationDraftServiceTest extends TestCase
{
    private RegistrationDraftService $drafts;

    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);

        Schema::dropIfExists('registration_drafts');
        Schema::dropIfExists('event_urls');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('title')->nullable();
            $table->boolean('registration_form_active')->default(true);
            $table->boolean('email_verification_required')->default(false);
            $table->timestamp('online_reg_close')->nullable();
            $table->decimal('vat_percentage', 5, 2)->default(0);
            $table->boolean('tax_inclusive')->default(false);
            $table->string('currency', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('event_urls', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('slug');
            $table->string('type')->default('online');
            $table->boolean('is_active')->default(true);
            $table->json('enabled_categories')->nullable();
            $table->timestamps();
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
            $table->string('otp_hash', 64)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_last_sent_at')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        $this->drafts = new RegistrationDraftService(
            new OnlineRegistrationContext(app(RegistrationService::class))
        );
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registration_drafts');
        Schema::dropIfExists('event_urls');
        Schema::dropIfExists('events');
        Mockery::close();
        parent::tearDown();
    }

    private function makeEvent(array $overrides = []): Event
    {
        return Event::query()->create(array_merge([
            'organization_id' => 1,
            'title' => 'Tech Trip',
            'registration_form_active' => true,
            'email_verification_required' => false,
            'currency' => 'PKR',
        ], $overrides));
    }

    #[Test]
    public function it_starts_a_draft_with_hashed_resume_token_and_encrypted_payload(): void
    {
        $event = $this->makeEvent();
        $url = EventUrl::query()->create([
            'event_id' => $event->id,
            'organization_id' => 1,
            'slug' => 'tech-trip',
            'is_active' => true,
        ]);

        [$draft, $token] = $this->drafts->start($event, $url, 'Ada@Example.com');

        $this->assertSame('ada@example.com', $draft->email);
        $this->assertSame(RegistrationWizardStep::Email, $draft->current_step);
        $this->assertNotNull($draft->email_verified_at);
        $this->assertSame($this->drafts->hashToken($token), $draft->resume_token_hash);
        $this->assertSame('ada@example.com', $draft->payloadValue('email'));
        $this->assertTrue($draft->expires_at->greaterThan(now()->addDay()));

        $urlKey = $this->drafts->encodeUrlKey($draft);
        $this->assertNotSame($draft->public_id, $urlKey);
        $this->assertTrue($draft->is($this->drafts->findByUrlKey($urlKey, $event)));
        $this->assertNull($this->drafts->findByUrlKey('tampered-key', $event));
    }

    #[Test]
    public function it_finds_drafts_by_plain_resume_token(): void
    {
        $event = $this->makeEvent();
        [$draft, $token] = $this->drafts->start($event, null, 'user@example.com');

        $found = $this->drafts->findByPlainToken($token, $event);

        $this->assertTrue($draft->is($found));
        $this->assertNull($this->drafts->findByPlainToken('invalid-token', $event));
    }

    #[Test]
    public function it_blocks_skipping_ahead_and_requires_email_verification_when_enabled(): void
    {
        $event = $this->makeEvent(['email_verification_required' => true]);
        [$draft] = $this->drafts->start($event, null, 'user@example.com');

        $this->assertNull($draft->email_verified_at);

        $this->expectException(\InvalidArgumentException::class);
        $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Information);
    }

    #[Test]
    public function otp_service_locks_out_after_max_attempts(): void
    {
        $event = $this->makeEvent(['email_verification_required' => true, 'title' => 'OTP Event']);
        [$draft] = $this->drafts->start($event, null, 'otp@example.com');

        $provider = Mockery::mock(ProviderManager::class);
        $provider->shouldReceive('getProvider')->andReturn(new class {
            public function send(...$args): string
            {
                return 'msg-1';
            }
        });
        $otp = new RegistrationOtpService($provider);
        $otp->issueAndSend($draft->fresh(), $event, 'https://example.test/resume');

        $draft->refresh();
        for ($i = 0; $i < RegistrationOtpService::MAX_ATTEMPTS; $i++) {
            try {
                $otp->verify($draft->fresh(), '000000');
            } catch (\InvalidArgumentException) {
                // expected
            }
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Too many invalid attempts');
        $otp->verify($draft->fresh(), '000000');
    }
}
