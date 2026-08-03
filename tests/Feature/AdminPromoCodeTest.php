<?php

namespace Tests\Feature;

use App\Enums\PromoDiscountType;
use App\Models\Event;
use App\Models\PromoCode;
use App\Services\HashService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminPromoCodeTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
        ]);

        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('promo_code_emails');
        Schema::dropIfExists('promo_codes');
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
            $table->unique(['event_id', 'org_id', 'code']);
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
            $table->string('registration_type')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('badge_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 10)->nullable();
            $table->unsignedBigInteger('promo_code_id')->nullable();
            $table->string('promo_code', 50)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('payment_status')->nullable();
            $table->boolean('terms_accepted')->default(false);
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

        $this->event = Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'org_id' => 1,
            'name' => 'Promo Event',
            'title' => 'Promo Event',
            'currency' => 'AED',
        ]);

        app()->instance('current.event', $this->event);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('promo_code_emails');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    private function actingAsAdmin(array $overrides = [])
    {
        return $this->withSession(array_merge([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@test.com',
            'admin_is_primary' => true,
            'event_id' => 1,
            'org_id' => 1,
        ], $overrides));
    }

    private function makePromo(array $overrides = []): PromoCode
    {
        $promo = PromoCode::query()->create(array_merge([
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'SAVE10',
            'name' => 'Ten percent off',
            'discount_type' => PromoDiscountType::Percentage,
            'discount_value' => 10,
            'max_total_uses' => 100,
            'max_uses_per_email' => 1,
            'used_count' => 0,
            'is_active' => true,
        ], $overrides));

        app(HashService::class)->generateHash($promo);

        return $promo->fresh();
    }

    #[Test]
    public function index_lists_promo_codes_for_the_current_event(): void
    {
        $this->makePromo(['code' => 'EARLYBIRD']);

        $response = $this->actingAsAdmin()->get(route('admin.promo-codes.index'));

        $response->assertOk();
        $response->assertSee('data-testid="promo-codes-page"', false);
        $response->assertSee('EARLYBIRD');
        $response->assertSee('10%');
    }

    #[Test]
    public function it_creates_a_percentage_promo_code(): void
    {
        $response = $this->actingAsAdmin()->post(route('admin.promo-codes.store'), [
            'code' => 'early-bird',
            'name' => 'Early bird',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'starts_at' => now()->format('Y-m-d\TH:i'),
            'expires_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'max_total_uses' => 50,
            'max_uses_per_email' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.promo-codes.show', PromoCode::query()->where('code', 'EARLY-BIRD')->first()));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('promo_codes', [
            'event_id' => 1,
            'org_id' => 1,
            'code' => 'EARLY-BIRD',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'max_total_uses' => 50,
            'max_uses_per_email' => 1,
            'is_active' => 1,
        ]);
    }

    #[Test]
    public function it_creates_a_fixed_amount_promo_code(): void
    {
        $this->actingAsAdmin()->post(route('admin.promo-codes.store'), [
            'code' => 'FLAT50',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'currency' => 'aed',
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('promo_codes', [
            'code' => 'FLAT50',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'currency' => 'AED',
        ]);
    }

    #[Test]
    public function percentage_over_100_is_rejected(): void
    {
        $this->actingAsAdmin()
            ->from(route('admin.promo-codes.create'))
            ->post(route('admin.promo-codes.store'), [
                'code' => 'TOOHIGH',
                'discount_type' => 'percentage',
                'discount_value' => 150,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.promo-codes.create'))
            ->assertSessionHasErrors('discount_value');
    }

    #[Test]
    public function fixed_amount_requires_currency(): void
    {
        $this->actingAsAdmin()
            ->from(route('admin.promo-codes.create'))
            ->post(route('admin.promo-codes.store'), [
                'code' => 'NOCUR',
                'discount_type' => 'fixed',
                'discount_value' => 25,
                'currency' => null,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.promo-codes.create'))
            ->assertSessionHasErrors('currency');
    }

    #[Test]
    public function duplicate_codes_are_rejected_for_the_same_event(): void
    {
        $this->makePromo(['code' => 'DUPLICATE']);

        $this->actingAsAdmin()
            ->from(route('admin.promo-codes.create'))
            ->post(route('admin.promo-codes.store'), [
                'code' => 'duplicate',
                'discount_type' => 'percentage',
                'discount_value' => 5,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.promo-codes.create'))
            ->assertSessionHasErrors('code');
    }

    #[Test]
    public function it_updates_toggles_and_deletes_a_promo_code(): void
    {
        $promo = $this->makePromo(['code' => 'UPDATEME', 'is_active' => true]);

        $this->actingAsAdmin()->put(route('admin.promo-codes.update', $promo), [
            'code' => 'UPDATED',
            'name' => 'Updated name',
            'discount_type' => 'fixed',
            'discount_value' => 20,
            'currency' => 'AED',
            'max_total_uses' => 10,
            'max_uses_per_email' => 2,
            'is_active' => 1,
        ])->assertRedirect(route('admin.promo-codes.show', $promo));

        $this->assertDatabaseHas('promo_codes', [
            'id' => $promo->id,
            'code' => 'UPDATED',
            'discount_type' => 'fixed',
            'discount_value' => 20,
        ]);

        $this->actingAsAdmin()
            ->from(route('admin.promo-codes.index'))
            ->post(route('admin.promo-codes.toggle', $promo))
            ->assertRedirect(route('admin.promo-codes.index'));

        $this->assertFalse((bool) $promo->fresh()->is_active);

        $this->actingAsAdmin()
            ->delete(route('admin.promo-codes.destroy', $promo))
            ->assertRedirect(route('admin.promo-codes.index'));

        $this->assertDatabaseMissing('promo_codes', ['id' => $promo->id]);
    }

    #[Test]
    public function validity_helpers_respect_expiry_and_usage_limits(): void
    {
        $expired = $this->makePromo([
            'code' => 'EXPIRED',
            'expires_at' => now()->subDay(),
        ]);
        $exhausted = $this->makePromo([
            'code' => 'EXHAUSTED',
            'max_total_uses' => 2,
            'used_count' => 2,
        ]);
        $valid = $this->makePromo([
            'code' => 'VALID',
            'starts_at' => now()->subHour(),
            'expires_at' => now()->addDay(),
            'max_total_uses' => 5,
            'used_count' => 1,
        ]);

        $this->assertFalse($expired->isCurrentlyValid());
        $this->assertFalse($exhausted->isCurrentlyValid());
        $this->assertTrue($valid->isCurrentlyValid());
        $this->assertSame(4, $valid->remainingUses());
    }

    #[Test]
    public function guests_cannot_manage_promo_codes(): void
    {
        $this->get(route('admin.promo-codes.index'))->assertRedirect();
        $this->post(route('admin.promo-codes.store'), [])->assertRedirect();
    }

    #[Test]
    public function it_creates_email_restricted_promo_with_csv_allowlist(): void
    {
        $csv = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'emails.csv',
            "email\nalumni@example.com\nReturning.Attendee@Example.com\n"
        );

        $response = $this->actingAsAdmin()->post(route('admin.promo-codes.store'), [
            'code' => 'RETURNING26',
            'name' => 'Returning attendees',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'max_uses_per_email' => 1,
            'restrict_to_email_list' => 1,
            'email_list_file' => $csv,
            'is_active' => 1,
        ]);

        $promo = PromoCode::query()->where('code', 'RETURNING26')->first();
        $this->assertNotNull($promo);
        $response->assertRedirect(route('admin.promo-codes.show', $promo));
        $this->assertTrue($promo->restrict_to_email_list);
        $this->assertSame(2, $promo->emails()->count());
        $this->assertTrue($promo->isEmailAllowed('alumni@example.com'));
        $this->assertTrue($promo->isEmailAllowed('returning.attendee@example.com'));
        $this->assertFalse($promo->isEmailAllowed('outsider@example.com'));
    }

    #[Test]
    public function show_page_lists_allowlist_emails_and_supports_search(): void
    {
        $promo = $this->makePromo([
            'code' => 'DETAILS',
            'restrict_to_email_list' => true,
        ]);

        \App\Models\PromoCodeEmail::query()->create([
            'promo_code_id' => $promo->id,
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'alumni@example.com',
        ]);
        \App\Models\PromoCodeEmail::query()->create([
            'promo_code_id' => $promo->id,
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'partner@example.com',
        ]);

        $this->actingAsAdmin()
            ->get(route('admin.promo-codes.show', $promo))
            ->assertOk()
            ->assertSee('data-testid="promo-code-show-page"', false)
            ->assertSee('data-testid="promo-code-emails-table"', false)
            ->assertSee('alumni@example.com')
            ->assertSee('partner@example.com')
            ->assertSee('DETAILS');

        $this->actingAsAdmin()
            ->get(route('admin.promo-codes.show', $promo).'?q=alumni')
            ->assertOk()
            ->assertSee('alumni@example.com')
            ->assertDontSee('partner@example.com');
    }

    #[Test]
    public function show_page_marks_which_allowlist_emails_have_been_used(): void
    {
        $promo = $this->makePromo([
            'code' => 'USEDCHECK',
            'restrict_to_email_list' => true,
            'used_count' => 1,
        ]);

        $used = \App\Models\PromoCodeEmail::query()->create([
            'promo_code_id' => $promo->id,
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'used@example.com',
        ]);
        $unused = \App\Models\PromoCodeEmail::query()->create([
            'promo_code_id' => $promo->id,
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'unused@example.com',
        ]);

        \App\Models\Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_number' => 'REG-USED-1',
            'first_name' => 'Used',
            'last_name' => 'Person',
            'email' => 'used@example.com',
            'base_price' => 900,
            'tax_amount' => 45,
            'total_amount' => 945,
            'currency' => 'AED',
            'promo_code_id' => $promo->id,
            'promo_code' => 'USEDCHECK',
            'discount_amount' => 100,
            'payment_status' => 'pending',
            'terms_accepted' => true,
        ]);

        $this->actingAsAdmin()
            ->get(route('admin.promo-codes.show', $promo))
            ->assertOk()
            ->assertSee('data-testid="promo-code-usage-email-summary"', false)
            ->assertSee('1 of 2 allowlisted emails used', false)
            ->assertSee('data-testid="promo-code-email-used-'.$used->id.'"', false)
            ->assertSee('data-testid="promo-code-email-unused-'.$unused->id.'"', false)
            ->assertSee('data-testid="promo-code-redemptions-table"', false)
            ->assertSee('used@example.com')
            ->assertSee('REG-USED-1');

        $this->actingAsAdmin()
            ->get(route('admin.promo-codes.show', $promo).'?status=used')
            ->assertOk()
            ->assertSee('data-testid="promo-code-email-row-'.$used->id.'"', false)
            ->assertDontSee('data-testid="promo-code-email-row-'.$unused->id.'"', false);

        $this->actingAsAdmin()
            ->get(route('admin.promo-codes.show', $promo).'?status=unused')
            ->assertOk()
            ->assertSee('data-testid="promo-code-email-row-'.$unused->id.'"', false)
            ->assertDontSee('data-testid="promo-code-email-row-'.$used->id.'"', false);
    }

    #[Test]
    public function show_page_can_add_edit_and_delete_emails_one_by_one(): void
    {
        $promo = $this->makePromo([
            'code' => 'MANUAL',
            'restrict_to_email_list' => true,
        ]);

        $this->actingAsAdmin()
            ->post(route('admin.promo-codes.emails.store', $promo), [
                'email' => 'New.Person@Example.com',
            ])
            ->assertRedirect(route('admin.promo-codes.show', $promo))
            ->assertSessionHas('success');

        $row = \App\Models\PromoCodeEmail::query()
            ->where('promo_code_id', $promo->id)
            ->where('email', 'new.person@example.com')
            ->first();
        $this->assertNotNull($row);

        $this->actingAsAdmin()
            ->put(route('admin.promo-codes.emails.update', [$promo, $row]), [
                'email' => 'updated.person@example.com',
            ])
            ->assertRedirect(route('admin.promo-codes.show', $promo));

        $this->assertSame('updated.person@example.com', $row->fresh()->email);

        $this->actingAsAdmin()
            ->from(route('admin.promo-codes.show', $promo))
            ->post(route('admin.promo-codes.emails.store', $promo), [
                'email' => 'updated.person@example.com',
            ])
            ->assertRedirect(route('admin.promo-codes.show', $promo))
            ->assertSessionHasErrors('email');

        $this->actingAsAdmin()
            ->delete(route('admin.promo-codes.emails.destroy', [$promo, $row]))
            ->assertRedirect(route('admin.promo-codes.show', $promo));

        $this->assertDatabaseMissing('promo_code_emails', ['id' => $row->id]);
    }

    #[Test]
    public function restricted_promo_requires_email_csv_on_create(): void
    {
        $this->actingAsAdmin()
            ->from(route('admin.promo-codes.create'))
            ->post(route('admin.promo-codes.store'), [
                'code' => 'NEEDLIST',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'restrict_to_email_list' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.promo-codes.create'))
            ->assertSessionHasErrors('email_list_file');
    }

    #[Test]
    public function create_page_shows_email_allowlist_controls(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.promo-codes.create'))
            ->assertOk()
            ->assertSee('data-testid="promo-restrict-email-list"', false)
            ->assertSee('data-testid="promo-email-list-file"', false);
    }
}
