<?php

namespace Tests\Feature;

use App\Forms\Enums\FormAudience;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Enums\FormResponseStatus;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormAnswer;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Models\CustomFormQuestionOption;
use App\Forms\Models\CustomFormResponse;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithCustomFormSchema;
use Tests\TestCase;

class AdminCustomQuestionsReportTest extends TestCase
{
    use InteractsWithCustomFormSchema;

    private CustomFormQuestion $mealQuestion;

    private CustomFormQuestion $interestQuestion;

    private CustomFormQuestion $notesQuestion;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'event.currency' => 'AED',
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

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
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
            'name' => 'Questions Event',
            'title' => 'Questions Event',
            'currency' => 'AED',
        ]);
        app()->instance('current.event', Event::query()->find(1));

        $form = CustomForm::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'Registration Survey',
            'slug' => 'registration-survey',
            'audience' => FormAudience::Registration,
            'is_active' => true,
        ]);

        $this->mealQuestion = CustomFormQuestion::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'key' => 'meal_preference',
            'label' => 'Meal preference',
            'type' => FormQuestionType::Radio,
            'is_required' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        CustomFormQuestionOption::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_question_id' => $this->mealQuestion->id,
            'value' => 'veg',
            'label' => 'Vegetarian',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        CustomFormQuestionOption::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_question_id' => $this->mealQuestion->id,
            'value' => 'non_veg',
            'label' => 'Non-vegetarian',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->interestQuestion = CustomFormQuestion::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'key' => 'interests',
            'label' => 'Areas of interest',
            'type' => FormQuestionType::Checkbox,
            'is_required' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);
        CustomFormQuestionOption::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_question_id' => $this->interestQuestion->id,
            'value' => 'ai',
            'label' => 'AI',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        CustomFormQuestionOption::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_question_id' => $this->interestQuestion->id,
            'value' => 'cloud',
            'label' => 'Cloud',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->notesQuestion = CustomFormQuestion::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'key' => 'notes',
            'label' => 'Additional notes',
            'type' => FormQuestionType::Text,
            'is_required' => false,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $registrationA = Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
        ]);
        $registrationB = Registration::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace@example.com',
        ]);

        $responseA = CustomFormResponse::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'respondent_type' => Registration::class,
            'respondent_id' => $registrationA->id,
            'status' => FormResponseStatus::Submitted,
            'submitted_at' => now()->subDay(),
        ]);
        $responseB = CustomFormResponse::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'respondent_type' => Registration::class,
            'respondent_id' => $registrationB->id,
            'status' => FormResponseStatus::Submitted,
            'submitted_at' => now()->subHours(3),
        ]);

        CustomFormAnswer::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_response_id' => $responseA->id,
            'custom_form_question_id' => $this->mealQuestion->id,
            'question_key' => 'meal_preference',
            'question_label' => 'Meal preference',
            'question_type' => FormQuestionType::Radio,
            'value' => ['value' => 'veg'],
        ]);
        CustomFormAnswer::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_response_id' => $responseB->id,
            'custom_form_question_id' => $this->mealQuestion->id,
            'question_key' => 'meal_preference',
            'question_label' => 'Meal preference',
            'question_type' => FormQuestionType::Radio,
            'value' => ['value' => 'veg'],
        ]);
        CustomFormAnswer::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_response_id' => $responseA->id,
            'custom_form_question_id' => $this->interestQuestion->id,
            'question_key' => 'interests',
            'question_label' => 'Areas of interest',
            'question_type' => FormQuestionType::Checkbox,
            'value' => ['values' => ['ai', 'cloud']],
        ]);
        CustomFormAnswer::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_response_id' => $responseB->id,
            'custom_form_question_id' => $this->interestQuestion->id,
            'question_key' => 'interests',
            'question_label' => 'Areas of interest',
            'question_type' => FormQuestionType::Checkbox,
            'value' => ['values' => ['ai']],
        ]);
        CustomFormAnswer::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_response_id' => $responseA->id,
            'custom_form_question_id' => $this->notesQuestion->id,
            'question_key' => 'notes',
            'question_label' => 'Additional notes',
            'question_type' => FormQuestionType::Text,
            'value' => ['value' => 'Looking forward to the keynote'],
        ]);
        CustomFormAnswer::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_response_id' => $responseB->id,
            'custom_form_question_id' => $this->notesQuestion->id,
            'question_key' => 'notes',
            'question_label' => 'Additional notes',
            'question_type' => FormQuestionType::Text,
            'value' => ['value' => 'Looking forward to the keynote'],
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');
        $this->dropCustomFormTables();
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
    public function custom_questions_report_shows_charts_and_text_samples(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('data-testid="reports-card-questions"', false);

        $this->actingAsAdmin()
            ->get(route('admin.reports.questions'))
            ->assertOk()
            ->assertSee('data-testid="reports-questions-page"', false)
            ->assertSee('Registration Survey')
            ->assertSee('Meal preference')
            ->assertSee('Vegetarian')
            ->assertSee('Areas of interest')
            ->assertSee('Looking forward to the keynote')
            ->assertSee('data-testid="reports-question-chart-'.$this->mealQuestion->id.'"', false);
    }

    #[Test]
    public function custom_questions_csv_export_contains_aggregated_answers(): void
    {
        $csv = $this->actingAsAdmin()
            ->get(route('admin.reports.questions.export'))
            ->assertOk()
            ->assertHeader('content-disposition')
            ->streamedContent();

        $this->assertStringContainsString('Meal preference', $csv);
        $this->assertStringContainsString('Vegetarian', $csv);
        $this->assertStringContainsString('AI', $csv);
        $this->assertStringContainsString('Looking forward to the keynote', $csv);
    }
}
