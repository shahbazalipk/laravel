<?php

namespace Tests\Feature;

use App\Forms\Enums\FormAudience;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormQuestion;
use App\Models\Event;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithCustomFormSchema;
use Tests\TestCase;

class AdminCustomFormBuilderTest extends TestCase
{
    use InteractsWithCustomFormSchema;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        config(['event.event_id' => 1, 'event.org_id' => 1]);
        $this->dropCustomFormTables();
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
        $this->createCustomFormTables();
        $this->event = Event::query()->create([
            'id' => 1,
            'organization_id' => 1,
            'title' => 'Builder Event',
        ]);
        app()->instance('current.event', $this->event);
    }

    protected function tearDown(): void
    {
        $this->dropCustomFormTables();
        Schema::dropIfExists('landing_page_templates');
        Schema::dropIfExists('events');
        parent::tearDown();
    }

    public static function audienceCases(): array
    {
        return [
            'registration' => [FormAudience::Registration],
            'exhibitor' => [FormAudience::Exhibitor],
            'group' => [FormAudience::Group],
        ];
    }

    #[Test]
    #[DataProvider('audienceCases')]
    public function admin_can_create_update_and_delete_forms_for_each_audience(FormAudience $audience): void
    {
        $name = ucfirst($audience->value).' Questions';
        $create = $this->actingAsAdmin()->post(route('admin.custom-forms.store'), [
            'name' => $name,
            'slug' => $audience->value.'-questions',
            'description' => 'Questions for '.$audience->value,
            'audience' => $audience->value,
            'is_active' => '1',
        ]);

        $form = CustomForm::query()->where('audience', $audience)->firstOrFail();
        $create->assertRedirect(route('admin.custom-forms.edit', $form));
        $this->assertSame($name, $form->name);

        $this->actingAsAdmin()->put(route('admin.custom-forms.update', $form), [
            'name' => $name.' Updated',
            'slug' => $audience->value.'-questions-updated',
            'description' => 'Updated',
            'audience' => $audience->value,
            'is_active' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('custom_forms', [
            'id' => $form->id,
            'name' => $name.' Updated',
            'is_active' => false,
        ]);

        $this->actingAsAdmin()->delete(route('admin.custom-forms.destroy', $form))
            ->assertRedirect(route('admin.custom-forms.index'));
        $this->assertSoftDeleted('custom_forms', ['id' => $form->id]);
    }

    #[Test]
    public function builder_supports_every_question_type_options_conditions_updates_reordering_and_deletion(): void
    {
        $form = $this->createForm();
        $types = [
            FormQuestionType::Text,
            FormQuestionType::Textarea,
            FormQuestionType::Radio,
            FormQuestionType::Select,
            FormQuestionType::Checkbox,
            FormQuestionType::Upload,
        ];

        foreach ($types as $index => $type) {
            $payload = [
                'label' => $type->label().' question',
                'key' => $type->value.'_question',
                'type' => $type->value,
                'is_required' => $index % 2 === 0 ? '1' : '0',
                'validation' => $type === FormQuestionType::Upload
                    ? ['mimes' => 'pdf, png', 'max_kb' => 1024]
                    : ['min' => 0, 'max' => 255],
            ];
            if ($type->hasOptions()) {
                $payload['options'] = [
                    ['label' => 'First', 'value' => 'first'],
                    ['label' => 'Second', 'value' => 'second'],
                ];
            }
            if ($index === 1) {
                $source = $form->questions()->firstOrFail();
                $payload['condition'] = [
                    'source_question' => $source->public_id,
                    'operator' => 'equals',
                    'compare_value' => 'show',
                    'action' => 'show',
                ];
            }

            $this->actingAsAdmin()
                ->post(route('admin.custom-forms.questions.store', $form), $payload)
                ->assertSessionHasNoErrors();
        }

        $form->refresh();
        $this->assertCount(6, $form->questions);
        $this->assertSame(6, $form->questions->pluck('type')->unique()->count());
        $this->assertSame(6, $form->questions->flatMap->options->count());
        $this->assertCount(1, $form->conditions);

        $radio = $form->questions->firstWhere('type', FormQuestionType::Radio);
        $this->assertInstanceOf(CustomFormQuestion::class, $radio);
        $this->actingAsAdmin()->put(route('admin.custom-forms.questions.update', [$form, $radio]), [
            'label' => 'Updated radio',
            'key' => $radio->key,
            'type' => 'radio',
            'is_required' => '1',
            'options' => [
                ['label' => 'Yes', 'value' => 'yes'],
                ['label' => 'No', 'value' => 'no'],
            ],
        ])->assertSessionHasNoErrors();
        $this->assertSame(['yes', 'no'], $radio->fresh()->options->pluck('value')->all());

        $last = CustomFormQuestion::query()
            ->where('custom_form_id', $form->id)
            ->orderByDesc('sort_order')
            ->firstOrFail();
        $previousOrder = $last->sort_order;
        $this->actingAsAdmin()->patch(
            route('admin.custom-forms.questions.reorder', [$form, $last]),
            ['direction' => 'up']
        )->assertSessionHasNoErrors();
        $this->assertSame($previousOrder - 1, $last->fresh()->sort_order);

        $this->actingAsAdmin()->delete(route('admin.custom-forms.questions.destroy', [$form, $last]))
            ->assertSessionHasNoErrors();
        $this->assertSoftDeleted('custom_form_questions', ['id' => $last->id]);

        $this->actingAsAdmin()->get(route('admin.custom-forms.edit', $form))
            ->assertOk()
            ->assertSee('data-testid="add-question-panel"', false)
            ->assertSee('Updated radio');
    }

    #[Test]
    public function help_text_uses_a_textarea_and_sanitizes_supported_html(): void
    {
        $form = $this->createForm();

        $this->actingAsAdmin()
            ->post(route('admin.custom-forms.questions.store', $form), [
                'label' => 'Payment receipt',
                'key' => 'payment_receipt',
                'type' => FormQuestionType::Text->value,
                'is_required' => '0',
                'help_text' => '<h3>Bank details</h3><p>Pay to <strong>Account 123</strong>.</p><script>alert("xss")</script>',
            ])
            ->assertSessionHasNoErrors();

        $question = $form->questions()->where('key', 'payment_receipt')->firstOrFail();
        $this->assertStringContainsString('<h3>Bank details</h3>', $question->help_text);
        $this->assertStringContainsString('<strong>Account 123</strong>', $question->help_text);
        $this->assertStringNotContainsString('<script', $question->help_text);

        $this->actingAsAdmin()
            ->get(route('admin.custom-forms.edit', $form))
            ->assertOk()
            ->assertSee('<textarea id="help-'.$question->public_id.'"', false)
            ->assertSee('Safe HTML is supported');
    }

    #[Test]
    public function a_condition_source_from_another_form_is_rejected(): void
    {
        $form = $this->createForm('Primary', 'primary', FormAudience::Registration);
        $other = $this->createForm('Other', 'other', FormAudience::Exhibitor);
        $foreignSource = $other->questions()->create([
            'label' => 'Foreign source',
            'key' => 'foreign_source',
            'type' => FormQuestionType::Text,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->actingAsAdmin()
            ->from(route('admin.custom-forms.edit', $form))
            ->post(route('admin.custom-forms.questions.store', $form), [
                'label' => 'Target',
                'key' => 'target',
                'type' => 'text',
                'is_required' => '0',
                'condition' => [
                    'source_question' => $foreignSource->public_id,
                    'operator' => 'equals',
                    'compare_value' => 'yes',
                    'action' => 'show',
                ],
            ])
            ->assertRedirect(route('admin.custom-forms.edit', $form))
            ->assertSessionHasErrors('condition.source_question');

        $this->assertDatabaseMissing('custom_form_questions', [
            'custom_form_id' => $form->id,
            'key' => 'target',
        ]);
    }

    #[Test]
    public function only_one_live_form_is_allowed_per_audience_and_soft_delete_frees_the_slot(): void
    {
        $this->actingAsAdmin()->post(route('admin.custom-forms.store'), [
            'name' => 'Registration Questions',
            'slug' => 'registration-questions',
            'audience' => FormAudience::Registration->value,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $form = CustomForm::query()->where('audience', FormAudience::Registration)->firstOrFail();
        $this->assertSame(FormAudience::Registration->value, $form->audience_unique);

        $this->actingAsAdmin()
            ->from(route('admin.custom-forms.create'))
            ->post(route('admin.custom-forms.store'), [
                'name' => 'Second Registration',
                'slug' => 'second-registration',
                'audience' => FormAudience::Registration->value,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.custom-forms.create'))
            ->assertSessionHasErrors('audience');

        $this->actingAsAdmin()->get(route('admin.custom-forms.index'))
            ->assertOk()
            ->assertSee('data-testid="create-custom-form"', false);

        $this->actingAsAdmin()->delete(route('admin.custom-forms.destroy', $form))
            ->assertRedirect(route('admin.custom-forms.index'));
        $this->assertSoftDeleted('custom_forms', ['id' => $form->id]);
        $this->assertDatabaseHas('custom_forms', [
            'id' => $form->id,
            'audience_unique' => null,
        ]);

        $this->actingAsAdmin()->post(route('admin.custom-forms.store'), [
            'name' => 'Replacement Registration',
            'slug' => 'replacement-registration',
            'audience' => FormAudience::Registration->value,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->actingAsAdmin()->post(route('admin.custom-forms.store'), [
            'name' => 'Exhibitor Questions',
            'slug' => 'exhibitor-questions',
            'audience' => FormAudience::Exhibitor->value,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->actingAsAdmin()->post(route('admin.custom-forms.store'), [
            'name' => 'Group Questions',
            'slug' => 'group-questions',
            'audience' => FormAudience::Group->value,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->actingAsAdmin()->get(route('admin.custom-forms.index'))
            ->assertOk()
            ->assertSee('data-testid="create-custom-form-disabled"', false)
            ->assertSee('data-testid="audience-limit-note"', false);

        $this->actingAsAdmin()->get(route('admin.custom-forms.create'))
            ->assertRedirect(route('admin.custom-forms.index'));
    }

    #[Test]
    public function forms_and_route_bindings_are_isolated_to_the_current_tenant(): void
    {
        $form = $this->createForm('Tenant One', 'tenant-one');
        $otherEvent = Event::query()->create([
            'id' => 2,
            'organization_id' => 2,
            'title' => 'Other Event',
        ]);
        config(['event.event_id' => 2, 'event.org_id' => 2]);
        app()->instance('current.event', $otherEvent);

        $this->actingAsAdmin(2, 2)
            ->get(route('admin.custom-forms.index'))
            ->assertOk()
            ->assertDontSee('Tenant One');
        $this->actingAsAdmin(2, 2)
            ->get('/admin/custom-forms/'.$form->public_id.'/edit')
            ->assertNotFound();

        $this->actingAsAdmin(2, 2)->post(route('admin.custom-forms.store'), [
            'name' => 'Tenant Two Registration',
            'slug' => 'tenant-two-registration',
            'audience' => FormAudience::Registration->value,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();
    }

    private function actingAsAdmin(int $eventId = 1, int $orgId = 1): self
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_email' => 'admin@example.com',
            'admin_id' => 1,
            'event_id' => $eventId,
            'org_id' => $orgId,
        ]);
    }

    private function createForm(
        string $name = 'Registration Questions',
        string $slug = 'registration-questions',
        FormAudience $audience = FormAudience::Registration
    ): CustomForm {
        return CustomForm::query()->create([
            'name' => $name,
            'slug' => $slug,
            'audience' => $audience,
            'is_active' => true,
        ]);
    }
}
