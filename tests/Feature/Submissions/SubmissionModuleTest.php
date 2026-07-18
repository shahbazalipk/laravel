<?php

namespace Tests\Feature\Submissions;

use App\Models\Event;
use App\Services\ProviderManager;
use App\Submissions\Models\PortalLoginToken;
use App\Submissions\Models\PortalUser;
use App\Submissions\Models\Reviewer;
use App\Submissions\Models\Submission;
use App\Submissions\Models\SubmissionType;
use App\Submissions\Services\DecisionService;
use App\Submissions\Services\PortalAuthenticationService;
use App\Submissions\Services\ReviewAssignmentService;
use App\Submissions\Services\SpeakerConversionService;
use App\Submissions\Services\SubmissionService;
use App\Submissions\Services\SubmissionTypeService;
use App\Submissions\Services\SubmissionWorkflowService;
use Database\Seeders\SubmissionDemoSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithSubmissionSchema;
use Tests\TestCase;

class SubmissionModuleTest extends TestCase
{
    use InteractsWithSubmissionSchema;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('speakers');
        Schema::dropIfExists('events');
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('title');
            $table->string('subdomain')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();
        });
        Schema::create('speakers', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->nullable()->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('onboarding_status')->default('not_started');
            $table->json('profile')->nullable();
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

        config(['event.event_id' => 1, 'event.org_id' => 10]);
        $this->event = Event::query()->create([
            'id' => 1,
            'organization_id' => 10,
            'title' => 'Demo Summit',
            'subdomain' => 'demo-summit',
            'timezone' => 'UTC',
        ]);
        app()->instance('current.event', $this->event);
        $this->createSubmissionTables();
    }

    protected function tearDown(): void
    {
        $this->dropSubmissionTables();
        Schema::dropIfExists('hash_mappings');
        Schema::dropIfExists('speakers');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    #[Test]
    public function submission_types_are_tenant_isolated(): void
    {
        $this->createType();

        config(['event.event_id' => 2, 'event.org_id' => 20]);
        $this->assertSame(0, SubmissionType::query()->count());

        config(['event.event_id' => 1, 'event.org_id' => 10]);
        $this->assertSame(1, SubmissionType::query()->count());
    }

    #[Test]
    public function a_type_is_created_with_default_workflow_and_can_be_published(): void
    {
        $type = $this->createType();
        $this->assertCount(5, $type->workflowStages);
        $this->assertNotNull($type->default_stage_id);

        $section = $type->sections()->create(['title' => 'Proposal', 'slug' => 'proposal']);
        $section->questions()->create([
            'key' => 'abstract',
            'label' => 'Abstract',
            'type' => 'textarea',
            'is_required' => true,
        ]);

        $published = app(SubmissionTypeService::class)->publish($type);

        $this->assertSame('active', $published->status);
        $this->assertSame(1, $published->published_form_version);
        $this->assertNotNull($published->published_at);
        $this->assertDatabaseHas('submission_schema_versions', [
            'submission_type_id' => $type->id,
            'version' => 1,
            'status' => 'published',
        ]);
    }

    #[Test]
    public function publishing_without_an_active_question_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(SubmissionTypeService::class)->publish($this->createType());
    }

    #[Test]
    public function magic_tokens_are_single_use_and_expire(): void
    {
        $user = $this->createPortalUser();
        $service = new PortalAuthenticationService($this->createMock(ProviderManager::class));
        $plain = 'valid-demo-token';
        $user->loginTokens()->create([
            'type' => 'magic_link',
            'token_hash' => hash('sha256', $plain),
            'expires_at' => now()->addMinute(),
        ]);

        $this->assertSame($user->id, $service->consume($plain)->id);
        $this->assertNotNull(PortalLoginToken::query()->firstOrFail()->used_at);

        try {
            $service->consume($plain);
            $this->fail('A replayed token was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('token', $exception->errors());
        }

        $expired = 'expired-demo-token';
        $user->loginTokens()->create([
            'type' => 'magic_link',
            'token_hash' => hash('sha256', $expired),
            'expires_at' => now()->subSecond(),
        ]);

        $this->expectException(ValidationException::class);
        $service->consume($expired);
    }

    #[Test]
    public function a_draft_can_be_completed_and_numbered_once_on_submission(): void
    {
        $type = $this->createType();
        $section = $type->sections()->create(['title' => 'Proposal', 'slug' => 'proposal']);
        $section->questions()->create([
            'key' => 'abstract',
            'label' => 'Abstract',
            'type' => 'textarea',
            'is_required' => true,
        ]);
        $type->forceFill(['status' => 'active'])->save();
        $applicant = $this->createPortalUser();
        $service = app(SubmissionService::class);

        $draft = $service->createDraft($type, $applicant, ['title' => 'Reliable Event Platforms']);
        $service->saveAnswers($draft, ['abstract' => 'A practical architecture session.']);
        $submitted = $service->submit($draft);

        $this->assertFalse($submitted->is_draft);
        $this->assertSame('submitted', $submitted->status->value);
        $this->assertMatchesRegularExpression('/^ABS-'.now()->format('Y').'-0001$/', $submitted->reference);
        $this->assertSame(1, $type->fresh()->number_sequence);
    }

    #[Test]
    public function workflow_guards_require_configured_transitions_and_notes(): void
    {
        $submission = $this->submittedRecord();
        $type = $submission->type;
        $submittedStage = $type->workflowStages()->where('slug', 'submitted')->firstOrFail();
        $reviewStage = $type->workflowStages()->where('slug', 'under-review')->firstOrFail();
        $submission->forceFill(['current_stage_id' => $submittedStage->id])->save();
        $submittedStage->outgoingTransitions()->withTrashed()->where('to_stage_id', $reviewStage->id)->forceDelete();
        $workflow = app(SubmissionWorkflowService::class);

        try {
            $workflow->move($submission, $reviewStage);
            $this->fail('An unconfigured transition was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('stage', $exception->errors());
        }

        $submittedStage->outgoingTransitions()->create([
            'submission_type_id' => $type->id,
            'to_stage_id' => $reviewStage->id,
            'conditions' => ['note_required' => true],
        ]);

        $this->expectException(ValidationException::class);
        $workflow->move($submission->fresh(), $reviewStage);
    }

    #[Test]
    public function conflicted_reviewers_cannot_be_assigned(): void
    {
        $submission = $this->submittedRecord();
        $reviewer = Reviewer::query()->create([
            'name' => 'Review Expert',
            'email' => 'reviewer@example.test',
            'status' => 'active',
        ]);
        $reviewer->conflicts()->create([
            'submission_id' => $submission->id,
            'reason' => 'Same employer',
            'status' => 'active',
        ]);

        $this->expectException(ValidationException::class);
        app(ReviewAssignmentService::class)->assign($submission, $reviewer);
    }

    #[Test]
    public function anonymous_review_hides_identity_answers_but_keeps_reviewable_content(): void
    {
        $type = $this->createType();
        $type->forceFill(['status' => 'active', 'allow_anonymous_review' => true])->save();
        $section = $type->sections()->create(['title' => 'Proposal', 'slug' => 'proposal']);
        $identity = $section->questions()->create([
            'key' => 'speaker_name',
            'label' => 'Speaker identity',
            'type' => 'text',
            'hidden_during_anonymous_review' => true,
        ]);
        $content = $section->questions()->create([
            'key' => 'abstract',
            'label' => 'Public abstract',
            'type' => 'textarea',
        ]);
        $user = $this->createPortalUser('reviewer-login@example.test', ['reviewer']);
        $submission = $type->submissions()->create([
            'portal_user_id' => $user->id,
            'title' => 'Blind Review',
            'status' => 'submitted',
            'is_draft' => false,
        ]);
        $submission->answers()->createMany([
            ['question_id' => $identity->id, 'question_key' => 'speaker_name', 'question_label' => 'Speaker identity', 'question_type' => 'text', 'answer_text' => 'Hidden Person'],
            ['question_id' => $content->id, 'question_key' => 'abstract', 'question_label' => 'Public abstract', 'question_type' => 'textarea', 'answer_text' => 'Visible proposal'],
        ]);
        $reviewer = Reviewer::query()->create([
            'portal_user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
        ]);
        $assignment = $submission->reviewAssignments()->create([
            'reviewer_id' => $reviewer->id,
            'status' => 'assigned',
        ]);

        $this->withSession(['submission_portal_user_id' => $user->id])
            ->get(route('reviewer.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('Visible proposal')
            ->assertDontSee('Hidden Person');
    }

    #[Test]
    public function a_final_decision_is_persisted(): void
    {
        $submission = $this->submittedRecord();
        $submission = app(DecisionService::class)->decide($submission, [
            'decision' => 'selected',
            'reason' => 'Strong fit',
        ]);

        $this->assertTrue($submission->fresh()->is_selected);
        $this->assertDatabaseHas('submission_decisions', ['submission_id' => $submission->id, 'decision' => 'selected']);
    }

    #[Test]
    public function revisions_and_selected_speaker_conversion_are_persisted(): void
    {
        $submission = $this->submittedRecord();
        $submission->forceFill([
            'final_decision' => 'selected',
            'decision_at' => now(),
            'is_selected' => true,
        ])->save();
        $submission->revisions()->create([
            'instructions' => 'Clarify the learning outcomes.',
            'due_at' => now()->addWeek(),
            'revision_number' => 1,
        ]);
        $submission->people()->create([
            'role' => 'primary_speaker',
            'name' => 'Ada Speaker',
            'email' => 'ada@example.test',
            'is_primary' => true,
        ]);

        $speakers = app(SpeakerConversionService::class)->convert($submission);

        $this->assertTrue($submission->fresh()->is_selected);
        $this->assertDatabaseHas('submission_revision_requests', ['submission_id' => $submission->id, 'revision_number' => 1]);
        $this->assertCount(1, $speakers);
        $this->assertSame('ada@example.test', $speakers[0]->email);
        $this->assertDatabaseHas('speaker_submission_links', ['submission_id' => $submission->id, 'speaker_id' => $speakers[0]->id]);
    }

    #[Test]
    public function demo_seeder_creates_a_representative_idempotent_dataset(): void
    {
        $seeder = app(SubmissionDemoSeeder::class);
        $seeder->run();
        $seeder->run();

        $type = SubmissionType::query()->where('code', 'DEMO-ABS')->firstOrFail();
        $this->assertSame(2, $type->sections()->count());
        $this->assertSame(6, $type->workflowStages()->count());
        $this->assertSame(2, $type->submissions()->count());
        $this->assertSame(1, Reviewer::query()->where('email', 'reviewer.demo@example.test')->count());
        $this->assertDatabaseCount('submission_review_assignments', 1);
    }

    private function createType(): SubmissionType
    {
        return app(SubmissionTypeService::class)->create([
            'name' => 'Abstract Submission',
            'code' => 'ABS',
            'slug' => 'abstract-submission',
            'category' => 'abstract',
            'status' => 'draft',
            'timezone' => 'UTC',
            'allow_drafts' => true,
            'allow_anonymous_review' => true,
            'enable_scoring' => true,
            'enable_revisions' => true,
            'enable_speaker_onboarding' => true,
            'number_prefix' => 'ABS',
        ]);
    }

    private function createPortalUser(string $email = 'speaker@example.test', array $roles = ['applicant']): PortalUser
    {
        return PortalUser::query()->create([
            'name' => 'Demo User',
            'email' => $email,
            'status' => 'active',
            'roles' => $roles,
        ]);
    }

    private function submittedRecord(): Submission
    {
        $type = $this->createType();
        $type->forceFill(['status' => 'active'])->save();

        return $type->submissions()->create([
            'portal_user_id' => $this->createPortalUser()->id,
            'current_stage_id' => $type->default_stage_id,
            'title' => 'Demo proposal',
            'status' => 'submitted',
            'is_draft' => false,
            'submitted_at' => now(),
        ]);
    }
}
