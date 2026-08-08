<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventUrl;
use App\Models\EventUrlVisit;
use App\Services\EventUrlAnalyticsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventUrlAnalyticsTest extends TestCase
{
    private Event $event;

    private EventUrl $eventUrl;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
        ]);

        Schema::dropIfExists('event_url_visit_events');
        Schema::dropIfExists('event_url_visits');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('event_urls');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->boolean('registration_form_active')->default(true);
            $table->timestamps();
        });

        Schema::create('event_urls', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('organization_id');
            $table->string('name');
            $table->string('slug');
            $table->string('type')->default('online');
            $table->string('registration_format')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('enabled_categories')->nullable();
            $table->boolean('allow_reprint')->default(false);
            $table->boolean('allow_print_from_photo')->default(false);
            $table->boolean('enable_barcode_scanner')->default(false);
            $table->boolean('enable_manual_input')->default(false);
            $table->text('description')->nullable();
            $table->longText('custom_html')->nullable();
            $table->timestamps();
        });

        Schema::create('event_url_visits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('event_url_id');
            $table->uuid('visitor_uuid');
            $table->string('session_key', 64);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('browser', 64)->nullable();
            $table->string('browser_version', 32)->nullable();
            $table->string('platform', 64)->nullable();
            $table->string('device_type', 24)->nullable();
            $table->string('referrer', 1000)->nullable();
            $table->string('landing_path', 500)->nullable();
            $table->string('landing_query', 1000)->nullable();
            $table->string('utm_source', 255)->nullable();
            $table->string('utm_medium', 255)->nullable();
            $table->string('utm_campaign', 255)->nullable();
            $table->string('utm_term', 255)->nullable();
            $table->string('utm_content', 255)->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('language', 32)->nullable();
            $table->string('screen_size', 32)->nullable();
            $table->unsignedInteger('pageview_count')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('farthest_step', 64)->nullable();
            $table->string('last_step', 64)->nullable();
            $table->boolean('registered')->default(false);
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->unsignedBigInteger('registration_draft_id')->nullable();
            $table->boolean('is_bounce')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['event_url_id', 'session_key']);
        });

        Schema::create('event_url_visit_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('event_url_id');
            $table->unsignedBigInteger('event_url_visit_id');
            $table->string('event_type', 64);
            $table->string('step', 64)->nullable();
            $table->string('path', 500)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at');
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
            'name' => 'Analytics Event',
            'title' => 'Analytics Event',
            'registration_form_active' => true,
        ]);
        app()->instance('current.event', $this->event);

        $this->eventUrl = EventUrl::query()->create([
            'event_id' => 1,
            'organization_id' => 1,
            'name' => 'Tech Trip',
            'slug' => 'tech-trip',
            'type' => 'online',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('event_url_visit_events');
        Schema::dropIfExists('event_url_visits');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('event_urls');
        Schema::dropIfExists('events');
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
    public function public_tracker_records_visit_device_and_funnel_step(): void
    {
        $visitor = (string) Str::uuid();
        $session = bin2hex(random_bytes(16));

        $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1')
            ->withHeader('CF-IPCountry', 'PK')
            ->postJson(route('event-url.track', 'tech-trip'), [
                'event_type' => 'step_view',
                'step' => 'email',
                'path' => '/online/tech-trip/step/email',
                'active_seconds' => 42,
                'visitor_uuid' => $visitor,
                'session_key' => $session,
                'utm_source' => 'newsletter',
                'language' => 'en-US',
                'screen_size' => '390x844',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $visit = EventUrlVisit::query()->where('session_key', $session)->first();
        $this->assertNotNull($visit);
        $this->assertSame('email', $visit->last_step);
        $this->assertSame('Safari', $visit->browser);
        $this->assertSame('mobile', $visit->device_type);
        $this->assertSame('iOS', $visit->platform);
        $this->assertSame('PK', $visit->country_code);
        $this->assertSame('newsletter', $visit->utm_source);
        $this->assertSame(42, (int) $visit->duration_seconds);
        $this->assertSame(1, (int) $visit->pageview_count);
    }

    #[Test]
    public function admin_stats_page_shows_summary_and_visits(): void
    {
        EventUrlVisit::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'event_url_id' => $this->eventUrl->id,
            'visitor_uuid' => (string) Str::uuid(),
            'session_key' => bin2hex(random_bytes(16)),
            'ip_address' => '203.0.113.10',
            'browser' => 'Chrome',
            'platform' => 'Windows',
            'device_type' => 'desktop',
            'pageview_count' => 3,
            'duration_seconds' => 120,
            'farthest_step' => 'confirmation',
            'last_step' => 'confirmation',
            'registered' => false,
            'is_bounce' => false,
            'started_at' => now()->subHour(),
            'last_seen_at' => now(),
        ]);

        $this->actingAsAdmin()
            ->get(route('admin.event-urls.stats', $this->eventUrl))
            ->assertOk()
            ->assertSee('data-testid="event-url-stats-page"', false)
            ->assertSee('data-testid="stats-visits"', false)
            ->assertSee('data-testid="event-url-monthly"', false)
            ->assertSee('data-testid="event-url-monthly-chart"', false)
            ->assertSee('Monthly trend')
            ->assertSee('203.0.113.10')
            ->assertSee('Chrome');

        $this->actingAsAdmin()
            ->get(route('admin.event-urls.index'))
            ->assertOk()
            ->assertSee('data-testid="event-url-stats-'.$this->eventUrl->id.'"', false);
    }

    #[Test]
    public function summarize_builds_monthly_trend_buckets(): void
    {
        EventUrlVisit::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'event_url_id' => $this->eventUrl->id,
            'visitor_uuid' => (string) Str::uuid(),
            'session_key' => bin2hex(random_bytes(16)),
            'pageview_count' => 1,
            'registered' => true,
            'is_bounce' => false,
            'started_at' => now()->subMonths(2)->startOfMonth()->addDays(3),
            'last_seen_at' => now()->subMonths(2)->startOfMonth()->addDays(3),
        ]);
        EventUrlVisit::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'event_url_id' => $this->eventUrl->id,
            'visitor_uuid' => (string) Str::uuid(),
            'session_key' => bin2hex(random_bytes(16)),
            'pageview_count' => 2,
            'registered' => false,
            'is_bounce' => true,
            'started_at' => now()->startOfMonth()->addDay(),
            'last_seen_at' => now()->startOfMonth()->addDay(),
        ]);

        $summary = app(EventUrlAnalyticsService::class)->summarize(
            $this->eventUrl,
            now()->subMonths(2)->startOfMonth(),
            now()->endOfMonth(),
        );

        $this->assertArrayHasKey('monthly', $summary);
        $this->assertCount(3, $summary['monthly']);
        $this->assertSame(1, $summary['monthly'][0]['visits']);
        $this->assertSame(1, $summary['monthly'][0]['registrations']);
        $this->assertSame(0, $summary['monthly'][1]['visits']);
        $this->assertSame(1, $summary['monthly'][2]['visits']);
        $this->assertSame(0, $summary['monthly'][2]['registrations']);
    }

    #[Test]
    public function analytics_service_parses_user_agents(): void
    {
        $service = app(EventUrlAnalyticsService::class);
        $parsed = $service->parseUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        $this->assertSame('Chrome', $parsed['browser']);
        $this->assertSame('desktop', $parsed['device_type']);
        $this->assertSame('Windows', $parsed['platform']);
    }

    #[Test]
    public function dashboard_summary_groups_views_by_url(): void
    {
        $secondUrl = EventUrl::query()->create([
            'event_id' => 1,
            'organization_id' => 1,
            'name' => 'Onsite Desk',
            'slug' => 'onsite-desk',
            'type' => 'onsite',
            'is_active' => true,
        ]);

        EventUrlVisit::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'event_url_id' => $this->eventUrl->id,
            'visitor_uuid' => (string) Str::uuid(),
            'session_key' => bin2hex(random_bytes(16)),
            'pageview_count' => 4,
            'duration_seconds' => 60,
            'registered' => true,
            'is_bounce' => false,
            'started_at' => now()->subDay(),
            'last_seen_at' => now(),
        ]);
        EventUrlVisit::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'event_url_id' => $this->eventUrl->id,
            'visitor_uuid' => (string) Str::uuid(),
            'session_key' => bin2hex(random_bytes(16)),
            'pageview_count' => 1,
            'duration_seconds' => 5,
            'registered' => false,
            'is_bounce' => true,
            'started_at' => now()->subHours(2),
            'last_seen_at' => now(),
        ]);
        EventUrlVisit::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'event_url_id' => $secondUrl->id,
            'visitor_uuid' => (string) Str::uuid(),
            'session_key' => bin2hex(random_bytes(16)),
            'pageview_count' => 2,
            'duration_seconds' => 30,
            'registered' => false,
            'is_bounce' => false,
            'started_at' => now()->subHours(3),
            'last_seen_at' => now(),
        ]);

        $summary = app(EventUrlAnalyticsService::class)->dashboardSummary(
            now()->subDays(7)->startOfDay(),
            now()->endOfDay()
        );

        $this->assertTrue($summary['enabled']);
        $this->assertSame(3, $summary['total_visits']);
        $this->assertSame(7, $summary['total_pageviews']);
        $this->assertSame(1, $summary['registrations']);
        $this->assertSame($this->eventUrl->id, $summary['urls'][0]['id']);
        $this->assertSame(2, $summary['urls'][0]['visits']);
        $this->assertSame(5, $summary['urls'][0]['pageviews']);
    }
}
