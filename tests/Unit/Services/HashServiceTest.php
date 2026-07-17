<?php

namespace Tests\Unit\Services;

use App\Models\HashMapping;
use App\Models\Registration;
use App\Services\HashService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HashServiceTest extends TestCase
{
    private HashService $service;

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

        $this->service = new HashService();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('hash_mappings');
        parent::tearDown();
    }

    public function test_resolve_hash_finds_model_for_current_event(): void
    {
        $registration = Registration::create([
            'event_id' => 30,
            'org_id' => 8,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'registration_number' => 'REG-1',
        ]);

        $hash = $registration->hash;

        $this->assertNotNull($hash);

        $resolved = $this->service->resolveHash($hash, Registration::class);

        $this->assertInstanceOf(Registration::class, $resolved);
        $this->assertTrue($registration->is($resolved));
    }

    public function test_resolve_hash_rejects_other_event_context(): void
    {
        $registration = Registration::create([
            'event_id' => 30,
            'org_id' => 8,
            'first_name' => 'Ada',
            'email' => 'ada@example.com',
        ]);

        $hash = $registration->hash;

        config(['event.event_id' => 99, 'event.org_id' => 99]);

        $this->assertNull($this->service->resolveHash($hash, Registration::class));
    }

    public function test_resolve_hash_bootstraps_context_when_missing(): void
    {
        $registration = Registration::create([
            'event_id' => 30,
            'org_id' => 8,
            'first_name' => 'Ada',
            'email' => 'ada@example.com',
        ]);

        $hash = $registration->hash;

        config(['event.event_id' => null, 'event.org_id' => null]);

        $resolved = $this->service->resolveHash($hash, Registration::class);

        $this->assertInstanceOf(Registration::class, $resolved);
        $this->assertSame(30, (int) config('event.event_id'));
        $this->assertSame(8, (int) config('event.org_id'));
    }

    public function test_resolve_hash_rejects_unexpected_model_type(): void
    {
        $registration = Registration::create([
            'event_id' => 30,
            'org_id' => 8,
            'first_name' => 'Ada',
            'email' => 'ada@example.com',
        ]);

        $this->assertNull(
            $this->service->resolveHash($registration->hash, HashMapping::class)
        );
    }

    public function test_soft_delete_keeps_hash_mapping(): void
    {
        $registration = Registration::create([
            'event_id' => 30,
            'org_id' => 8,
            'first_name' => 'Ada',
            'email' => 'ada@example.com',
        ]);

        $hash = $registration->hash;
        $registration->delete();

        $this->assertDatabaseHas('hash_mappings', [
            'hash' => $hash,
            'model_type' => Registration::class,
            'model_id' => $registration->id,
        ]);
    }
}
