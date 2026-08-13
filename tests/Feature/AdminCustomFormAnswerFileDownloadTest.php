<?php

namespace Tests\Feature;

use App\Forms\Enums\FormAudience;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Enums\FormResponseStatus;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormAnswerFile;
use App\Forms\Models\CustomFormQuestion;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithCustomFormSchema;
use Tests\TestCase;

class AdminCustomFormAnswerFileDownloadTest extends TestCase
{
    use InteractsWithCustomFormSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
        ]);

        Schema::dropIfExists('registrations');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');
        $this->dropCustomFormTables();

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('name')->nullable();
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
            $table->string('registration_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->createCustomFormTables();

        Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'org_id' => 1,
            'name' => 'Download Event',
        ]);
        app()->instance('current.event', Event::query()->find(1));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');
        $this->dropCustomFormTables();
        parent::tearDown();
    }

    #[Test]
    public function download_prefixes_the_filename_with_the_registration_number(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('custom-form-answers/photo.jpg', 'fake-image');

        $file = $this->makeRegistrationUpload('REG-4412', 'IMG-20260811-WA0160.jpg');
        $file->load('answer.response.respondent');

        $this->assertSame('REG-4412-IMG-20260811-WA0160.jpg', $file->downloadName());

        $this->actingAsAdmin()
            ->get(route('admin.custom-form-answer-files.download', $file))
            ->assertOk()
            ->assertDownload('REG-4412-IMG-20260811-WA0160.jpg');
    }

    private function makeRegistrationUpload(string $registrationNumber, string $originalName): CustomFormAnswerFile
    {
        $registration = Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_number' => $registrationNumber,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
        ]);

        $form = CustomForm::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Registration Survey',
            'slug' => 'registration-survey',
            'audience' => FormAudience::Registration,
            'is_active' => true,
        ]);

        $question = CustomFormQuestion::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'key' => 'photo',
            'label' => 'Photo',
            'type' => FormQuestionType::Upload,
            'is_required' => false,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $registration->customFormResponses()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'status' => FormResponseStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $answer = $response->answers()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_question_id' => $question->id,
            'question_key' => 'photo',
            'question_label' => 'Photo',
            'question_type' => FormQuestionType::Upload,
            'value' => ['original_name' => $originalName],
        ]);

        return $answer->files()->create([
            'event_id' => 1,
            'org_id' => 1,
            'disk' => 'local',
            'path' => 'custom-form-answers/photo.jpg',
            'original_name' => $originalName,
            'mime' => 'image/jpeg',
            'size' => 1200,
        ]);
    }

    private function actingAsAdmin()
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_name' => 'Test Admin',
            'admin_email' => 'admin@test.com',
            'admin_type' => 'event_admin',
            'admin_is_primary' => true,
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }
}
