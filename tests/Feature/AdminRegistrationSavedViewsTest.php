<?php

namespace Tests\Feature;

use App\Forms\Enums\FormAudience;
use App\Forms\Enums\FormConditionAction;
use App\Forms\Enums\FormConditionOperator;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Enums\FormResponseStatus;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormAnswerFile;
use App\Forms\Models\CustomFormCondition;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Models\CustomFormQuestionOption;
use App\Models\Event;
use App\Models\Registration;
use App\Registration\Enums\RegistrationSavedViewVisibility;
use App\Registration\Models\RegistrationSavedView;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithCustomFormSchema;
use Tests\TestCase;

class AdminRegistrationSavedViewsTest extends TestCase
{
    use InteractsWithCustomFormSchema;

    private int $ownerId;

    private int $colleagueId;

    private int $outsiderId;

    private CustomFormQuestion $mealQuestion;

    private CustomFormQuestion $allergyQuestion;

    private CustomFormQuestion $photoQuestion;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'event.event_id' => 1,
            'event.org_id' => 1,
            'event.name' => 'Test Event',
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
            'modules.finance.enabled' => false,
            'modules.projects.enabled' => false,
        ]);

        Schema::dropIfExists('registration_saved_view_shares');
        Schema::dropIfExists('registration_saved_views');
        Schema::dropIfExists('organization_admin_users');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('registration_drafts');
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

        Schema::create('registration_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('registration_statuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('registration_category_id')->nullable();
            $table->unsignedBigInteger('registration_status_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('payment_status')->nullable();
            $table->boolean('checked_in')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('registration_drafts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('email');
            $table->string('resume_token_hash', 64);
            $table->text('payload')->nullable();
            $table->string('current_step')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_admin_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_primary_admin')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        (require database_path('migrations/2026_08_14_100000_create_registration_saved_views_tables.php'))->up();
        $this->createCustomFormTables();

        Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'org_id' => 1,
            'name' => 'Views Event',
            'title' => 'Views Event',
            'currency' => 'AED',
        ]);
        app()->instance('current.event', Event::query()->find(1));

        DB::table('registration_categories')->insert([
            'event_id' => 1,
            'org_id' => 1,
            'name' => 'General',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->ownerId = $this->insertAdmin('Owner Admin', 'owner@example.com');
        $this->colleagueId = $this->insertAdmin('Colleague Admin', 'colleague@example.com');
        $this->outsiderId = $this->insertAdmin('Outsider Admin', 'outsider@example.com');

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

        $this->allergyQuestion = CustomFormQuestion::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'key' => 'allergy_notes',
            'label' => 'Allergy notes',
            'type' => FormQuestionType::Text,
            'is_required' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->photoQuestion = CustomFormQuestion::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'key' => 'photo',
            'label' => 'Photo',
            'type' => FormQuestionType::Upload,
            'is_required' => false,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        CustomFormCondition::query()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'source_question_id' => $this->mealQuestion->id,
            'target_question_id' => $this->allergyQuestion->id,
            'operator' => FormConditionOperator::Equals,
            'compare_value' => ['value' => 'veg'],
            'action' => FormConditionAction::Show,
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registration_saved_view_shares');
        Schema::dropIfExists('registration_saved_views');
        Schema::dropIfExists('organization_admin_users');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('registration_categories');
        Schema::dropIfExists('registration_statuses');
        Schema::dropIfExists('registration_drafts');
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('events');
        $this->dropCustomFormTables();
        parent::tearDown();
    }

    #[Test]
    public function registrations_index_shows_saved_views_bar_and_default_columns(): void
    {
        $this->makeRegistration();

        $response = $this->actingAsAdmin($this->ownerId)
            ->get(route('admin.registrations.index', ['stage' => 'registered']));

        $response->assertOk();
        $response->assertSee('data-testid="saved-views-bar"', false);
        $response->assertSee('Default columns');
        $response->assertSee('data-testid="column-header-std:reference"', false);
        $response->assertSee('data-testid="column-header-std:payment"', false);
        $response->assertDontSee('data-testid="column-header-std:company"', false);
        $response->assertSee('data-testid="column-std:job_title"', false);
        $response->assertSee('Meal preference');
        $response->assertSee('Allergy notes');
        $response->assertSee('Conditional question');
    }

    #[Test]
    public function admin_can_save_a_private_view_with_custom_question_columns(): void
    {
        $this->makeRegistrationWithAnswers();

        $response = $this->actingAsAdmin($this->ownerId)
            ->post(route('admin.registrations.views.store'), [
                'name' => 'Check-in desk',
                'visibility' => RegistrationSavedViewVisibility::Private->value,
                'columns' => [
                    'std:reference',
                    'std:name',
                    'std:company',
                    'q:'.$this->mealQuestion->public_id,
                    'q:'.$this->allergyQuestion->public_id,
                ],
            ]);

        $view = RegistrationSavedView::query()->where('name', 'Check-in desk')->first();
        $this->assertNotNull($view);
        $this->assertSame($this->ownerId, (int) $view->organization_admin_user_id);
        $this->assertSame(RegistrationSavedViewVisibility::Private, $view->visibility);
        $this->assertContains('q:'.$this->mealQuestion->public_id, $view->columns);

        $response->assertRedirect(route('admin.registrations.index', ['view' => $view->public_id]));

        $listing = $this->actingAsAdmin($this->ownerId)
            ->get(route('admin.registrations.index', [
                'stage' => 'registered',
                'view' => $view->public_id,
            ]));

        $listing->assertOk();
        $listing->assertSee('Check-in desk');
        $listing->assertSee('data-testid="column-header-std:company"', false);
        $listing->assertSee('data-testid="column-header-q:'.$this->mealQuestion->public_id.'"', false);
        $listing->assertDontSee('data-testid="column-header-std:email"', false);
        $listing->assertSee('Acme Corp');
        $listing->assertSee('Vegetarian');
        $listing->assertSee('Peanuts');
    }

    #[Test]
    public function public_view_is_visible_to_other_event_users(): void
    {
        $view = $this->createView($this->ownerId, 'Shared board', RegistrationSavedViewVisibility::Public);

        $this->actingAsAdmin($this->colleagueId)
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertSee('Shared board')
            ->assertSee($view->public_id);
    }

    #[Test]
    public function private_view_is_hidden_from_other_event_users(): void
    {
        $view = $this->createView($this->ownerId, 'My private desk', RegistrationSavedViewVisibility::Private);

        $this->actingAsAdmin($this->colleagueId)
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertDontSee('My private desk')
            ->assertDontSee($view->public_id);
    }

    #[Test]
    public function specific_people_view_is_visible_only_to_chosen_users(): void
    {
        $view = $this->createView(
            $this->ownerId,
            'Ops team',
            RegistrationSavedViewVisibility::Users,
            [$this->colleagueId]
        );

        $this->actingAsAdmin($this->colleagueId)
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertSee('Ops team');

        $this->actingAsAdmin($this->outsiderId)
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertDontSee('Ops team')
            ->assertDontSee($view->public_id);
    }

    #[Test]
    public function sharing_with_specific_people_requires_at_least_one_event_user(): void
    {
        $this->actingAsAdmin($this->ownerId)
            ->from(route('admin.registrations.index'))
            ->post(route('admin.registrations.views.store'), [
                'name' => 'Empty share',
                'visibility' => RegistrationSavedViewVisibility::Users->value,
                'columns' => ['std:reference', 'std:name'],
                'share_user_ids' => [],
            ])
            ->assertRedirect(route('admin.registrations.index'))
            ->assertSessionHasErrors('share_user_ids');
    }

    #[Test]
    public function default_view_is_applied_when_no_view_query_is_present(): void
    {
        $this->makeRegistration();
        $this->createView(
            $this->ownerId,
            'Company first',
            RegistrationSavedViewVisibility::Private,
            [],
            ['std:reference', 'std:company'],
            isDefault: true
        );

        $this->actingAsAdmin($this->ownerId)
            ->get(route('admin.registrations.index', ['stage' => 'registered']))
            ->assertOk()
            ->assertSee('Company first')
            ->assertSee('data-testid="column-header-std:company"', false)
            ->assertDontSee('data-testid="column-header-std:email"', false);
    }

    #[Test]
    public function non_owner_cannot_update_or_delete_a_view(): void
    {
        $view = $this->createView($this->ownerId, 'Locked view', RegistrationSavedViewVisibility::Public);

        $this->actingAsAdmin($this->colleagueId)
            ->put(route('admin.registrations.views.update', $view), [
                'name' => 'Hijacked',
                'visibility' => RegistrationSavedViewVisibility::Private->value,
                'columns' => ['std:name'],
            ])
            ->assertForbidden();

        $this->actingAsAdmin($this->colleagueId)
            ->delete(route('admin.registrations.views.destroy', $view))
            ->assertForbidden();

        $this->assertDatabaseHas('registration_saved_views', [
            'id' => $view->id,
            'name' => 'Locked view',
        ]);
    }

    #[Test]
    public function registrations_index_exposes_export_for_the_active_view(): void
    {
        $this->makeRegistration();

        $this->actingAsAdmin($this->ownerId)
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertSee('data-testid="export-saved-view"', false)
            ->assertSee('Export CSV');
    }

    #[Test]
    public function admin_can_export_a_saved_view_as_csv(): void
    {
        $this->makeRegistrationWithAnswers();
        $view = $this->createView(
            $this->ownerId,
            'Check-in desk',
            RegistrationSavedViewVisibility::Private,
            [],
            [
                'std:reference',
                'std:name',
                'std:company',
                'std:payment',
                'q:'.$this->mealQuestion->public_id,
                'q:'.$this->allergyQuestion->public_id,
            ]
        );

        $response = $this->actingAsAdmin($this->ownerId)
            ->get(route('admin.registrations.views.export', [
                'view' => $view->public_id,
                'stage' => 'registered',
            ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('check-in-desk', (string) $response->headers->get('content-disposition'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Reference', $csv);
        $this->assertStringContainsString('Company', $csv);
        $this->assertStringContainsString('Meal preference', $csv);
        $this->assertStringContainsString('Allergy notes', $csv);
        $this->assertStringContainsString('REG-VIEW-1', $csv);
        $this->assertStringContainsString('Ada Lovelace', $csv);
        $this->assertStringContainsString('Acme Corp', $csv);
        $this->assertStringContainsString('Paid', $csv);
        $this->assertStringContainsString('Vegetarian', $csv);
        $this->assertStringContainsString('Peanuts', $csv);
        $this->assertStringNotContainsString('ada@example.com', $csv);
    }

    #[Test]
    public function saved_view_export_respects_listing_filters(): void
    {
        $this->makeRegistration([
            'registration_number' => 'REG-ADA',
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
        ]);
        $this->makeRegistration([
            'registration_number' => 'REG-GRACE',
            'email' => 'grace@example.com',
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'company_name' => 'Navy',
        ]);

        $csv = $this->actingAsAdmin($this->ownerId)
            ->get(route('admin.registrations.views.export', [
                'stage' => 'registered',
                'search' => 'grace@example.com',
            ]))
            ->streamedContent();

        $this->assertStringContainsString('REG-GRACE', $csv);
        $this->assertStringContainsString('Grace Hopper', $csv);
        $this->assertStringNotContainsString('REG-ADA', $csv);
        $this->assertStringNotContainsString('Ada Lovelace', $csv);
    }

    #[Test]
    public function private_view_export_is_not_available_to_other_event_users(): void
    {
        $this->makeRegistration();
        $view = $this->createView(
            $this->ownerId,
            'My private desk',
            RegistrationSavedViewVisibility::Private,
            [],
            ['std:company']
        );

        $csv = $this->actingAsAdmin($this->colleagueId)
            ->get(route('admin.registrations.views.export', [
                'view' => $view->public_id,
                'stage' => 'registered',
            ]))
            ->streamedContent();

        $this->assertStringContainsString('Reference,Name,Email,Category,Stage,Payment', $csv);
        $this->assertStringNotContainsString('My private desk', (string) $this->actingAsAdmin($this->colleagueId)
            ->get(route('admin.registrations.views.export', ['view' => $view->public_id]))
            ->headers->get('content-disposition'));
    }

    #[Test]
    public function upload_answers_use_full_clickable_urls_in_the_list_and_csv(): void
    {
        $this->makeRegistrationWithAnswers();
        $file = CustomFormAnswerFile::query()->first();
        $this->assertNotNull($file);
        $downloadUrl = route('admin.custom-form-answer-files.download', $file);

        $view = $this->createView(
            $this->ownerId,
            'Photo desk',
            RegistrationSavedViewVisibility::Private,
            [],
            ['std:reference', 'q:'.$this->photoQuestion->public_id]
        );

        $listing = $this->actingAsAdmin($this->ownerId)
            ->get(route('admin.registrations.index', [
                'stage' => 'registered',
                'view' => $view->public_id,
            ]));

        $listing->assertOk();
        $listing->assertSee($downloadUrl, false);
        $listing->assertSee('href="'.$downloadUrl.'"', false);
        $listing->assertDontSee('>IMG-20260811-WA0160.jpg<', false);

        $csv = $this->actingAsAdmin($this->ownerId)
            ->get(route('admin.registrations.views.export', [
                'view' => $view->public_id,
                'stage' => 'registered',
            ]))
            ->streamedContent();

        $this->assertStringContainsString($downloadUrl, $csv);
        $this->assertStringNotContainsString('IMG-20260811-WA0160.jpg', $csv);
    }

    private function actingAsAdmin(int $adminId)
    {
        $admin = DB::table('organization_admin_users')->where('id', $adminId)->first();

        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $adminId,
            'admin_name' => $admin->name,
            'admin_email' => $admin->email,
            'admin_type' => 'event_admin',
            'admin_is_primary' => true,
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    private function insertAdmin(string $name, string $email): int
    {
        return (int) DB::table('organization_admin_users')->insertGetId([
            'organization_id' => 1,
            'name' => $name,
            'email' => $email,
            'password' => 'secret',
            'is_primary_admin' => false,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<int, int>  $shareIds
     * @param  array<int, string>|null  $columns
     */
    private function createView(
        int $ownerId,
        string $name,
        RegistrationSavedViewVisibility $visibility,
        array $shareIds = [],
        ?array $columns = null,
        bool $isDefault = false
    ): RegistrationSavedView {
        $view = RegistrationSavedView::query()->create([
            'organization_admin_user_id' => $ownerId,
            'name' => $name,
            'visibility' => $visibility,
            'is_default' => $isDefault,
            'columns' => $columns ?? ['std:reference', 'std:name'],
        ]);

        foreach ($shareIds as $adminId) {
            $view->shares()->create([
                'organization_admin_user_id' => $adminId,
            ]);
        }

        return $view->load('shares');
    }

    private function makeRegistration(array $overrides = []): Registration
    {
        return Registration::query()->create(array_merge([
            'event_id' => 1,
            'org_id' => 1,
            'registration_category_id' => 1,
            'registration_number' => 'REG-VIEW-1',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'phone' => '0500000000',
            'company_name' => 'Acme Corp',
            'registration_type' => 'individual',
            'payment_status' => 'paid',
        ], $overrides));
    }

    private function makeRegistrationWithAnswers(): Registration
    {
        $registration = $this->makeRegistration();
        $form = CustomForm::query()->first();

        $response = $registration->customFormResponses()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_id' => $form->id,
            'status' => FormResponseStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $response->answers()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_question_id' => $this->mealQuestion->id,
            'question_key' => 'meal_preference',
            'question_label' => 'Meal preference',
            'question_type' => FormQuestionType::Radio,
            'value' => ['value' => 'veg'],
        ]);
        $response->answers()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_question_id' => $this->allergyQuestion->id,
            'question_key' => 'allergy_notes',
            'question_label' => 'Allergy notes',
            'question_type' => FormQuestionType::Text,
            'value' => ['value' => 'Peanuts'],
        ]);
        $photoAnswer = $response->answers()->create([
            'event_id' => 1,
            'org_id' => 1,
            'custom_form_question_id' => $this->photoQuestion->id,
            'question_key' => 'photo',
            'question_label' => 'Photo',
            'question_type' => FormQuestionType::Upload,
            'value' => ['original_name' => 'IMG-20260811-WA0160.jpg'],
        ]);
        $photoAnswer->files()->create([
            'event_id' => 1,
            'org_id' => 1,
            'disk' => 'local',
            'path' => 'custom-form-answers/photo.jpg',
            'original_name' => 'IMG-20260811-WA0160.jpg',
            'mime' => 'image/jpeg',
            'size' => 1200,
        ]);

        return $registration;
    }
}
