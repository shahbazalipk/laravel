<?php

namespace Tests\Feature\Sales;

use App\Models\Event;
use App\Sales\Enums\DealStatus;
use App\Sales\Enums\InquiryFormStatus;
use App\Sales\Enums\SalesFieldType;
use App\Sales\Enums\StageCategory;
use App\Sales\Enums\SubmissionStatus;
use App\Sales\Models\Deal;
use App\Sales\Models\InquirySubmission;
use App\Sales\Models\PipelineType;
use App\Sales\Services\DealService;
use App\Sales\Services\InquiryFormService;
use App\Sales\Services\InquirySubmissionService;
use App\Sales\Services\PipelineService;
use App\Sales\Services\PipelineTypeService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithSalesSchema;
use Tests\TestCase;

class SalesModuleTest extends TestCase
{
    use InteractsWithSalesSchema;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        config(['event.event_id' => 1, 'event.org_id' => 1]);

        $this->dropSalesTables();
        Schema::dropIfExists('landing_page_templates');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('title')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();
        });

        Schema::create('landing_page_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->createSalesTables();

        $this->event = Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'title' => 'Sales Event',
        ]);
        app()->instance('current.event', $this->event);
    }

    protected function tearDown(): void
    {
        $this->dropSalesTables();
        Schema::dropIfExists('landing_page_templates');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    private function actingAsAdmin(): self
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@example.com',
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    private function createType(): PipelineType
    {
        return app(PipelineTypeService::class)->create([
            'name' => 'Sponsorship Sales',
            'slug' => 'sponsorship-sales',
            'is_active' => true,
            'stages' => [
                ['name' => 'New', 'category' => StageCategory::Open->value, 'probability' => 10, 'is_default' => true],
                ['name' => 'Won', 'category' => StageCategory::Won->value, 'probability' => 100],
                ['name' => 'Lost', 'category' => StageCategory::Lost->value, 'probability' => 0],
            ],
            'deal_fields' => [
                ['label' => 'Company', 'key' => 'company', 'type' => SalesFieldType::Text->value, 'is_required' => true],
            ],
        ]);
    }

    #[Test]
    public function pipeline_type_requires_won_and_lost_and_one_default(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(PipelineTypeService::class)->create([
            'name' => 'Broken',
            'stages' => [
                ['name' => 'Only Open', 'category' => StageCategory::Open->value, 'is_default' => true],
            ],
        ]);
    }

    #[Test]
    public function admin_can_create_pipeline_type_via_http(): void
    {
        $this->actingAsAdmin()->post(route('admin.sales.pipeline-types.store'), [
            'name' => 'Group Sales',
            'slug' => 'group-sales',
            'is_active' => '1',
            'stages' => [
                ['name' => 'Lead', 'category' => 'open', 'probability' => 10, 'is_default' => '1'],
                ['name' => 'Won', 'category' => 'won', 'probability' => 100],
                ['name' => 'Lost', 'category' => 'lost', 'probability' => 0],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('sales_pipeline_types', [
            'slug' => 'group-sales',
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    #[Test]
    public function pipeline_copies_stages_from_type_and_deal_can_move(): void
    {
        $type = $this->createType();
        $pipeline = app(PipelineService::class)->createFromType($type, [
            'name' => 'Tech Trip Sponsorship',
            'slug' => 'tech-trip-sponsorship',
        ]);

        $this->assertCount(3, $pipeline->stages);
        $this->assertTrue($pipeline->stages->contains(fn ($s) => $s->is_default));

        $deal = app(DealService::class)->create($pipeline, [
            'title' => 'Acme Gold',
            'value' => 5000,
            'contact' => ['name' => 'Ada', 'email' => 'ada@acme.test', 'is_primary' => true],
        ]);

        $this->assertNotNull($deal->reference);
        $this->assertStringStartsWith('DL-', $deal->reference);

        $won = $pipeline->stages->firstWhere('category', StageCategory::Won);
        app(DealService::class)->moveStage($deal, $won);
        $this->assertSame(DealStatus::Won->value, $deal->fresh()->status->value);
    }

    #[Test]
    public function inquiry_form_publishes_and_accepts_public_submission_then_converts(): void
    {
        $type = $this->createType();
        $pipeline = app(PipelineService::class)->createFromType($type, [
            'name' => 'Exhibitor Pipeline',
            'slug' => 'exhibitor-pipeline',
        ]);

        $form = app(InquiryFormService::class)->create([
            'name' => 'Exhibitor Inquiry',
            'slug' => 'exhibitor-inquiry',
            'sales_pipeline_id' => $pipeline->id,
            'sales_pipeline_type_id' => $type->id,
            'default_stage_id' => $pipeline->stages->firstWhere('is_default')->id,
            'fields' => [
                ['label' => 'Company', 'key' => 'company', 'type' => SalesFieldType::Text->value, 'is_required' => true],
                ['label' => 'Email', 'key' => 'email', 'type' => SalesFieldType::Email->value, 'is_required' => true],
            ],
        ]);

        app(InquiryFormService::class)->publish($form);
        $this->assertSame(InquiryFormStatus::Published, $form->fresh()->status);
        $this->assertNotNull($form->fresh()->embed_token);

        $this->post(route('sales.public.form.store', $form->slug), [
            'answers' => [
                'company' => 'Booth Co',
                'email' => 'hello@booth.test',
            ],
        ])->assertOk();

        $submission = InquirySubmission::query()->firstOrFail();
        $this->assertSame(SubmissionStatus::New, $submission->status);

        $converted = app(InquirySubmissionService::class)->convertToDeal($submission);
        $this->assertNotNull($converted->sales_deal_id);
        $this->assertSame(SubmissionStatus::Converted, $converted->status);
        $this->assertInstanceOf(Deal::class, $converted->deal);
    }

    #[Test]
    public function pipeline_types_are_tenant_isolated(): void
    {
        $this->createType();

        config(['event.event_id' => 2, 'event.org_id' => 2]);
        $this->assertSame(0, PipelineType::query()->count());

        config(['event.event_id' => 1, 'event.org_id' => 1]);
        $this->assertSame(1, PipelineType::query()->count());
    }

    #[Test]
    public function sales_dashboard_is_reachable_for_admin(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.sales.dashboard'))
            ->assertOk()
            ->assertSee('Sales');
    }

    #[Test]
    public function cannot_delete_pipeline_type_when_pipelines_exist(): void
    {
        $type = $this->createType();
        app(PipelineService::class)->createFromType($type, [
            'name' => 'Live Pipeline',
            'slug' => 'live-pipeline',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(PipelineTypeService::class)->delete($type);
    }

    #[Test]
    public function pipeline_type_can_be_duplicated(): void
    {
        $type = $this->createType();
        $copy = app(PipelineTypeService::class)->duplicate($type);

        $this->assertStringContainsString('(Copy)', $copy->name);
        $this->assertCount(3, $copy->stages);
        $this->assertCount(1, $copy->fields);
        $this->assertFalse($copy->is_active);
    }

    #[Test]
    public function inquiry_form_auto_converts_submission_to_deal(): void
    {
        $type = $this->createType();
        $pipeline = app(PipelineService::class)->createFromType($type, [
            'name' => 'Auto Convert Pipeline',
            'slug' => 'auto-convert-pipeline',
        ]);

        $form = app(InquiryFormService::class)->create([
            'name' => 'Auto Form',
            'slug' => 'auto-form',
            'sales_pipeline_id' => $pipeline->id,
            'auto_create_deal' => true,
            'fields' => [
                [
                    'label' => 'Company',
                    'key' => 'company',
                    'type' => SalesFieldType::Text->value,
                    'is_required' => true,
                    'map_to_deal_field' => 'title',
                    'map_to_contact_field' => 'company_name',
                ],
            ],
        ]);
        app(InquiryFormService::class)->publish($form);

        $submission = app(InquirySubmissionService::class)->submit($form->fresh('fields'), [
            'answers' => ['company' => 'Auto Co'],
            'submitter_name' => 'Sam',
            'submitter_email' => 'sam@auto.test',
        ]);

        $this->assertSame(SubmissionStatus::Converted, $submission->fresh()->status);
        $this->assertNotNull($submission->fresh()->sales_deal_id);
        $this->assertSame('Auto Co', Deal::query()->find($submission->sales_deal_id)?->title);
    }

    #[Test]
    public function embed_rejects_disallowed_domains(): void
    {
        $form = app(InquiryFormService::class)->create([
            'name' => 'Embed Form',
            'slug' => 'embed-form',
            'allowed_domains' => 'trusted.example',
            'fields' => [
                ['label' => 'Name', 'key' => 'name', 'type' => SalesFieldType::Text->value, 'is_required' => true],
            ],
        ]);
        app(InquiryFormService::class)->publish($form);
        $token = $form->fresh()->embed_token;

        $this->get(route('sales.public.embed', ['embed_token' => $token, 'parent' => 'evil.test']))
            ->assertForbidden();

        $this->get(route('sales.public.embed', ['embed_token' => $token, 'parent' => 'trusted.example']))
            ->assertOk();
    }

    #[Test]
    public function admin_can_export_submissions_csv(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.sales.submissions.export'))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    #[Test]
    public function deal_move_stage_returns_json_for_kanban(): void
    {
        $type = $this->createType();
        $pipeline = app(PipelineService::class)->createFromType($type, [
            'name' => 'Kanban Pipe',
            'slug' => 'kanban-pipe',
        ]);
        $deal = app(DealService::class)->create($pipeline, [
            'title' => 'Drag Me',
            'value' => 1000,
            'contact' => ['name' => 'Lee', 'email' => 'lee@test.com'],
        ]);
        $won = $pipeline->stages->firstWhere('category', StageCategory::Won);

        $this->actingAsAdmin()
            ->postJson(route('admin.sales.deals.move-stage', $deal), [
                'sales_pipeline_stage_id' => $won->id,
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(DealStatus::Won->value, $deal->fresh()->status->value);
    }

    #[Test]
    public function money_formatting_uses_event_currency(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            if (! Schema::hasColumn('events', 'currency')) {
                $table->string('currency', 10)->nullable();
            }
        });

        $this->event->forceFill(['currency' => 'AED'])->save();
        app()->instance('current.event', $this->event->fresh());

        $this->assertSame('AED', current_event_currency());
        $this->assertSame('AED 1,250', format_money(1250));

        $type = $this->createType();
        $pipeline = app(PipelineService::class)->createFromType($type, [
            'name' => 'Currency Pipeline',
            'slug' => 'currency-pipeline',
        ]);
        $this->assertSame('AED', $pipeline->currency);

        $deal = app(DealService::class)->create($pipeline, [
            'title' => 'Currency Deal',
            'value' => 5000,
        ]);
        $this->assertSame('AED', $deal->currency);

        $this->actingAsAdmin()
            ->get(route('admin.sales.deals.show', $deal))
            ->assertOk()
            ->assertSee('AED 5,000')
            ->assertDontSee('$5,000');
    }
}
