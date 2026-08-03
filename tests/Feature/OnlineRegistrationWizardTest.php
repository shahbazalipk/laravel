<?php

namespace Tests\Feature;

use App\Forms\Enums\FormAudience;
use App\Forms\Enums\FormConditionAction;
use App\Forms\Enums\FormConditionOperator;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormResponse;
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
use App\Services\ProviderManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithCustomFormSchema;
use Tests\TestCase;

class OnlineRegistrationWizardTest extends TestCase
{
    use InteractsWithCustomFormSchema;

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
        $this->createCustomFormTables();

        $this->event = Event::query()->create([
            'organization_id' => 1,
            'title' => 'Tech Trip',
            'event_name' => 'TechTrip 2.0',
            'seo_title' => 'TechTrip 2.0 | Register for PITF Adventure',
            'seo_description' => 'Join Pakistan IT Forum Tech Trip in Nathia Gali for tech, networking, and adventure.',
            'seo_keywords' => 'PITF, TechTrip, registration, Nathia Gali',
            'social_media_description' => 'Register for TechTrip 2.0 — adventure meets innovation in Nathia Gali.',
            'social_media_share_banner' => 'event/social/techtrip-share.jpg',
            'logo' => 'event/logos/techtrip.png',
            'twitter_mention' => '@pitftrip',
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
            'instructions_text' => 'Bring your badge to the gate.',
            'instruction_description' => 'Arrive 30 minutes early for check-in.',
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
        $this->dropCustomFormTables();
        Schema::dropIfExists('registration_drafts');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('promo_code_emails');
        Schema::dropIfExists('promo_codes');
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
            $table->string('event_name')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->text('social_media_description')->nullable();
            $table->string('social_media_share_banner')->nullable();
            $table->string('social_image')->nullable();
            $table->string('header_image')->nullable();
            $table->string('logo')->nullable();
            $table->string('twitter_mention')->nullable();
            $table->timestamps();
        });

        Schema::create('event_urls', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug');
            $table->string('type')->default('online');
            $table->string('registration_format', 32)->default('multi_step');
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
            $table->text('description')->nullable();
            $table->text('instructions_text')->nullable();
            $table->text('instruction_description')->nullable();
            $table->boolean('need_professional_student_id')->default(false);
            $table->text('professional_student_id_message')->nullable();
            $table->boolean('need_membership_id')->default(false);
            $table->boolean('needs_password')->default(false);
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

        Schema::create('promo_codes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('code');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 12, 2);
            $table->string('currency', 10)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_total_uses')->nullable();
            $table->unsignedInteger('max_uses_per_email')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('restrict_to_email_list')->default(false);
            $table->timestamps();
        });

