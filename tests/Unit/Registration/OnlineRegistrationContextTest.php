<?php

namespace Tests\Unit\Registration;

use App\Models\Event;
use App\Models\EventUrl;
use App\Models\RegistrationCategory;
use App\Registration\Services\OnlineRegistrationContext;
use App\Services\RegistrationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OnlineRegistrationContextTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);

        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('event_urls');
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
            $table->boolean('is_active')->default(true);
            $table->json('enabled_categories')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('visible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('event_urls');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    #[Test]
    public function it_excludes_date_closed_categories_from_allowed_list(): void
    {
        $event = Event::query()->create([
            'organization_id' => 1,
            'title' => 'Event',
            'registration_form_active' => true,
            'currency' => 'AED',
        ]);

        $open = RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Open',
            'price' => 0,
            'is_active' => true,
            'visible' => true,
            'valid_to' => now()->addDay(),
        ]);

        RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Closed',
            'price' => 0,
            'is_active' => true,
            'visible' => true,
            'valid_to' => now()->subDay(),
        ]);

        $url = EventUrl::query()->create([
            'event_id' => 1,
            'organization_id' => 1,
            'slug' => 'open-url',
            'is_active' => true,
            'enabled_categories' => null,
        ]);

        $context = new OnlineRegistrationContext(app(RegistrationService::class));
        $allowed = $context->allowedCategories($event, $url);

        $this->assertCount(1, $allowed);
        $this->assertTrue($open->is($allowed->first()));
    }

    #[Test]
    public function it_allows_categories_when_enabled_ids_are_stored_as_strings(): void
    {
        $event = Event::query()->create([
            'organization_id' => 1,
            'title' => 'Event',
            'registration_form_active' => true,
            'currency' => 'AED',
        ]);

        $category = RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Standard',
            'price' => 0,
            'is_active' => true,
            'visible' => true,
        ]);

        $url = EventUrl::query()->create([
            'event_id' => 1,
            'organization_id' => 1,
            'slug' => 'online',
            'is_active' => true,
            // Mimic admin JSON payloads that store IDs as strings.
            'enabled_categories' => [(string) $category->id],
        ]);

        $context = new OnlineRegistrationContext(app(RegistrationService::class));

        $this->assertSame([(int) $category->id], $context->normalizedEnabledCategoryIds($url));
        $this->assertCount(1, $context->allowedCategories($event, $url));

        $context->assertCategoryAllowed($category, $event, $url);
        $this->assertTrue(true);
    }
}
