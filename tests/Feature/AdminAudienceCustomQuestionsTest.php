<?php

namespace Tests\Feature;

use App\Forms\Enums\FormAudience;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormResponse;
use App\Forms\Services\AudienceFormSubmissionService;
use App\Models\Event;
use App\Models\Exhibitor;
use App\Models\Group;
use App\Models\Registration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithCustomFormSchema;
use Tests\TestCase;

class AdminAudienceCustomQuestionsTest extends TestCase
{
    use InteractsWithCustomFormSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);
        $this->dropCustomFormTables();
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('exhibitors');
        Schema::dropIfExists('event_groups');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('hash_mappings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('hash', 64)->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();
            $table->index(['event_id', 'org_id']);
            $table->index(['model_type', 'model_id']);
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exhibitors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('company_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_groups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('group_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->createCustomFormTables();

        Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'title' => 'Audience Forms Event',
        ]);
        app()->instance('current.event', Event::query()->find(1));
    }

    protected function tearDown(): void
    {
        $this->dropCustomFormTables();
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('exhibitors');
        Schema::dropIfExists('event_groups');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    #[Test]
    public function audience_forms_validate_and_persist_answers_for_registration_exhibitor_and_group(): void
    {
        Storage::fake('local');

        $registrationForm = $this->createAudienceForm(FormAudience::Registration, 'Favorite color');
        $exhibitorForm = $this->createAudienceForm(FormAudience::Exhibitor, 'Booth note');
        $groupForm = $this->createAudienceForm(FormAudience::Group, 'Delegation size');

        $registration = Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_number' => 'REG-1',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
        ]);
        $exhibitor = Exhibitor::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'company_name' => 'Ada Labs',
        ]);
        $group = Group::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'group_name' => 'VIP Delegation',
        ]);

        $service = app(AudienceFormSubmissionService::class);

        $service->submit(
            FormAudience::Registration,
            $registration,
            $this->requestFor($registrationForm, ['favorite_color' => 'blue']),
            'admin_registration'
        );
        $service->submit(
            FormAudience::Exhibitor,
            $exhibitor,
            $this->requestFor($exhibitorForm, [
                'booth_note' => 'Corner booth',
                'asset' => UploadedFile::fake()->create('plan.pdf', 20, 'application/pdf'),
            ]),
            'admin_exhibitor'
        );
        $service->submit(
            FormAudience::Group,
            $group,
            $this->requestFor($groupForm, ['delegation_size' => '12']),
            'admin_group'
        );

        $this->assertSame(1, $registration->customFormResponses()->count());
        $this->assertSame(1, $exhibitor->customFormResponses()->count());
        $this->assertSame(1, $group->customFormResponses()->count());

        $this->assertDatabaseHas('custom_form_answers', [
            'question_key' => 'favorite_color',
        ]);
        $this->assertDatabaseHas('custom_form_answers', [
            'question_key' => 'booth_note',
        ]);
        $this->assertDatabaseHas('custom_form_answers', [
            'question_key' => 'delegation_size',
        ]);

        $uploadAnswer = CustomFormResponse::query()
            ->where('custom_form_id', $exhibitorForm->id)
            ->with('answers.files')
            ->firstOrFail()
            ->answers
            ->firstWhere('question_key', 'asset');
        $this->assertNotNull($uploadAnswer?->files->first());
        Storage::disk('local')->assertExists($uploadAnswer->files->first()->path);

        try {
            $service->validate(
                FormAudience::Registration,
                $this->requestFor($registrationForm, ['favorite_color' => '']),
                $registration
            );
            $this->fail('Expected validation failure for blank required answer.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                "custom_forms.{$registrationForm->public_id}.favorite_color",
                $exception->errors()
            );
        }
    }

    private function createAudienceForm(FormAudience $audience, string $textLabel): CustomForm
    {
        $form = CustomForm::query()->create([
            'name' => $audience->label().' Form',
            'slug' => $audience->value.'-form',
            'audience' => $audience,
            'is_active' => true,
        ]);

        $form->questions()->create([
            'key' => match ($audience) {
                FormAudience::Registration => 'favorite_color',
                FormAudience::Exhibitor => 'booth_note',
                FormAudience::Group => 'delegation_size',
            },
            'label' => $textLabel,
            'type' => FormQuestionType::Text,
            'is_required' => true,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        if ($audience === FormAudience::Exhibitor) {
            $form->questions()->create([
                'key' => 'asset',
                'label' => 'Asset',
                'type' => FormQuestionType::Upload,
                'is_required' => true,
                'sort_order' => 1,
                'is_active' => true,
                'validation' => ['mimes' => 'pdf', 'max_kb' => 100],
            ]);
        }

        return $form->fresh(['questions.options', 'conditions']);
    }

    /**
     * @param  array<string, mixed>  $answers
     */
    private function requestFor(CustomForm $form, array $answers): Request
    {
        return Request::create('/admin/test', 'POST', [
            'custom_forms' => [
                $form->public_id => collect($answers)
                    ->reject(fn ($value) => $value instanceof UploadedFile)
                    ->all(),
            ],
        ], [], [
            'custom_forms' => [
                $form->public_id => collect($answers)
                    ->filter(fn ($value) => $value instanceof UploadedFile)
                    ->all(),
            ],
        ]);
    }
}
