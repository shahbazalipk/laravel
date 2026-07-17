<?php

namespace Tests\Feature;

use App\Models\Event;
use DateTimeZone;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventSettingsTimezoneTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
        ]);

        Schema::dropIfExists('landing_page_templates');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('title')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('llm_enabled')->default(false);
            $table->string('llm_provider')->nullable();
            $table->string('llm_model')->nullable();
            $table->text('llm_api_key')->nullable();
            $table->json('llm_settings')->nullable();
            $table->timestamps();
        });

        Schema::create('landing_page_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $event = Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'title' => 'Tech Trip',
            'timezone' => 'UTC',
        ]);

        app()->instance('current.event', $event);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('landing_page_templates');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    private function actingAsAdmin()
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@test.com',
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    #[Test]
    public function timezone_is_a_dropdown_of_valid_identifiers(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.event-settings.edit'));

        $response->assertOk();
        $response->assertSee('data-testid="event-timezone-select"', false);
        $response->assertSee('<select', false);
        $response->assertSee('Asia/Karachi', false);
        $response->assertSee('America/New_York', false);
        $response->assertDontSee('placeholder="e.g., Asia/Dubai"', false);
    }

    #[Test]
    public function timezone_update_rejects_invalid_values_and_accepts_valid_ones(): void
    {
        $this->actingAsAdmin()
            ->from(route('admin.event-settings.edit'))
            ->put(route('admin.event-settings.update'), [
                'timezone' => 'Not/A_Real_Zone',
            ])
            ->assertRedirect(route('admin.event-settings.edit'))
            ->assertSessionHasErrors('timezone');

        $this->assertSame('UTC', Event::query()->findOrFail(1)->timezone);

        $validTimezone = in_array('Asia/Karachi', DateTimeZone::listIdentifiers(), true)
            ? 'Asia/Karachi'
            : DateTimeZone::listIdentifiers()[0];

        $validator = Validator::make(
            ['timezone' => $validTimezone],
            ['timezone' => ['nullable', 'string', 'max:100', Rule::in(DateTimeZone::listIdentifiers())]]
        );
        $this->assertFalse($validator->fails());

        $this->actingAsAdmin()
            ->put(route('admin.event-settings.update'), [
                'timezone' => $validTimezone,
            ])
            ->assertRedirect(route('admin.event-settings.edit'))
            ->assertSessionHasNoErrors();

        $this->assertSame($validTimezone, Event::query()->findOrFail(1)->fresh()->timezone);
    }
}
