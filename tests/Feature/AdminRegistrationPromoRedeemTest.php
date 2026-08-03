<?php

namespace Tests\Feature;

use App\Enums\PromoDiscountType;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Services\HashService;
use App\Services\PromoCodeService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminRegistrationPromoRedeemTest extends TestCase
{
    private Event $event;

    private RegistrationCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
        ]);

        Schema::dropIfExists('promo_code_emails');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('registration_payment_entries');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');
        Schema::dropIfExists('custom_form_responses');

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
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->decimal('vat_percentage', 5, 2)->default(0);
            $table->boolean('tax_inclusive')->default(false);
            $table->string('currency', 10)->nullable();
            $table->timestamps();
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
            $table->timestamps();
        });

        Schema::create('registration_statuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
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
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('registration_category_id')->nullable();
            $table->unsignedBigInteger('registration_status_id')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('badge_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('PKR');
            $table->unsignedBigInteger('promo_code_id')->nullable();
            $table->string('promo_code', 50)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('payment_status', 32)->default('pending');
            $table->boolean('terms_accepted')->default(false);
            $table->boolean('checked_in')->default(false);
            $table->boolean('email_verified')->default(false);
            $table->boolean('badge_printed')->default(false);
            $table->longText('qr_code')->nullable();
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

        Schema::create('custom_form_responses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->unsignedBigInteger('custom_form_id')->nullable();
            $table->string('respondent_type')->nullable();
            $table->unsignedBigInteger('respondent_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        $this->event = Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'org_id' => 1,
            'name' => 'Promo Event',
            'title' => 'Promo Event',
            'vat_percentage' => 5,
            'tax_inclusive' => false,
            'currency' => 'PKR',
        ]);
        app()->instance('current.event', $this->event);

        $this->category = RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Paid Pass',
            'price' => 1000,
            'currency' => 'PKR',
            'vat_percentage' => 5,
            'is_active' => true,
            'visible' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('custom_form_responses');
        Schema::dropIfExists('promo_code_emails');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('registration_payment_entries');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('hash_mappings');
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

    private function makeRegistration(): Registration
    {
        $registration = Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => $this->category->id,
            'registration_type' => 'individual',
            'registration_number' => 'REG-PROMO1',
            'badge_number' => 'BDG-PROMO1',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'phone' => '+971500000001',
            'company_name' => 'Analytical Engines',
            'base_price' => 1000,
            'tax_amount' => 50,
            'total_amount' => 1050,
            'currency' => 'PKR',
            'payment_status' => 'pending',
            'terms_accepted' => true,
        ]);

        app(HashService::class)->generateHash($registration);

        return $registration->fresh();
    }

    #[Test]
    public function admin_can_redeem_and_remove_promo_on_registration_detail(): void
    {
        $registration = $this->makeRegistration();

        $promo = PromoCode::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'RETURNING26',
            'discount_type' => PromoDiscountType::Percentage,
            'discount_value' => 10,
            'max_uses_per_email' => 1,
            'is_active' => true,
            'restrict_to_email_list' => false,
            'used_count' => 0,
        ]);

        $this->actingAsAdmin()
            ->post(route('admin.registrations.promo.redeem', $registration), [
                'promo_code' => 'returning26',
            ])
            ->assertRedirect(route('admin.registrations.show', $registration))
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame('RETURNING26', $registration->promo_code);
        $this->assertEquals(100.0, (float) $registration->discount_amount);
        $this->assertEquals(900.0, (float) $registration->base_price);
        $this->assertEquals(945.0, (float) $registration->total_amount);
        $this->assertSame(1, (int) $promo->fresh()->used_count);

        $this->actingAsAdmin()
            ->delete(route('admin.registrations.promo.remove', $registration))
            ->assertRedirect(route('admin.registrations.show', $registration))
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertNull($registration->promo_code);
        $this->assertEquals(0.0, (float) $registration->discount_amount);
        $this->assertEquals(1000.0, (float) $registration->base_price);
        $this->assertEquals(1050.0, (float) $registration->total_amount);
        $this->assertSame(0, (int) $promo->fresh()->used_count);
    }

    #[Test]
    public function redeem_on_registration_service_blocks_without_replace_flag(): void
    {
        $registration = $this->makeRegistration();

        PromoCode::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'FIRST10',
            'discount_type' => PromoDiscountType::Percentage,
            'discount_value' => 10,
            'is_active' => true,
        ]);
        PromoCode::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'SECOND20',
            'discount_type' => PromoDiscountType::Percentage,
            'discount_value' => 20,
            'is_active' => true,
        ]);

        $service = app(PromoCodeService::class);
        $service->redeemOnRegistration($registration, 'FIRST10');

        try {
            $service->redeemOnRegistration($registration->fresh(), 'SECOND20', false);
            $this->fail('Expected validation failure when replacing without flag.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('promo_code', $exception->errors());
        }

        $updated = $service->redeemOnRegistration($registration->fresh(), 'SECOND20', true);
        $this->assertSame('SECOND20', $updated->promo_code);
        $this->assertEquals(200.0, (float) $updated->discount_amount);
        $this->assertEquals(840.0, (float) $updated->total_amount);
    }
}
