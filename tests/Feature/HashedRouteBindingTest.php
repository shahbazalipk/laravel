<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Services\EventContextService;
use App\Services\HashService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HashedRouteBindingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 30, 'event.org_id' => 8]);

        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registrations');

        Schema::create('hash_mappings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('hash', 64)->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('registration_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Route::middleware(['web', 'event.admin'])
            ->get('/_test/hashed-registrations/{registration}', function (Registration $registration) {
                return response()->json([
                    'id' => $registration->id,
                    'first_name' => $registration->first_name,
                ]);
            })
            ->name('test.hashed-registrations.show');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('hash_mappings');
        parent::tearDown();
    }

    public function test_web_middleware_applies_event_context_before_route_binding(): void
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $web = array_values($kernel->getMiddlewareGroups()['web']);

        $setEventContext = \App\Http\Middleware\SetEventContext::class;
        $bindings = \Illuminate\Routing\Middleware\SubstituteBindings::class;

        $this->assertContains($setEventContext, $web);
        $this->assertContains($bindings, $web);
        $this->assertLessThan(
            array_search($bindings, $web, true),
            array_search($setEventContext, $web, true),
            'SetEventContext must run before SubstituteBindings'
        );
    }

    public function test_hashed_registration_url_resolves_when_session_has_event_context(): void
    {
        // Request starts without config context; middleware must restore it from session.
        config(['event.event_id' => null, 'event.org_id' => null]);

        $registration = Registration::withoutGlobalScopes()->create([
            'event_id' => 30,
            'org_id' => 8,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'registration_number' => 'REG-100',
        ]);

        $hash = app(HashService::class)->generateHash($registration);

        $response = $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@test.com',
            'event_id' => 30,
            'org_id' => 8,
        ])->get('/_test/hashed-registrations/'.$hash);

        $response->assertOk();
        $response->assertJson([
            'id' => $registration->id,
            'first_name' => 'Ada',
        ]);
    }

    public function test_hashed_registration_url_returns_404_for_other_event(): void
    {
        $registration = Registration::create([
            'event_id' => 30,
            'org_id' => 8,
            'first_name' => 'Ada',
            'email' => 'ada@example.com',
        ]);

        $response = $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@test.com',
            'event_id' => 99,
            'org_id' => 99,
        ])->get('/_test/hashed-registrations/'.$registration->hash);

        $response->assertNotFound();
    }

    public function test_route_binding_uses_hash_service_after_context_boot(): void
    {
        config(['event.event_id' => null, 'event.org_id' => null]);

        $registration = Registration::withoutGlobalScopes()->create([
            'event_id' => 30,
            'org_id' => 8,
            'first_name' => 'Grace',
            'email' => 'grace@example.com',
        ]);

        $hash = app(HashService::class)->generateHash($registration);

        session(['event_id' => 30, 'org_id' => 8]);
        app(EventContextService::class)->bootFromSession();

        $bound = (new Registration)->resolveRouteBinding($hash);

        $this->assertInstanceOf(Registration::class, $bound);
        $this->assertTrue($registration->is($bound));
    }
}
