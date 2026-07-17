<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminRegistrationDraftShowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);

        Schema::dropIfExists('registration_drafts');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_drafts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id')->nullable();
            $table->unsignedBigInteger('event_url_id')->nullable();
            $table->string('email');
            $table->string('resume_token_hash', 64);
            $table->text('payload');
            $table->string('current_step');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('otp_hash', 255)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_last_sent_at')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'org_id' => 1,
            'name' => 'Test Event',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registration_drafts');
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
    public function draft_detail_shows_saved_profile_photo(): void
    {
        $path = 'registrations/drafts/demo/photo.jpg';

        $draft = RegistrationDraft::query()->create([
            'public_id' => (string) Str::uuid(),
            'event_id' => 1,
            'org_id' => 1,
            'email' => 'draft-photo@example.com',
            'resume_token_hash' => hash('sha256', 'token'),
            'payload' => [
                'email' => 'draft-photo@example.com',
                'first_name' => 'Draft',
                'last_name' => 'Photo',
                'profile_picture' => $path,
            ],
            'current_step' => RegistrationWizardStep::Information,
            'email_verified_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.registration-drafts.show', $draft));

        $response->assertOk();
        $response->assertSee('data-testid="draft-profile-photo"', false);
        $response->assertSee('storage/'.$path, false);
        $response->assertSee('Draft Photo');
    }
}
