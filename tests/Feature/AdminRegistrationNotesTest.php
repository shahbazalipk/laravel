<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\RegistrationNote;
use App\Services\AuditService;
use App\Services\HashService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminRegistrationNotesTest extends TestCase
{
    private Registration $registration;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
        ]);

        Schema::dropIfExists('registration_notes');
        Schema::dropIfExists('registration_payment_entries');
        Schema::dropIfExists('custom_form_responses');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('name')->nullable();
            $table->decimal('vat_percentage', 5, 2)->nullable();
            $table->boolean('tax_inclusive')->default(false);
            $table->string('currency', 10)->nullable();
            $table->timestamps();
        });

        \DB::table('events')->insert([
            'id' => 1, 'org_id' => 1, 'name' => 'Test Event',
            'vat_percentage' => 5, 'tax_inclusive' => false, 'currency' => 'AED',
            'created_at' => now(), 'updated_at' => now(),
        ]);

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

        Schema::create('registration_statuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
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

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('registration_category_id')->nullable();
            $table->unsignedBigInteger('registration_status_id')->nullable();
            $table->string('hash', 64)->nullable()->unique();
            $table->string('registration_number')->nullable();
            $table->string('registration_type')->nullable();
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
            $table->boolean('checked_in')->default(false);
            $table->boolean('email_verified')->default(false);
            $table->boolean('badge_printed')->default(false);
            $table->text('qr_code')->nullable();
            $table->text('notes')->nullable();
            $table->string('profile_picture')->nullable();
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
            $table->string('recorded_by_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('registration_notes', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('registration_id')->index();
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('author_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_form_responses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('respondent_type')->nullable();
            $table->unsignedBigInteger('respondent_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $audit = Mockery::mock(AuditService::class);
        $audit->shouldReceive('log')->andReturnNull();
        $this->app->instance(AuditService::class, $audit);

        \DB::table('registration_categories')->insert([
            'id' => 1, 'event_id' => 1, 'org_id' => 1,
            'name' => 'General', 'price' => 500, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->registration = Registration::create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => 1,
            'registration_number' => 'REG-001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '0501234567',
            'payment_status' => 'pending',
            'total_amount' => 500,
        ]);

        app(HashService::class)->generateHash($this->registration);
        $this->registration->refresh();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registration_notes');
        Schema::dropIfExists('registration_payment_entries');
        Schema::dropIfExists('custom_form_responses');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');

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
            'admin_is_primary' => true,
            'event_id' => 1,
            'org_id' => 1,
        ], $overrides));
    }

    #[Test]
    public function show_page_contains_notes_card(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.registrations.show', $this->registration));

        $response->assertOk();
        $response->assertSee('data-testid="registration-notes-card"', false);
        $response->assertSee('data-testid="add-note-form"', false);
        $response->assertSee('data-testid="notes-empty"', false);
    }

    #[Test]
    public function admin_can_add_text_only_note(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.registrations.notes.store', $this->registration), [
                'body' => 'This is an important internal note.',
            ]);

        $response->assertRedirect(route('admin.registrations.show', $this->registration));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('registration_notes', [
            'registration_id' => $this->registration->id,
            'body' => 'This is an important internal note.',
            'author_name' => 'Test Admin',
        ]);
    }

    #[Test]
    public function admin_can_add_note_with_image(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('note.jpg', 200, 200);

        $response = $this->actingAsAdmin()
            ->post(route('admin.registrations.notes.store', $this->registration), [
                'body' => 'Note with image',
                'image' => $image,
            ]);

        $response->assertRedirect(route('admin.registrations.show', $this->registration));
        $response->assertSessionHas('success');

        $note = RegistrationNote::where('registration_id', $this->registration->id)->first();
        $this->assertNotNull($note);
        $this->assertNotNull($note->image_path);
        $this->assertStringStartsWith('registrations/notes/', $note->image_path);
        Storage::disk('public')->assertExists($note->image_path);
    }

    #[Test]
    public function admin_can_add_image_only_note(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('attachment.png');

        $response = $this->actingAsAdmin()
            ->post(route('admin.registrations.notes.store', $this->registration), [
                'image' => $image,
            ]);

        $response->assertRedirect(route('admin.registrations.show', $this->registration));
        $this->assertDatabaseCount('registration_notes', 1);
    }

    #[Test]
    public function note_with_neither_text_nor_image_fails_validation(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.registrations.notes.store', $this->registration), [
                'body' => '',
            ]);

        $response->assertSessionHasErrors('body');
        $this->assertDatabaseCount('registration_notes', 0);
    }

    #[Test]
    public function admin_can_delete_a_note(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('del.jpg');
        $imagePath = $image->storeAs('registrations/notes', 'del.jpg', 'public');

        $note = RegistrationNote::create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_id' => $this->registration->id,
            'body' => 'Delete me',
            'image_path' => $imagePath,
            'author_name' => 'Admin',
        ]);

        Storage::disk('public')->assertExists($imagePath);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.registrations.notes.destroy', [$this->registration, $note]));

        $response->assertRedirect(route('admin.registrations.show', $this->registration));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('registration_notes', ['id' => $note->id, 'deleted_at' => null]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    #[Test]
    public function notes_are_shown_on_the_show_page(): void
    {
        RegistrationNote::create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_id' => $this->registration->id,
            'body' => 'Visible note content',
            'author_name' => 'Event Staff',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.registrations.show', $this->registration));

        $response->assertOk();
        $response->assertSee('Visible note content');
        $response->assertSee('Event Staff');
    }

    #[Test]
    public function guests_cannot_store_notes(): void
    {
        $response = $this->post(
            route('admin.registrations.notes.store', $this->registration),
            ['body' => 'Sneaky note']
        );

        $response->assertRedirect(); // redirected to login
        $this->assertDatabaseCount('registration_notes', 0);
    }

    #[Test]
    public function notes_from_wrong_event_return_404(): void
    {
        $response = $this->actingAsAdmin(['event_id' => 99, 'org_id' => 99])
            ->post(
                route('admin.registrations.notes.store', $this->registration),
                ['body' => 'From wrong event']
            );

        $response->assertNotFound();
    }

    #[Test]
    public function multiple_notes_are_listed_newest_first(): void
    {
        $first = RegistrationNote::create([
            'event_id' => 1, 'org_id' => 1,
            'registration_id' => $this->registration->id,
            'body' => 'First note',
            'author_name' => 'Admin',
        ]);

        // Bump created_at for ordering
        $first->created_at = now()->subMinute();
        $first->saveQuietly();

        RegistrationNote::create([
            'event_id' => 1, 'org_id' => 1,
            'registration_id' => $this->registration->id,
            'body' => 'Second note',
            'author_name' => 'Admin',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.registrations.show', $this->registration));

        $response->assertOk();
        $content = $response->getContent();

        // Second note (newer) must appear before first note in the page
        $this->assertLessThan(
            strpos($content, 'First note'),
            strpos($content, 'Second note'),
            'Newer note should appear above older note.'
        );
    }
}
