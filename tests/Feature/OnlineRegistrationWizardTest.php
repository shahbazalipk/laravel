<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventUrl;
use App\Models\Industry;
use App\Models\Partner;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Models\RegistrationStatus;
use App\Models\Sponsor;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use App\Registration\Services\RegistrationDraftService;
use App\Registration\Services\RegistrationOtpService;
use App\Services\ProviderManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OnlineRegistrationWizardTest extends TestCase
{
    private Event $event;

    private EventUrl $eventUrl;

    private RegistrationCategory $freeCategory;

    private RegistrationCategory $paidCategory;

    private Industry $industry;

    private string $slug = 'tech-trip';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
        ]);

        $this->dropTables();
        $this->createTables();

        $this->event = Event::query()->create([
            'organization_id' => 1,
            'title' => 'Tech Trip',
            'registration_form_active' => true,
            'email_verification_required' => false,
            'currency' => 'PKR',
            'vat_percentage' => 5,
            'tax_inclusive' => false,
            'start_date' => now()->addMonth()->setTime(9, 30),
            'end_date' => now()->addMonth()->addDay()->setTime(17, 0),
            'timezone' => 'Asia/Karachi',
            'location' => 'Expo Centre',
            'city' => 'Karachi',
            'country' => 'Pakistan',
            'format' => 'in-person',
            'manager_name' => 'Event Team',
            'manager_email' => 'events@example.com',
            'manager_phone' => '+92 300 1234567',
            'footer_information' => 'Our team is available for registration support.',
            'website_url' => 'https://example.com',
        ]);

        app()->instance('current.event', $this->event);

        $this->freeCategory = RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Free Pass',
            'price' => 0,
            'currency' => 'PKR',
            'is_active' => true,
            'visible' => true,
            'sort_order' => 1,
        ]);

        $this->paidCategory = RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Paid Pass',
            'price' => 1000,
            'currency' => 'PKR',
            'vat_percentage' => 5,
            'is_active' => true,
            'visible' => true,
            'sort_order' => 2,
        ]);

        RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Hidden Pass',
            'price' => 50,
            'currency' => 'PKR',
            'is_active' => true,
            'visible' => true,
            'sort_order' => 3,
        ]);

        $this->eventUrl = EventUrl::query()->create([
            'event_id' => 1,
            'organization_id' => 1,
            'name' => 'Tech Trip',
            'slug' => $this->slug,
            'type' => 'online',
            'is_active' => true,
            'enabled_categories' => [$this->freeCategory->id, $this->paidCategory->id],
        ]);

        $this->industry = Industry::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Technology',
            'slug' => 'technology',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        RegistrationStatus::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Confirmed',
            'slug' => 'confirmed',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        $this->dropTables();
        parent::tearDown();
    }

    private function dropTables(): void
    {
        Schema::dropIfExists('registration_drafts');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('event_url_partner');
        Schema::dropIfExists('event_url_sponsor');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('sponsors');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('industries');
        Schema::dropIfExists('event_urls');
        Schema::dropIfExists('events');
    }

    private function createTables(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('title')->nullable();
            $table->boolean('registration_form_active')->default(true);
            $table->boolean('email_verification_required')->default(false);
            $table->boolean('code_verification_required')->default(false);
            $table->timestamp('online_reg_close')->nullable();
            $table->decimal('vat_percentage', 5, 2)->default(0);
            $table->boolean('tax_inclusive')->default(false);
            $table->string('currency', 10)->nullable();
            $table->string('smtp_username')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('timezone')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('format')->nullable();
            $table->string('type')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('map_url')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_email')->nullable();
            $table->string('manager_phone')->nullable();
            $table->text('footer_information')->nullable();
            $table->string('website_url')->nullable();
            $table->timestamps();
        });

        Schema::create('event_urls', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug');
            $table->string('type')->default('online');
            $table->boolean('is_active')->default(true);
            $table->json('enabled_categories')->nullable();
            $table->boolean('allow_reprint')->default(false);
            $table->boolean('allow_print_from_photo')->default(false);
            $table->boolean('enable_barcode_scanner')->default(true);
            $table->boolean('enable_manual_input')->default(true);
            $table->text('description')->nullable();
            $table->longText('custom_html')->nullable();
            $table->timestamps();
        });

        foreach (['sponsors', 'partners'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('org_id');
                $table->boolean('is_active')->default(true);
                $table->boolean('visible_online')->default(true);
                $table->string('name');
                $table->string('sponsorship_label')->nullable();
                $table->string('type')->nullable();
                $table->text('description')->nullable();
                $table->string('logo_thumbnail')->nullable();
                $table->string('logo_defined_size')->nullable();
                $table->string('website_url')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::create('event_url_sponsor', function (Blueprint $table): void {
            $table->unsignedBigInteger('event_url_id');
            $table->unsignedBigInteger('sponsor_id');
            $table->primary(['event_url_id', 'sponsor_id']);
        });

        Schema::create('event_url_partner', function (Blueprint $table): void {
            $table->unsignedBigInteger('event_url_id');
            $table->unsignedBigInteger('partner_id');
            $table->primary(['event_url_id', 'partner_id']);
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
            $table->integer('sort_order')->default(0);
            $table->boolean('password_required')->default(false);
            $table->string('password')->nullable();
            $table->boolean('membership_required')->default(false);
            $table->boolean('professional_id_required')->default(false);
            $table->integer('max_capacity')->nullable();
            $table->timestamp('registration_start')->nullable();
            $table->timestamp('registration_end')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_statuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('industries', function (Blueprint $table): void {
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

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('registration_category_id')->nullable();
            $table->unsignedBigInteger('registration_status_id')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->string('company_name')->nullable();
            $table->unsignedBigInteger('industry_id')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('membership_id')->nullable();
            $table->string('professional_student_id')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('PKR');
            $table->string('payment_status', 32)->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->string('verification_code')->nullable();
            $table->boolean('code_verified')->default(false);
            $table->string('badge_number')->nullable();
            $table->longText('qr_code')->nullable();
            $table->boolean('badge_printed')->default(false);
            $table->boolean('checked_in')->default(false);
            $table->string('registration_source')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
            $table->string('otp_hash', 255)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_last_sent_at')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    private function withDraftCookie(string $token): self
    {
        return $this->withCookie(RegistrationDraftService::COOKIE_NAME, $token);
    }

    /**
     * @return array{0: RegistrationDraft, 1: string}
     */
    private function startDraft(string $email = 'ada@example.com'): array
    {
        $drafts = app(RegistrationDraftService::class);
        [$draft, $token] = $drafts->start($this->event, $this->eventUrl, $email);
        $drafts->advanceTo($draft, RegistrationWizardStep::Information);

        return [$draft->fresh(), $token];
    }

    private function regFor(RegistrationDraft $draft): string
    {
        return app(RegistrationDraftService::class)->encodeUrlKey($draft);
    }

    private function regStep(string $step, RegistrationDraft $draft, ?string $action = null): string
    {
        $name = 'online.registration.reg.step.'.$step.($action ? '.'.$action : '');

        return route($name, ['slug' => $this->slug, 'reg' => $this->regFor($draft)]);
    }

    #[Test]
    public function entry_redirects_to_email_step_and_unknown_slug_is_404(): void
    {
        $this->get('/online/'.$this->slug)
            ->assertRedirect(route('online.registration.step.email', $this->slug));

        $this->get('/online/missing-slug')->assertNotFound();
    }

    #[Test]
    public function event_url_can_show_selected_sponsors_partners_and_sanitized_custom_html(): void
    {
        $sponsor = Sponsor::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Example Sponsor',
            'is_active' => true,
            'visible_online' => true,
            'sort_order' => 1,
        ]);
        $partner = Partner::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Example Partner',
            'is_active' => true,
            'visible_online' => true,
            'sort_order' => 1,
        ]);

        $admin = $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@example.com',
            'event_id' => 1,
            'org_id' => 1,
        ]);

        $admin->get(route('admin.event-urls.edit', $this->eventUrl))
            ->assertOk()
            ->assertSee('data-testid="event-url-sponsors"', false)
            ->assertSee('data-testid="event-url-partners"', false)
            ->assertSee('data-testid="event-url-custom-html"', false);

        $admin->put(route('admin.event-urls.update', $this->eventUrl), [
            'name' => $this->eventUrl->name,
            'slug' => $this->eventUrl->slug,
            'type' => 'online',
            'is_active' => '1',
            'enabled_categories' => [$this->freeCategory->id, $this->paidCategory->id],
            'sponsor_ids' => [$sponsor->id],
            'partner_ids' => [$partner->id],
            'custom_html' => '<section><h2>Travel notes</h2><script>alert(1)</script><a href="javascript:alert(2)" onclick="alert(3)">Read more</a></section>',
        ])->assertRedirect(route('admin.event-urls.index'));

        $this->eventUrl->refresh();
        $this->assertStringContainsString('Travel notes', (string) $this->eventUrl->custom_html);
        $this->assertStringNotContainsString('<script', (string) $this->eventUrl->custom_html);
        $this->assertStringNotContainsString('onclick', (string) $this->eventUrl->custom_html);
        $this->assertStringNotContainsString('javascript:', (string) $this->eventUrl->custom_html);
        $this->assertTrue($this->eventUrl->sponsors->contains($sponsor));
        $this->assertTrue($this->eventUrl->partners->contains($partner));

        $this->get('/online/'.$this->slug.'/step/email')
            ->assertOk()
            ->assertSee('data-testid="event-url-linked-content"', false)
            ->assertSee('Travel notes')
            ->assertSee('Example Sponsor')
            ->assertSee('Example Partner')
            ->assertDontSee('alert(1)');
    }

    #[Test]
    public function wizard_displays_event_details_and_contact_footer(): void
    {
        $this->get('/online/'.$this->slug.'/step/email')
            ->assertOk()
            ->assertSee('data-testid="event-details"', false)
            ->assertSee('Expo Centre')
            ->assertSee('Asia/Karachi')
            ->assertSee('data-testid="registration-contact-footer"', false)
            ->assertSee('events@example.com')
            ->assertSee('Our team is available for registration support.');
    }

    #[Test]
    public function start_new_registration_clears_resume_cookie_and_returns_to_email(): void
    {
        [, $token] = $this->startDraft();

        $this->withDraftCookie($token)
            ->get('/online/'.$this->slug.'/new')
            ->assertRedirect(route('online.registration.step.email', $this->slug))
            ->assertCookieExpired(RegistrationDraftService::COOKIE_NAME);
    }

    #[Test]
    public function email_step_creates_draft_sets_cookie_and_advances_without_verification(): void
    {
        $response = $this->post('/online/'.$this->slug.'/step/email', [
            'email' => 'Ada@Example.COM',
        ]);

        $draft = RegistrationDraft::query()->first();
        $this->assertNotNull($draft);
        $reg = app(RegistrationDraftService::class)->encodeUrlKey($draft);

        $response->assertRedirect(route('online.registration.reg.step.information', [
            'slug' => $this->slug,
            'reg' => $reg,
        ]));
        $response->assertCookie(RegistrationDraftService::COOKIE_NAME);

        $this->assertSame('ada@example.com', $draft->email);
        $this->assertSame(RegistrationWizardStep::Information, $draft->current_step);
        $this->assertNotNull($draft->email_verified_at);
    }

    #[Test]
    public function information_step_is_refresh_safe_via_encrypted_reg_without_cookie(): void
    {
        [$draft, $token] = $this->startDraft();
        $reg = app(RegistrationDraftService::class)->encodeUrlKey($draft);

        $this->get('/online/'.$this->slug.'/'.$reg.'/step/information')
            ->assertOk()
            ->assertSee('data-testid="wizard-information-form"', false);

        $this->get('/online/'.$this->slug.'/'.$reg.'/step/category')
            ->assertRedirect($this->regStep('email', $draft));

        $this->withCookie(RegistrationDraftService::COOKIE_NAME, 'missing-token')
            ->get('/online/'.$this->slug.'/step/information')
            ->assertRedirect(route('online.registration.step.email', $this->slug));
    }

    #[Test]
    public function wizard_persists_information_and_only_shows_url_enabled_categories(): void
    {
        [$draft, $token] = $this->startDraft();

        $this->withDraftCookie($token)
            ->post($this->regStep('information', $draft, 'store'), [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'phone' => '+971500000000',
                'job_title' => 'Engineer',
                'company_name' => 'Analytical Engines',
                'industry_id' => $this->industry->id,
            ])
            ->assertRedirect($this->regStep('category', $draft));

        $response = $this->get($this->regStep('category', $draft));

        $response->assertOk();
        $response->assertSee('Free Pass');
        $response->assertSee('Paid Pass');
        $response->assertDontSee('Hidden Pass');
    }

    #[Test]
    public function resume_link_restores_progress_via_token(): void
    {
        [$draft, $token] = $this->startDraft('resume@example.com');
        app(RegistrationDraftService::class)->savePayload($draft, [
            'first_name' => 'Res',
            'last_name' => 'Ume',
        ], RegistrationWizardStep::Category);

        $this->get('/online/'.$this->slug.'/resume/'.$token)
            ->assertRedirect($this->regStep('category', $draft))
            ->assertCookie(RegistrationDraftService::COOKIE_NAME);
    }

    #[Test]
    public function information_step_shows_saved_profile_picture(): void
    {
        [$draft, $token] = $this->startDraft('photo@example.com');
        $path = 'registrations/drafts/'.$draft->public_id.'/saved-photo.jpg';

        app(RegistrationDraftService::class)->savePayload($draft, [
            'first_name' => 'Photo',
            'last_name' => 'User',
            'phone' => '+971500000001',
            'company_name' => 'Photo Co',
            'industry_id' => $this->industry->id,
            'profile_picture' => $path,
        ], RegistrationWizardStep::Category);

        $response = $this->withDraftCookie($token)
            ->get($this->regStep('information', $draft));

        $response->assertOk();
        $response->assertSee('storage/'.$path, false);
        $response->assertSee('id="preview"', false);
        $response->assertDontSee('id="preview" class="hidden"', false);
    }

    #[Test]
    public function otp_verification_is_required_when_enabled_and_accepts_valid_code(): void
    {
        $this->event->forceFill(['email_verification_required' => true])->save();
        app()->instance('current.event', $this->event->fresh());

        $provider = Mockery::mock(ProviderManager::class);
        $provider->shouldReceive('getProvider')->andReturn(new class {
            public function send(...$args): string
            {
                return 'ok';
            }
        });
        $this->app->instance(ProviderManager::class, $provider);

        $start = $this->post('/online/'.$this->slug.'/step/email', [
            'email' => 'verify@example.com',
        ]);

        $draft = RegistrationDraft::query()->firstOrFail();
        $start->assertRedirect($this->regStep('email', $draft));
        $token = $start->getCookie(RegistrationDraftService::COOKIE_NAME)?->getValue();
        $this->assertNotEmpty($token);
        $this->assertNull($draft->email_verified_at);

        $plainOtp = '123456';
        $draft->forceFill([
            'otp_hash' => Hash::make($plainOtp),
            'otp_expires_at' => now()->addMinutes(10),
            'otp_attempts' => 0,
            'otp_last_sent_at' => now()->subMinutes(2),
        ])->save();

        $this->get($this->regStep('information', $draft))
            ->assertRedirect($this->regStep('email', $draft));

        $this->post($this->regStep('email', $draft, 'verify'), ['otp' => $plainOtp])
            ->assertRedirect($this->regStep('information', $draft));

        $this->assertNotNull($draft->fresh()->email_verified_at);
    }

    #[Test]
    public function free_registration_completes_as_paid_and_is_idempotent(): void
    {
        [$draft, $token] = $this->startDraft('free@example.com');
        $drafts = app(RegistrationDraftService::class);

        $drafts->savePayload($draft, [
            'first_name' => 'Free',
            'last_name' => 'User',
            'phone' => '123',
            'company_name' => 'Co',
            'industry_id' => $this->industry->id,
            'registration_category_id' => $this->freeCategory->id,
            'pricing' => [
                'base_price' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'currency' => 'PKR',
            ],
            'terms_accepted' => true,
        ], RegistrationWizardStep::Confirmation);

        $first = $this->withDraftCookie($token)
            ->post('/online/'.$this->slug.'/step/confirmation', [
                'terms_accepted' => '1',
            ]);

        $registration = Registration::query()->firstOrFail();
        $first->assertRedirect(route('registration.confirmation', $registration->hash));
        $this->assertSame('paid', $registration->payment_status);
        $this->assertEquals(0, (float) $registration->total_amount);

        $this->get(route('registration.confirmation', $registration->hash))
            ->assertOk()
            ->assertSee('Registration Confirmed!')
            ->assertDontSee('data-testid="payment-pending-alert"', false)
            ->assertSee('data-testid="start-new-registration"', false)
            ->assertSee(route('online.registration.new', $this->slug), false)
            ->assertSee('data-testid="registration-contact-footer"', false);

        $draftId = $draft->id;
        $second = $this->withDraftCookie($token)
            ->post('/online/'.$this->slug.'/step/confirmation', [
                'terms_accepted' => '1',
            ]);

        $second->assertRedirect(route('online.registration.step.email', $this->slug));
        $this->assertSame(1, Registration::query()->count());
        $this->assertTrue(RegistrationDraft::query()->findOrFail($draftId)->isCompleted());
    }

    #[Test]
    public function paid_registration_completes_with_pending_payment_status(): void
    {
        [$draft, $token] = $this->startDraft('paid@example.com');
        app(RegistrationDraftService::class)->savePayload($draft, [
            'first_name' => 'Paid',
            'last_name' => 'User',
            'phone' => '123',
            'company_name' => 'Co',
            'industry_id' => $this->industry->id,
            'registration_category_id' => $this->paidCategory->id,
            'pricing' => [
                'base_price' => 1000,
                'tax_amount' => 50,
                'total_amount' => 1050,
                'currency' => 'PKR',
            ],
            'terms_accepted' => true,
        ], RegistrationWizardStep::Confirmation);

        $this->withDraftCookie($token)
            ->post('/online/'.$this->slug.'/step/confirmation', [
                'terms_accepted' => '1',
            ])
            ->assertRedirect();

        $registration = Registration::query()->firstOrFail();
        $this->assertSame('pending', $registration->payment_status);
        $this->assertEquals(1050.0, (float) $registration->total_amount);

        $this->get(route('registration.confirmation', $registration->hash))
            ->assertOk()
            ->assertSee('Payment Pending')
            ->assertSee('Your registration was received, but your attendance is not confirmed yet.')
            ->assertSee('Complete payment to confirm your registration')
            ->assertSee('1,050.00 PKR')
            ->assertSee('data-testid="payment-pending-alert"', false)
            ->assertDontSee('Registration Confirmed!');
    }

    #[Test]
    public function drafts_are_isolated_across_events(): void
    {
        [$draft, $token] = $this->startDraft('iso@example.com');

        $otherEvent = Event::query()->create([
            'organization_id' => 2,
            'title' => 'Other',
            'registration_form_active' => true,
            'email_verification_required' => false,
            'currency' => 'AED',
        ]);

        EventUrl::query()->create([
            'event_id' => $otherEvent->id,
            'organization_id' => 2,
            'slug' => 'other-event',
            'type' => 'online',
            'is_active' => true,
        ]);

        config(['event.event_id' => $otherEvent->id, 'event.org_id' => 2]);
        app()->instance('current.event', $otherEvent);

        $this->withDraftCookie($token)
            ->get('/online/other-event/step/information')
            ->assertRedirect(route('online.registration.step.email', 'other-event'));

        $this->assertNull(
            app(RegistrationDraftService::class)->findByPlainToken($token, $otherEvent)
        );
        $this->assertTrue($draft->is($draft->fresh()));
    }

    #[Test]
    public function tampered_category_outside_url_enabled_list_is_rejected(): void
    {
        [$draft, $token] = $this->startDraft();
        app(RegistrationDraftService::class)->savePayload($draft, [
            'first_name' => 'A',
            'last_name' => 'B',
            'phone' => '1',
            'company_name' => 'C',
            'industry_id' => $this->industry->id,
        ], RegistrationWizardStep::Category);

        $hidden = RegistrationCategory::query()->where('name', 'Hidden Pass')->firstOrFail();

        $this->withDraftCookie($token)
            ->from('/online/'.$this->slug.'/step/category')
            ->post('/online/'.$this->slug.'/step/category', [
                'registration_category_id' => $hidden->id,
            ])
            ->assertRedirect('/online/'.$this->slug.'/step/category')
            ->assertSessionHas('error');
    }
}
