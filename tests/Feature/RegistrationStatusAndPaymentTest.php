<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\RegistrationStatus;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Services\AuditService;
use App\Services\HashService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationStatusAndPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);

        Schema::dropIfExists('registration_payment_entries');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('events');
        Schema::dropIfExists('hash_mappings');

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
            $table->unsignedBigInteger('org_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('name')->nullable();
            $table->decimal('vat_percentage', 5, 2)->nullable();
            $table->boolean('tax_inclusive')->default(false);
            $table->string('currency', 10)->nullable();
            $table->timestamps();
        });

        \DB::table('events')->insert([
            'id' => 1,
            'org_id' => 1,
            'organization_id' => 1,
            'name' => 'Test Event',
            'vat_percentage' => 5,
            'tax_inclusive' => false,
            'currency' => 'AED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('registration_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->nullable();
            $table->decimal('vat_percentage', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('registration_statuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
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
            $table->string('company_name')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('AED');
            $table->string('payment_status', 32)->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->string('checked_in_by')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->boolean('badge_printed')->default(false);
            $table->string('badge_number')->nullable();
            $table->text('qr_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('registration_payment_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('registration_id')->index();
            $table->string('type', 32);
            $table->string('status', 32);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10);
            $table->string('method', 100)->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('occurred_at');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reverses_entry_id')->nullable();
            $table->string('recorded_by_type')->nullable();
            $table->unsignedBigInteger('recorded_by_id')->nullable();
            $table->string('recorded_by_name')->nullable();
            $table->string('recorded_by_email')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $audit = Mockery::mock(AuditService::class);
        $audit->shouldReceive('log')->andReturnNull();
        $this->app->instance(AuditService::class, $audit);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registration_payment_entries');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('events');
        Schema::dropIfExists('hash_mappings');
        Mockery::close();
        parent::tearDown();
    }

    private function actingAsAdmin(array $overrides = [])
    {
        return $this->withSession(array_merge([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_name' => 'Test Admin',
            'admin_email' => 'admin@test.com',
            'admin_type' => 'event_admin',
            'event_id' => 1,
            'org_id' => 1,
        ], $overrides));
    }

    private function makeRegistration(array $overrides = []): Registration
    {
        $categoryId = $overrides['registration_category_id'] ?? \DB::table('registration_categories')->insertGetId([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'General',
            'price' => 100,
            'currency' => 'AED',
            'vat_percentage' => 5,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $registration = Registration::create(array_merge([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => $categoryId,
            'registration_type' => 'individual',
            'registration_number' => 'REG-'.Str::upper(Str::random(6)),
            'first_name' => 'Shahbaz',
            'last_name' => 'Ali',
            'email' => 'shahbaz@example.com',
            'phone' => '0500000000',
            'company_name' => 'Acme',
            'base_price' => 100,
            'tax_amount' => 0,
            'total_amount' => 100,
            'currency' => 'AED',
            'payment_status' => 'pending',
        ], $overrides));

        app(HashService::class)->generateHash($registration);

        return $registration->fresh();
    }

    #[Test]
    public function guests_cannot_update_status_or_record_payments(): void
    {
        $registration = $this->makeRegistration();

        $this->patch(route('admin.registrations.status.update', $registration), [
            'registration_status_id' => 1,
        ])->assertRedirect();

        $this->post(route('admin.registrations.payments.store', $registration), [
            'amount' => 50,
            'status' => 'succeeded',
        ])->assertRedirect();
    }

    #[Test]
    public function it_updates_registration_status_with_hashed_route(): void
    {
        $status = RegistrationStatus::create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Confirmed',
            'color' => '#22c55e',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $registration = $this->makeRegistration();

        $response = $this->actingAsAdmin()->patch(
            route('admin.registrations.status.update', $registration),
            ['registration_status_id' => $status->id]
        );

        $response->assertRedirect(route('admin.registrations.show', $registration));
        $this->assertSame($status->id, $registration->fresh()->registration_status_id);
    }

    #[Test]
    public function it_rejects_inactive_or_foreign_status(): void
    {
        $inactive = RegistrationStatus::create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Archived',
            'is_active' => false,
        ]);

        $foreign = RegistrationStatus::withoutGlobalScopes()->create([
            'event_id' => 99,
            'org_id' => 99,
            'name' => 'Other Event',
            'is_active' => true,
        ]);

        $registration = $this->makeRegistration();

        $this->actingAsAdmin()
            ->from(route('admin.registrations.show', $registration))
            ->patch(route('admin.registrations.status.update', $registration), [
                'registration_status_id' => $inactive->id,
            ])
            ->assertSessionHasErrors('registration_status_id');

        $this->actingAsAdmin()
            ->from(route('admin.registrations.show', $registration))
            ->patch(route('admin.registrations.status.update', $registration), [
                'registration_status_id' => $foreign->id,
            ])
            ->assertSessionHasErrors('registration_status_id');
    }

    #[Test]
    public function changing_category_to_zero_price_recalculates_totals_and_clears_balance(): void
    {
        $paidCategoryId = \DB::table('registration_categories')->insertGetId([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Paid',
            'price' => 30000,
            'currency' => 'PKR',
            'vat_percentage' => 5,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $freeCategoryId = \DB::table('registration_categories')->insertGetId([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Complimentary',
            'price' => 0,
            'currency' => 'PKR',
            'vat_percentage' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $registration = $this->makeRegistration([
            'registration_category_id' => $paidCategoryId,
            'base_price' => 30000,
            'tax_amount' => 1500,
            'total_amount' => 31500,
            'currency' => 'PKR',
            'payment_status' => 'pending',
        ]);

        $this->actingAsAdmin()->put(route('admin.registrations.update', $registration), [
            'registration_category_id' => $freeCategoryId,
            'registration_type' => 'individual',
            'first_name' => $registration->first_name,
            'last_name' => $registration->last_name,
            'email' => $registration->email,
            'phone' => $registration->phone,
            'company_name' => $registration->company_name,
        ])->assertRedirect(route('admin.registrations.show', $registration));

        $registration->refresh();

        $this->assertSame(0.0, (float) $registration->base_price);
        $this->assertSame(0.0, (float) $registration->tax_amount);
        $this->assertSame(0.0, (float) $registration->total_amount);
        $this->assertSame('PKR', $registration->currency);
        $this->assertSame('paid', $registration->payment_status);

        $summary = app(\App\Payments\Services\RegistrationPaymentTotals::class)->calculate($registration);
        $this->assertSame(0.0, $summary['balance_due']);
        $this->assertSame('paid', $summary['summary_status']->value);
    }

    #[Test]
    public function it_records_payment_with_actor_snapshot_and_updates_compatibility_fields(): void
    {
        $registration = $this->makeRegistration();

        $response = $this->actingAsAdmin()->post(
            route('admin.registrations.payments.store', $registration),
            [
                'amount' => 40.5,
                'currency' => 'AED',
                'method' => 'Bank Transfer',
                'reference' => 'TX-100',
                'status' => 'succeeded',
                'notes' => 'Partial payment',
            ]
        );

        $response->assertRedirect(route('admin.registrations.show', $registration));

        $entry = RegistrationPaymentEntry::query()->first();
        $this->assertNotNull($entry);
        $this->assertSame(PaymentEntryType::Payment, $entry->type);
        $this->assertSame('Test Admin', $entry->recorded_by_name);
        $this->assertSame('admin@test.com', $entry->recorded_by_email);
        $this->assertSame(1, (int) $entry->recorded_by_id);

        $registration->refresh();
        $this->assertSame('partially_paid', $registration->payment_status);
        $this->assertSame('Bank Transfer', $registration->payment_method);
        $this->assertSame('TX-100', $registration->payment_reference);
    }

    #[Test]
    public function it_refunds_and_reverses_payments(): void
    {
        $registration = $this->makeRegistration(['total_amount' => 100]);

        $this->actingAsAdmin()->post(route('admin.registrations.payments.store', $registration), [
            'amount' => 100,
            'status' => 'succeeded',
            'method' => 'Card',
            'reference' => 'FULL',
        ])->assertRedirect();

        $payment = RegistrationPaymentEntry::query()->firstOrFail();

        $this->actingAsAdmin()->post(
            route('admin.registrations.payments.refund', [$registration, $payment]),
            ['amount' => 25, 'reference' => 'RF-25']
        )->assertRedirect();

        $this->assertSame(2, RegistrationPaymentEntry::query()->count());
        $this->assertSame('partially_refunded', $registration->fresh()->payment_status);

        $this->actingAsAdmin()->post(
            route('admin.registrations.payments.reverse', [$registration, $payment]),
            ['notes' => 'Mistake']
        )->assertRedirect();

        $this->assertSame(3, RegistrationPaymentEntry::query()->count());
        $this->assertTrue(
            RegistrationPaymentEntry::query()
                ->where('type', PaymentEntryType::Reversal->value)
                ->where('reverses_entry_id', $payment->id)
                ->exists()
        );
    }

    #[Test]
    public function show_page_renders_status_and_payment_ui(): void
    {
        $status = RegistrationStatus::create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Pending Review',
            'color' => '#6366f1',
            'is_active' => true,
        ]);

        $registration = $this->makeRegistration([
            'registration_status_id' => $status->id,
        ]);

        $response = $this->actingAsAdmin()->get(route('admin.registrations.show', $registration));

        $response->assertOk();
        $response->assertSee('data-testid="registration-status-card"', false);
        $response->assertSee('data-testid="payment-summary-card"', false);
        $response->assertSee('data-testid="payment-history-card"', false);
        $response->assertSee('data-testid="log-payment-modal"', false);
        $response->assertSee('Pending Review');
    }

    #[Test]
    public function other_event_cannot_access_registration_payment_routes(): void
    {
        $registration = $this->makeRegistration();

        $this->actingAsAdmin([
            'event_id' => 99,
            'org_id' => 99,
        ])->get(route('admin.registrations.show', $registration))->assertNotFound();

        $this->actingAsAdmin([
            'event_id' => 99,
            'org_id' => 99,
        ])->post(route('admin.registrations.payments.store', $registration), [
            'amount' => 10,
            'status' => 'succeeded',
        ])->assertNotFound();
    }

    #[Test]
    public function payment_entries_have_no_update_or_delete_routes(): void
    {
        $routes = collect(app('router')->getRoutes())->map(fn ($route) => [
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'name' => $route->getName(),
        ]);

        $paymentRoutes = $routes->filter(fn ($route) => str_contains($route['uri'], 'registrations/{registration}/payments'));

        $this->assertTrue($paymentRoutes->contains(fn ($route) => in_array('POST', $route['methods'], true)
            && $route['name'] === 'admin.registrations.payments.store'));

        $this->assertFalse($paymentRoutes->contains(fn ($route) => in_array('PUT', $route['methods'], true)
            || in_array('PATCH', $route['methods'], true)
            || in_array('DELETE', $route['methods'], true)));
    }

    #[Test]
    public function permanent_deletion_requires_the_registration_number(): void
    {
        $registration = $this->makeRegistration();

        $this->actingAsAdmin()
            ->from(route('admin.registrations.show', $registration))
            ->delete(route('admin.registrations.destroy', $registration), [
                'confirmation' => 'WRONG-NUMBER',
            ])
            ->assertRedirect(route('admin.registrations.show', $registration))
            ->assertSessionHasErrors('confirmation');

        $this->assertDatabaseHas('registrations', ['id' => $registration->id]);
    }

    #[Test]
    public function it_permanently_deletes_a_registration_payment_entries_and_hash(): void
    {
        $registration = $this->makeRegistration();

        $this->actingAsAdmin()->post(
            route('admin.registrations.payments.store', $registration),
            [
                'amount' => 25,
                'status' => 'succeeded',
                'method' => 'Card',
            ]
        )->assertRedirect();

        $this->assertDatabaseHas('registration_payment_entries', [
            'registration_id' => $registration->id,
        ]);
        $this->assertDatabaseHas('hash_mappings', [
            'model_type' => Registration::class,
            'model_id' => $registration->id,
        ]);

        $this->actingAsAdmin()
            ->delete(route('admin.registrations.destroy', $registration), [
                'confirmation' => $registration->registration_number,
            ])
            ->assertRedirect(route('admin.registrations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('registrations', ['id' => $registration->id]);
        $this->assertDatabaseMissing('registration_payment_entries', [
            'registration_id' => $registration->id,
        ]);
        $this->assertDatabaseMissing('hash_mappings', [
            'model_type' => Registration::class,
            'model_id' => $registration->id,
        ]);
    }

    #[Test]
    public function backfill_creates_opening_entry_for_paid_registrations_only(): void
    {
        $paid = $this->makeRegistration([
            'payment_status' => 'paid',
            'total_amount' => 150,
            'payment_method' => 'Cash',
            'payment_reference' => 'LEGACY-1',
            'payment_date' => now()->subDay(),
        ]);

        $pending = $this->makeRegistration([
            'payment_status' => 'pending',
            'total_amount' => 80,
        ]);

        $refunded = $this->makeRegistration([
            'payment_status' => 'refunded',
            'total_amount' => 90,
        ]);

        Artisan::call('payments:backfill-paid-registrations');

        $this->assertSame(1, RegistrationPaymentEntry::withoutGlobalScopes()->count());
        $entry = RegistrationPaymentEntry::withoutGlobalScopes()->first();
        $this->assertSame($paid->id, $entry->registration_id);
        $this->assertSame(150.0, (float) $entry->amount);
        $this->assertSame('Cash', $entry->method);
        $this->assertSame('LEGACY-1', $entry->reference);
        $this->assertSame(PaymentEntryType::Payment, $entry->type);
        $this->assertSame(PaymentEntryStatus::Succeeded, $entry->status);

        $this->assertFalse(
            RegistrationPaymentEntry::withoutGlobalScopes()
                ->where('registration_id', $pending->id)
                ->exists()
        );
        $this->assertFalse(
            RegistrationPaymentEntry::withoutGlobalScopes()
                ->where('registration_id', $refunded->id)
                ->exists()
        );
    }
}
