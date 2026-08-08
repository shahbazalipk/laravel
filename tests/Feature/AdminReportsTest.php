<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    private RegistrationCategory $paidCategory;

    private RegistrationCategory $freeCategory;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'event.currency' => 'AED',
        ]);

        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('currency', 10)->nullable();
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

        Schema::create('registration_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('registration_category_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('currency', 10)->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->boolean('checked_in')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'org_id' => 1,
            'name' => 'Reports Event',
            'title' => 'Reports Event',
            'currency' => 'AED',
        ]);
        app()->instance('current.event', Event::query()->find(1));

        $this->paidCategory = RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'VIP Pass',
            'price' => 1000,
            'currency' => 'AED',
            'is_active' => true,
        ]);
        $this->freeCategory = RegistrationCategory::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Guest Pass',
            'price' => 0,
            'currency' => 'AED',
            'is_active' => true,
        ]);

        Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => $this->paidCategory->id,
            'registration_number' => 'REG-1',
            'first_name' => 'Paid',
            'last_name' => 'User',
            'email' => 'paid@example.com',
            'base_price' => 1000,
            'tax_amount' => 50,
            'total_amount' => 1050,
            'discount_amount' => 0,
            'currency' => 'AED',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'checked_in' => true,
            'created_at' => now()->subDay(),
        ]);
        Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => $this->paidCategory->id,
            'registration_number' => 'REG-2',
            'first_name' => 'Pending',
            'last_name' => 'User',
            'email' => 'pending@example.com',
            'base_price' => 1000,
            'tax_amount' => 50,
            'total_amount' => 945,
            'discount_amount' => 100,
            'currency' => 'AED',
            'payment_status' => 'pending',
            'payment_method' => 'bank',
            'checked_in' => false,
            'created_at' => now()->subHours(5),
        ]);
        Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => $this->freeCategory->id,
            'registration_number' => 'REG-3',
            'first_name' => 'Free',
            'last_name' => 'User',
            'email' => 'free@example.com',
            'base_price' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'discount_amount' => 0,
            'currency' => 'AED',
            'payment_status' => 'paid',
            'checked_in' => false,
            'created_at' => now()->subHours(2),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_categories');
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

    #[Test]
    public function reports_index_and_category_report_are_available(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('data-testid="reports-index-page"', false)
            ->assertSee('data-testid="reports-card-categories"', false)
            ->assertSee('data-testid="reports-card-payments"', false)
            ->assertSee('data-testid="reports-card-questions"', false);

        $this->actingAsAdmin()
            ->get(route('admin.reports.categories'))
            ->assertOk()
            ->assertSee('data-testid="reports-categories-page"', false)
            ->assertSee('data-testid="reports-categories-charts"', false)
            ->assertSee('VIP Pass')
            ->assertSee('Guest Pass')
            ->assertSee('1,995.00');
    }

    #[Test]
    public function payments_report_and_csv_exports_work(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.reports.payments'))
            ->assertOk()
            ->assertSee('data-testid="reports-payments-page"', false)
            ->assertSee('data-testid="reports-payments-by-status"', false)
            ->assertSee('data-testid="reports-payments-charts"', false)
            ->assertSee('Paid');

        $categoriesCsv = $this->actingAsAdmin()
            ->get(route('admin.reports.categories.export'))
            ->assertOk()
            ->assertHeader('content-disposition')
            ->streamedContent();

        $this->assertStringContainsString('VIP Pass', $categoriesCsv);
        $this->assertStringContainsString('Guest Pass', $categoriesCsv);

        $paymentsCsv = $this->actingAsAdmin()
            ->get(route('admin.reports.payments.export'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Paid', $paymentsCsv);
        $this->assertStringContainsString('Pending', $paymentsCsv);
    }
}