        Schema::create('promo_code_emails', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('promo_code_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('email');
            $table->timestamps();
            $table->unique(['promo_code_id', 'email']);
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
            $table->unsignedBigInteger('promo_code_id')->nullable();
            $table->string('promo_code', 50)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
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
        $drafts->advanceTo($draft, RegistrationWizardStep::Category);

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
    public function email_step_includes_complete_seo_and_social_share_meta(): void
    {
        $response = $this->get('/online/'.$this->slug.'/step/email');

        $response->assertOk();
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('Register for TechTrip 2.0 — adventure meets innovation in Nathia Gali.', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('TechTrip 2.0 | Register for PITF Adventure', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('storage/event/social/techtrip-share.jpg', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('summary_large_image', false);
        $response->assertSee('name="twitter:site"', false);
        $response->assertSee('@pitftrip', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee(route('online.registration.step.email', $this->slug), false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Event"', false);
        $response->assertSee('"@type":"WebPage"', false);
        $response->assertSee('content="index, follow', false);
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

        $response->assertRedirect(route('online.registration.reg.step.category', [
            'slug' => $this->slug,
            'reg' => $reg,
        ]));
        $response->assertCookie(RegistrationDraftService::COOKIE_NAME);

        $this->assertSame('ada@example.com', $draft->email);
        $this->assertSame(RegistrationWizardStep::Category, $draft->current_step);
        $this->assertNotNull($draft->email_verified_at);
    }

    #[Test]
    public function category_step_is_refresh_safe_and_information_is_locked_until_category_is_saved(): void
    {
        [$draft, $token] = $this->startDraft();
        $reg = app(RegistrationDraftService::class)->encodeUrlKey($draft);

        $this->get('/online/'.$this->slug.'/'.$reg.'/step/category')
            ->assertOk()
            ->assertSee('data-testid="wizard-category-form"', false);

        $this->get('/online/'.$this->slug.'/'.$reg.'/step/information')
            ->assertRedirect($this->regStep('category', $draft));

        $this->withCookie(RegistrationDraftService::COOKIE_NAME, 'missing-token')
            ->get('/online/'.$this->slug.'/step/category')
            ->assertRedirect(route('online.registration.step.email', $this->slug));
    }

    #[Test]
    public function wizard_selects_category_before_persisting_information(): void
    {
        [$draft, $token] = $this->startDraft();

        $response = $this->get($this->regStep('category', $draft));
        $response->assertOk();
        $response->assertSee('Free Pass');
        $response->assertSee('Paid Pass');
        $response->assertDontSee('Hidden Pass');

        $this->withDraftCookie($token)
            ->post($this->regStep('category', $draft, 'store'), [
                'registration_category_id' => $this->freeCategory->id,
            ])
            ->assertRedirect($this->regStep('information', $draft));

        $this->withDraftCookie($token)
            ->post($this->regStep('information', $draft->fresh(), 'store'), [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'phone' => '+971500000000',
                'job_title' => 'Engineer',
                'company_name' => 'Analytical Engines',
                'industry_id' => $this->industry->id,
            ])
            ->assertRedirect($this->regStep('confirmation', $draft));

        $this->assertSame(
            $this->freeCategory->id,
            $draft->fresh()->payload['registration_category_id']
        );

        $this->get($this->regStep('confirmation', $draft->fresh()))
            ->assertOk()
            ->assertSee($this->regStep('information', $draft), false);
    }

    #[Test]
    public function registration_custom_questions_store_conditional_answers_uploads_and_promote_on_completion(): void
    {
        Storage::fake('local');
        $form = CustomForm::query()->create([
            'name' => 'Travel requirements',
            'slug' => 'travel-requirements',
            'audience' => FormAudience::Registration,
            'is_active' => true,
        ]);
        $attending = $form->questions()->create([
            'key' => 'attending_dinner',
            'label' => 'Will you attend dinner?',
            'help_text' => '<p>Review the <strong>dinner details</strong>.</p><script>alert("xss")</script>',
            'type' => FormQuestionType::Radio,
            'is_required' => true,
            'sort_order' => 0,
            'is_active' => true,
        ]);
        foreach (['yes' => 'Yes', 'no' => 'No'] as $value => $label) {
            $attending->options()->create([
                'value' => $value,
                'label' => $label,
                'sort_order' => $value === 'yes' ? 0 : 1,
                'is_active' => true,
            ]);
        }
        $hiddenText = $form->questions()->create([
            'key' => 'decline_reason',
            'label' => 'Why can you not attend?',
            'type' => FormQuestionType::Text,
            'is_required' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $upload = $form->questions()->create([
            'key' => 'meal_document',
            'label' => 'Meal document',
            'type' => FormQuestionType::Upload,
            'is_required' => true,
            'sort_order' => 2,
            'is_active' => true,
            'validation' => ['mimes' => 'pdf', 'max_kb' => 100],
        ]);
        $form->conditions()->create([
            'source_question_id' => $attending->id,
            'target_question_id' => $hiddenText->id,
            'operator' => FormConditionOperator::Equals,
            'compare_value' => 'no',
            'action' => FormConditionAction::Show,
            'sort_order' => 0,
            'is_active' => true,
        ]);
        $form->conditions()->create([
            'source_question_id' => $attending->id,
            'target_question_id' => $upload->id,
            'operator' => FormConditionOperator::Equals,
            'compare_value' => 'yes',
            'action' => FormConditionAction::Show,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        foreach ([
            ['Exhibitor questions', FormAudience::Exhibitor, true],
            ['Group questions', FormAudience::Group, true],
        ] as [$name, $audience, $active]) {
            CustomForm::query()->create([
                'name' => $name,
                'slug' => str($name)->slug()->toString(),
                'audience' => $audience,
                'is_active' => $active,
            ]);
        }

        [$draft, $token] = $this->startDraft('questions@example.com');
        $draft = app(RegistrationDraftService::class)
            ->advanceTo($draft, RegistrationWizardStep::Information);
        $this->withDraftCookie($token)
            ->get($this->regStep('information', $draft))
            ->assertOk()
            ->assertSee('Travel requirements')
            ->assertSee('data-custom-question="attending_dinner"', false)
            ->assertSee('<strong>dinner details</strong>', false)
            ->assertDontSee('alert("xss")', false)
            ->assertDontSee('Exhibitor questions')
            ->assertDontSee('Group questions');

        $this->withDraftCookie($token)
            ->post($this->regStep('information', $draft, 'store'), [
                'first_name' => 'Custom',
                'last_name' => 'Questions',
                'phone' => '+971500000099',
                'job_title' => 'Engineer',
                'company_name' => 'Forms Co',
                'industry_id' => $this->industry->id,
                'custom_forms' => [
                    $form->public_id => [
                        'attending_dinner' => 'yes',
                        'meal_document' => UploadedFile::fake()->create(
                            'meal-document.pdf',
                            20,
                            'application/pdf'
                        ),
                    ],
                ],
            ])
            ->assertRedirect($this->regStep('confirmation', $draft))
            ->assertSessionHasNoErrors();

        $customResponse = CustomFormResponse::query()
            ->where('custom_form_id', $form->id)
            ->with('answers.files')
            ->firstOrFail();
        $this->assertSame($draft->getMorphClass(), $customResponse->respondent_type);
        $this->assertSame($draft->id, $customResponse->respondent_id);
        $this->assertSame(
            ['attending_dinner', 'meal_document'],
            $customResponse->answers->pluck('question_key')->sort()->values()->all()
        );
        $this->assertNull($customResponse->answers->firstWhere('question_key', 'decline_reason'));
        $storedFile = $customResponse->answers
            ->firstWhere('question_key', 'meal_document')
            ?->files
            ->first();
        $this->assertNotNull($storedFile);
        Storage::disk('local')->assertExists($storedFile->path);

        $draft = $draft->fresh();
        app(RegistrationDraftService::class)->savePayload($draft, [
            ...($draft->payload ?? []),
            'registration_category_id' => $this->freeCategory->id,
            'pricing' => [
                'base_price' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'currency' => 'PKR',
            ],
            'terms_accepted' => true,
        ], RegistrationWizardStep::Confirmation);

        $this->withDraftCookie($token)
            ->post('/online/'.$this->slug.'/step/confirmation', ['terms_accepted' => '1'])
            ->assertRedirect();

        $registration = Registration::query()->where('email', 'questions@example.com')->firstOrFail();
        $customResponse->refresh();
        $this->assertSame($registration->getMorphClass(), $customResponse->respondent_type);
        $this->assertSame($registration->id, $customResponse->respondent_id);
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
        $draft = app(RegistrationDraftService::class)
            ->advanceTo($draft, RegistrationWizardStep::Information);
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
        $provider->shouldReceive('getProvider')->andReturn(new class
        {
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
            ->assertRedirect($this->regStep('category', $draft));

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
            ->assertSee('Payment Verification Pending')
            ->assertSee('We received your registration and payment screenshot.')
            ->assertSee('We’ll verify your payment and update you')
            ->assertSee('No further action is required right now.')
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

    #[Test]
    public function single_page_url_entry_redirects_to_single_form(): void
    {
        $this->eventUrl->update([
            'registration_format' => \App\Registration\Enums\RegistrationFormat::SinglePage,
        ]);

        $this->get('/online/'.$this->slug)
            ->assertRedirect(route('online.registration.single', $this->slug));

        $this->get(route('online.registration.single', $this->slug))
            ->assertOk()
            ->assertSee('data-testid="single-page-form"', false)
            ->assertSee('data-testid="single-page-category-list"', false)
            ->assertSee('Free Pass')
            ->assertSee('Bring your badge to the gate.')
            ->assertSee('Arrive 30 minutes early for check-in.')
            ->assertSee('data-testid="category-instructions-text-'.$this->freeCategory->id.'"', false)
            ->assertSee('data-testid="category-instruction-description-'.$this->freeCategory->id.'"', false)
            ->assertDontSee('data-testid="wizard-progress"', false);
    }

    #[Test]
    public function multi_step_category_step_shows_instruction_text_and_description(): void
    {
        [$draft] = $this->startDraft('instructions@example.com');
        $reg = app(RegistrationDraftService::class)->encodeUrlKey($draft);

        $this->get('/online/'.$this->slug.'/'.$reg.'/step/category')
            ->assertOk()
            ->assertSee('Bring your badge to the gate.')
            ->assertSee('Arrive 30 minutes early for check-in.')
            ->assertSee('data-testid="category-instructions-text-'.$this->freeCategory->id.'"', false)
            ->assertSee('data-testid="category-instruction-description-'.$this->freeCategory->id.'"', false);
    }

    #[Test]
    public function multi_step_url_cannot_open_single_page_form_route(): void
    {
        $this->get(route('online.registration.single', $this->slug))->assertNotFound();
    }

    #[Test]
    public function single_page_form_completes_free_registration_in_one_submit(): void
    {
        $this->eventUrl->update([
            'registration_format' => \App\Registration\Enums\RegistrationFormat::SinglePage,
        ]);

        RegistrationStatus::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Confirmed',
            'slug' => 'confirmed',
            'is_active' => true,
        ]);

        $response = $this->post(route('online.registration.single.store', $this->slug), [
            'email' => 'single@example.com',
            'registration_category_id' => $this->freeCategory->id,
            'first_name' => 'Single',
            'last_name' => 'Page',
            'phone' => '+971500000099',
            'job_title' => 'Attendee',
            'company_name' => 'One Form Co',
            'industry_id' => $this->industry->id,
            'terms_accepted' => '1',
        ]);

        $registration = Registration::query()->where('email', 'single@example.com')->first();
        $this->assertNotNull($registration);
        $this->assertSame('paid', $registration->payment_status);
        $this->assertEquals(0.0, (float) $registration->total_amount);
        $response->assertRedirect(route('registration.confirmation', $registration->hash));
    }

    #[Test]
    public function multi_step_email_rejects_already_registered_email(): void
    {
        Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => $this->freeCategory->id,
            'registration_type' => 'individual',
            'registration_number' => 'REG-TAKEN1',
            'first_name' => 'Taken',
            'last_name' => 'Email',
            'email' => 'taken@example.com',
            'phone' => '+971500000001',
            'company_name' => 'Acme',
            'base_price' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'currency' => 'PKR',
            'payment_status' => 'paid',
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
        ]);

        $this->from(route('online.registration.step.email', $this->slug))
            ->post('/online/'.$this->slug.'/step/email', [
                'email' => 'Taken@Example.com',
            ])
            ->assertRedirect(route('online.registration.step.email', $this->slug))
            ->assertSessionHasErrors([
                'email' => 'This email is already registered. You cannot use the same email again.',
            ]);
    }

    #[Test]
    public function single_page_rejects_already_registered_email(): void
    {
        $this->eventUrl->update([
            'registration_format' => \App\Registration\Enums\RegistrationFormat::SinglePage,
        ]);

        Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => $this->freeCategory->id,
            'registration_type' => 'individual',
            'registration_number' => 'REG-TAKEN2',
            'first_name' => 'Taken',
            'last_name' => 'Again',
            'email' => 'taken-single@example.com',
            'phone' => '+971500000002',
            'company_name' => 'Acme',
            'base_price' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'currency' => 'PKR',
            'payment_status' => 'paid',
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
        ]);

        $this->from(route('online.registration.single', $this->slug))
            ->post(route('online.registration.single.store', $this->slug), [
                'email' => 'taken-single@example.com',
                'registration_category_id' => $this->freeCategory->id,
                'first_name' => 'New',
                'last_name' => 'Person',
                'phone' => '+971500000088',
                'job_title' => 'Attendee',
                'company_name' => 'Other Co',
                'industry_id' => $this->industry->id,
                'terms_accepted' => '1',
            ])
            ->assertRedirect(route('online.registration.single', $this->slug))
            ->assertSessionHasErrors([
                'email' => 'This email is already registered. You cannot use the same email again.',
            ]);
    }

    #[Test]
    public function single_page_resume_shows_start_fresh_and_new_clears_session(): void
    {
        $this->eventUrl->update([
            'registration_format' => \App\Registration\Enums\RegistrationFormat::SinglePage,
        ]);

        [$draft, $token] = $this->startDraft('resume-single@example.com');
        $reg = app(RegistrationDraftService::class)->encodeUrlKey($draft);

        $this->withDraftCookie($token)
            ->get(route('online.registration.single.reg', [
                'slug' => $this->slug,
                'reg' => $reg,
            ]))
            ->assertOk()
            ->assertSee('data-testid="single-page-resume-banner"', false)
            ->assertSee('data-testid="single-page-start-fresh"', false)
            ->assertSee('resume-single@example.com')
            ->assertSee(route('online.registration.new', $this->slug), false);

        $this->withDraftCookie($token)
            ->get('/online/'.$this->slug.'/new')
            ->assertRedirect(route('online.registration.single', $this->slug))
            ->assertCookieExpired(RegistrationDraftService::COOKIE_NAME);

        // Drop the cookie that withCookie() keeps on the test client.
        unset($this->defaultCookies[RegistrationDraftService::COOKIE_NAME]);

        $this->get(route('online.registration.single', $this->slug))
            ->assertOk()
            ->assertDontSee('data-testid="single-page-resume-banner"', false)
            ->assertSee('data-testid="single-page-form"', false);
    }

    #[Test]
    public function admin_can_set_registration_format_on_online_urls(): void
    {
        $admin = $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@example.com',
            'event_id' => 1,
            'org_id' => 1,
        ]);

        $admin->put(route('admin.event-urls.update', $this->eventUrl), [
            'name' => $this->eventUrl->name,
            'slug' => $this->eventUrl->slug,
            'type' => 'online',
            'registration_format' => 'single_page',
            'is_active' => '1',
            'enabled_categories' => [$this->freeCategory->id, $this->paidCategory->id],
        ])->assertRedirect(route('admin.event-urls.index'));

        $this->assertTrue($this->eventUrl->fresh()->usesSinglePageRegistration());

        config([
            'modules.finance.enabled' => false,
            'modules.projects.enabled' => false,
        ]);

        $admin->get(route('admin.event-urls.edit', $this->eventUrl))
            ->assertOk()
            ->assertSee('data-testid="registration-format-select"', false)
            ->assertSee('value="single_page"', false);
    }

    #[Test]
    public function single_page_form_shows_promo_code_field(): void
    {
        $this->eventUrl->update([
            'registration_format' => \App\Registration\Enums\RegistrationFormat::SinglePage,
        ]);

        $this->get(route('online.registration.single', $this->slug))
            ->assertOk()
            ->assertSee('data-testid="single-page-promo-code"', false)
            ->assertSee('data-testid="single-page-promo-apply"', false);
    }

    #[Test]
    public function wizard_can_apply_and_remove_promo_on_confirmation(): void
    {
        [$draft, $token] = $this->startDraft('alumni@example.com');

        $this->withDraftCookie($token)
            ->post($this->regStep('category', $draft, 'store'), [
                'registration_category_id' => $this->paidCategory->id,
            ])
            ->assertRedirect($this->regStep('information', $draft));

        $this->withDraftCookie($token)
            ->post($this->regStep('information', $draft->fresh(), 'store'), [
                'first_name' => 'Alumni',
                'last_name' => 'Member',
                'phone' => '+971500000088',
                'job_title' => 'Attendee',
                'company_name' => 'Alumni Co',
                'industry_id' => $this->industry->id,
            ])
            ->assertRedirect($this->regStep('confirmation', $draft));

        $promo = \App\Models\PromoCode::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'RETURNING26',
            'discount_type' => \App\Enums\PromoDiscountType::Percentage,
            'discount_value' => 10,
            'max_uses_per_email' => 1,
            'is_active' => true,
            'restrict_to_email_list' => true,
        ]);
        \App\Models\PromoCodeEmail::query()->create([
            'promo_code_id' => $promo->id,
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'alumni@example.com',
        ]);

        $draft = $draft->fresh();

        $this->withDraftCookie($token)
            ->get($this->regStep('confirmation', $draft))
            ->assertOk()
            ->assertSee('data-testid="wizard-promo-apply"', false)
            ->assertSee('1,050.00', false);

        $this->withDraftCookie($token)
            ->from($this->regStep('confirmation', $draft))
            ->post($this->regStep('confirmation', $draft, 'promo.apply'), [
                'promo_code' => 'returning26',
            ])
            ->assertRedirect($this->regStep('confirmation', $draft))
            ->assertSessionHas('success');

        $this->assertSame('RETURNING26', $draft->fresh()->payload['promo_code'] ?? null);

        $this->withDraftCookie($token)
            ->get($this->regStep('confirmation', $draft->fresh()))
            ->assertOk()
            ->assertSee('data-testid="wizard-promo-applied"', false)
            ->assertSee('data-testid="wizard-promo-remove"', false)
            ->assertSee('945.00', false);

        $this->withDraftCookie($token)
            ->from($this->regStep('confirmation', $draft->fresh()))
            ->delete($this->regStep('confirmation', $draft->fresh(), 'promo.remove'))
            ->assertRedirect($this->regStep('confirmation', $draft))
            ->assertSessionHas('success');

        $this->assertEmpty($draft->fresh()->payload['promo_code'] ?? null);

        $this->withDraftCookie($token)
            ->get($this->regStep('confirmation', $draft->fresh()))
            ->assertOk()
            ->assertSee('data-testid="wizard-promo-apply"', false)
            ->assertSee('1,050.00', false)
            ->assertDontSee('data-testid="wizard-promo-applied"', false);
    }

    #[Test]
    public function single_page_promo_preview_returns_discounted_pricing_and_rejects_invalid(): void
    {
        $this->eventUrl->update([
            'registration_format' => \App\Registration\Enums\RegistrationFormat::SinglePage,
        ]);

        $promo = \App\Models\PromoCode::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'RETURNING26',
            'discount_type' => \App\Enums\PromoDiscountType::Percentage,
            'discount_value' => 10,
            'is_active' => true,
            'restrict_to_email_list' => true,
        ]);
        \App\Models\PromoCodeEmail::query()->create([
            'promo_code_id' => $promo->id,
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'alumni@example.com',
        ]);

        $this->postJson(route('online.registration.single.promo.preview', $this->slug), [
            'email' => 'alumni@example.com',
            'registration_category_id' => $this->paidCategory->id,
            'promo_code' => 'returning26',
        ])
            ->assertOk()
            ->assertJsonPath('pricing.promo_code', 'RETURNING26')
            ->assertJsonPath('pricing.discount_amount', 100)
            ->assertJsonPath('pricing.total_amount', 945);

        $this->postJson(route('online.registration.single.promo.preview', $this->slug), [
            'email' => 'outsider@example.com',
            'registration_category_id' => $this->paidCategory->id,
            'promo_code' => 'RETURNING26',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['promo_code']]);
    }

    #[Test]
    public function single_page_applies_email_restricted_promo_discount(): void
    {
        $this->eventUrl->update([
            'registration_format' => \App\Registration\Enums\RegistrationFormat::SinglePage,
        ]);

        RegistrationStatus::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Confirmed',
            'slug' => 'confirmed',
            'is_active' => true,
        ]);

        $promo = \App\Models\PromoCode::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'RETURNING26',
            'discount_type' => \App\Enums\PromoDiscountType::Percentage,
            'discount_value' => 10,
            'max_uses_per_email' => 1,
            'is_active' => true,
            'restrict_to_email_list' => true,
        ]);
        \App\Models\PromoCodeEmail::query()->create([
            'promo_code_id' => $promo->id,
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'alumni@example.com',
        ]);

        // Paid category is 1000 + 5% VAT = 1050; 10% off base => base 900, tax 45, total 945
        $response = $this->post(route('online.registration.single.store', $this->slug), [
            'email' => 'alumni@example.com',
            'registration_category_id' => $this->paidCategory->id,
            'first_name' => 'Alumni',
            'last_name' => 'Member',
            'phone' => '+971500000088',
            'job_title' => 'Attendee',
            'company_name' => 'Alumni Co',
            'industry_id' => $this->industry->id,
            'promo_code' => 'returning26',
            'terms_accepted' => '1',
        ]);

        $registration = Registration::query()->where('email', 'alumni@example.com')->first();
        $this->assertNotNull($registration);
        $this->assertSame('RETURNING26', $registration->promo_code);
        $this->assertEquals(100.0, (float) $registration->discount_amount);
        $this->assertEquals(900.0, (float) $registration->base_price);
        $this->assertEquals(45.0, (float) $registration->tax_amount);
        $this->assertEquals(945.0, (float) $registration->total_amount);
        $this->assertSame(1, (int) $promo->fresh()->used_count);
        $response->assertRedirect(route('registration.confirmation', $registration->hash));
    }

    #[Test]
    public function single_page_rejects_promo_for_email_not_on_allowlist(): void
    {
        $this->eventUrl->update([
            'registration_format' => \App\Registration\Enums\RegistrationFormat::SinglePage,
        ]);

        $promo = \App\Models\PromoCode::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'RETURNING26',
            'discount_type' => \App\Enums\PromoDiscountType::Percentage,
            'discount_value' => 10,
            'is_active' => true,
            'restrict_to_email_list' => true,
        ]);
        \App\Models\PromoCodeEmail::query()->create([
            'promo_code_id' => $promo->id,
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'alumni@example.com',
        ]);

        $this->from(route('online.registration.single', $this->slug))
            ->post(route('online.registration.single.store', $this->slug), [
                'email' => 'outsider@example.com',
                'registration_category_id' => $this->paidCategory->id,
                'first_name' => 'Out',
                'last_name' => 'Sider',
                'phone' => '+971500000077',
                'company_name' => 'Other Co',
                'industry_id' => $this->industry->id,
                'promo_code' => 'RETURNING26',
                'terms_accepted' => '1',
            ])
            ->assertRedirect(route('online.registration.single', $this->slug))
            ->assertSessionHasErrors('promo_code');

        $this->assertNull(Registration::query()->where('email', 'outsider@example.com')->first());
    }

    #[Test]
    public function single_page_unrestricted_promo_works_without_allowlist(): void
    {
        $this->eventUrl->update([
            'registration_format' => \App\Registration\Enums\RegistrationFormat::SinglePage,
        ]);

        RegistrationStatus::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Confirmed',
            'slug' => 'confirmed',
            'is_active' => true,
        ]);

        \App\Models\PromoCode::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'OPEN20',
            'discount_type' => \App\Enums\PromoDiscountType::Fixed,
            'discount_value' => 200,
            'currency' => 'PKR',
            'is_active' => true,
            'restrict_to_email_list' => false,
        ]);

        // 1000 - 200 = 800 base, +5% VAT = 840
        $this->post(route('online.registration.single.store', $this->slug), [
            'email' => 'anyone@example.com',
            'registration_category_id' => $this->paidCategory->id,
            'first_name' => 'Any',
            'last_name' => 'One',
            'phone' => '+971500000066',
            'company_name' => 'Any Co',
            'industry_id' => $this->industry->id,
            'promo_code' => 'OPEN20',
            'terms_accepted' => '1',
        ])->assertRedirect();

        $registration = Registration::query()->where('email', 'anyone@example.com')->first();
        $this->assertNotNull($registration);
        $this->assertEquals(200.0, (float) $registration->discount_amount);
        $this->assertEquals(800.0, (float) $registration->base_price);
        $this->assertEquals(840.0, (float) $registration->total_amount);
    }

    #[Test]
    public function promo_service_enforces_max_total_and_per_email_limits(): void
    {
        $promo = \App\Models\PromoCode::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'LIMITED',
            'discount_type' => \App\Enums\PromoDiscountType::Percentage,
            'discount_value' => 5,
            'max_uses_per_email' => 1,
            'max_total_uses' => 1,
            'used_count' => 0,
            'is_active' => true,
            'restrict_to_email_list' => false,
        ]);

        $service = app(\App\Services\PromoCodeService::class);
        $this->assertSame('LIMITED', $service->findUsable('LIMITED', $this->event, 'first@example.com')->code);

        Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => $this->paidCategory->id,
            'registration_type' => 'individual',
            'registration_number' => 'REG-LIM1',
            'badge_number' => 'BDG-LIM1',
            'first_name' => 'First',
            'last_name' => 'User',
            'email' => 'first@example.com',
            'phone' => '+971500000044',
            'company_name' => 'Limit Co',
            'base_price' => 950,
            'tax_amount' => 47.5,
            'total_amount' => 997.5,
            'currency' => 'PKR',
            'promo_code_id' => $promo->id,
            'promo_code' => 'LIMITED',
            'discount_amount' => 50,
            'payment_status' => 'pending',
            'terms_accepted' => true,
        ]);

        try {
            $service->findUsable('LIMITED', $this->event, 'first@example.com');
            $this->fail('Expected per-email limit validation failure.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('promo_code', $exception->errors());
        }

        $promo->update(['used_count' => 1]);

        try {
            $service->findUsable('LIMITED', $this->event, 'second@example.com');
            $this->fail('Expected total usage limit validation failure.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('promo_code', $exception->errors());
        }
    }
}
