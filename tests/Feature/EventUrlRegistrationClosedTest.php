<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventUrl;
use App\Models\RegistrationCategory;
use App\Registration\Exceptions\RegistrationUrlClosedException;
use App\Registration\Services\OnlineRegistrationContext;
use App\Services\RegistrationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventUrlRegistrationClosedTest extends TestCase
{
    private Event $event;

    private EventUrl $eventUrl;

    private string $slug = 'tech-trip';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
        ]);

        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('event_urls');
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

        Schema::create('event_url_sponsor', function (Blueprint $table): void {
            $table->unsignedBigInteger('event_url_id');
            $table->unsignedBigInteger('sponsor_id');
        });

        Schema::create('event_url_partner', function (Blueprint $table): void {
            $table->unsignedBigInteger('event_url_id');
            $table->unsignedBigInteger('partner_id');
        });

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('title')->nullable();
            $table->boolean('registration_form_active')->default(true);
            $table->timestamp('online_reg_close')->nullable();
            $table->text('closed_message')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('visible')->default(true);
            $table->integer('sort_order')->default(0);
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
            $table->timestamp('expires_at')->nullable();
            $table->text('registration_closed_message')->nullable();
            $table->json('enabled_categories')->nullable();
            $table->boolean('allow_reprint')->default(false);
            $table->boolean('allow_print_from_photo')->default(false);
            $table->boolean('enable_barcode_scanner')->default(true);
            $table->boolean('enable_manual_input')->default(true);
            $table->text('description')->nullable();
            $table->longText('custom_html')->nullable();
            $table->timestamps();
        });

        $this->event = Event::query()->create([
            'organization_id' => 1,
            'title' => 'Tech Trip',
            'registration_form_active' => true,
        ]);

        app()->instance('current.event', $this->event);

        RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Standard',
            'price' => 0,
            'is_active' => true,
            'visible' => true,
        ]);

        $this->eventUrl = EventUrl::query()->create([
            'event_id' => 1,
            'organization_id' => 1,
            'name' => 'Tech Trip',
            'slug' => $this->slug,
            'type' => 'online',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('event_url_partner');
        Schema::dropIfExists('event_url_sponsor');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('event_urls');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    #[Test]
    public function expired_url_shows_registration_closed_message_instead_of_404(): void
    {
        $this->eventUrl->update([
            'expires_at' => now()->subHour(),
            'registration_closed_message' => 'Early bird registration has ended. See you at the event!',
        ]);

        $this->get(route('online.registration', ['slug' => $this->slug]))
            ->assertOk()
            ->assertSee('Registration Closed')
            ->assertSee('Early bird registration has ended. See you at the event!')
            ->assertDontSee('404');
    }

    #[Test]
    public function inactive_url_shows_registration_closed_message_instead_of_404(): void
    {
        $this->eventUrl->update([
            'is_active' => false,
            'registration_closed_message' => 'This registration link is no longer available.',
        ]);

        $this->get(route('online.registration', ['slug' => $this->slug]))
            ->assertOk()
            ->assertSee('This registration link is no longer available.');
    }

    #[Test]
    public function active_non_expired_url_still_loads_registration_flow(): void
    {
        $this->eventUrl->update([
            'expires_at' => now()->addDay(),
        ]);

        $this->get(route('online.registration', ['slug' => $this->slug]))
            ->assertRedirect(route('online.registration.step.email', ['slug' => $this->slug]));
    }

    #[Test]
    public function admin_can_save_expiry_and_closed_message(): void
    {
        session([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@example.com',
            'event_id' => 1,
            'org_id' => 1,
        ]);

        $expiresAt = now()->addWeek()->format('Y-m-d\TH:i');

        $this->put(route('admin.event-urls.update', $this->eventUrl), [
            'name' => $this->eventUrl->name,
            'slug' => $this->eventUrl->slug,
            'type' => 'online',
            'is_active' => '1',
            'expires_at' => $expiresAt,
            'registration_closed_message' => 'Registrations closed for this campaign.',
        ])->assertRedirect(route('admin.event-urls.index'));

        $this->eventUrl->refresh();

        $this->assertNotNull($this->eventUrl->expires_at);
        $this->assertSame('Registrations closed for this campaign.', $this->eventUrl->registration_closed_message);
    }

    #[Test]
    public function resolve_event_url_throws_closed_exception_for_expired_url(): void
    {
        $this->eventUrl->update(['expires_at' => now()->subMinute()]);

        $context = new OnlineRegistrationContext(app(RegistrationService::class));

        $this->expectException(RegistrationUrlClosedException::class);

        $context->resolveEventUrl($this->slug, $this->event);
    }

    #[Test]
    public function unknown_slug_still_returns_404(): void
    {
        $this->get(route('online.registration', ['slug' => 'does-not-exist']))
            ->assertNotFound();
    }
}
