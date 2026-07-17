<?php

namespace Tests\Unit\Forms;

use App\Forms\Enums\FormAudience;
use App\Forms\Enums\FormConditionAction;
use App\Forms\Enums\FormConditionOperator;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Models\CustomFormResponse;
use App\Forms\Services\DynamicFormValidator;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithCustomFormSchema;
use Tests\TestCase;

class DynamicFormValidatorTest extends TestCase
{
    use InteractsWithCustomFormSchema;

    private CustomForm $form;

    protected function setUp(): void
    {
        parent::setUp();
        config(['event.event_id' => 1, 'event.org_id' => 1]);
        $this->dropCustomFormTables();
        $this->createCustomFormTables();
        $this->form = CustomForm::query()->create([
            'name' => 'Registration details',
            'slug' => 'registration-details',
            'audience' => FormAudience::Registration,
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropCustomFormTables();
        parent::tearDown();
    }

    #[Test]
    public function hidden_required_questions_are_not_validated(): void
    {
        $source = $this->question('attending', FormQuestionType::Radio, true);
        $this->addOptions($source, ['yes', 'no']);
        $hidden = $this->question('dietary_notes', FormQuestionType::Text, true);
        $this->form->conditions()->create([
            'source_question_id' => $source->id,
            'target_question_id' => $hidden->id,
            'operator' => FormConditionOperator::Equals,
            'compare_value' => 'yes',
            'action' => FormConditionAction::Show,
            'is_active' => true,
        ]);

        $validator = app(DynamicFormValidator::class)->make($this->form->fresh(), ['attending' => 'no']);

        $this->assertFalse($validator->fails());
        $this->assertArrayNotHasKey('dietary_notes', $validator->getRules());
    }

    #[Test]
    public function text_and_textarea_rules_enforce_required_length_and_regex_settings(): void
    {
        $this->question('code', FormQuestionType::Text, true, [
            'min' => 3,
            'max' => 5,
            'regex' => '/^[A-Z]+$/',
        ]);
        $this->question('notes', FormQuestionType::Textarea, false, ['max_length' => 8]);
        $validator = app(DynamicFormValidator::class);

        $this->assertTrue($validator->make($this->form->fresh(), [
            'code' => 'ab',
            'notes' => 'too long notes',
        ])->fails());
        $this->assertSame([
            'code' => 'ABCDE',
            'notes' => 'brief',
        ], $validator->validate($this->form->fresh(), [
            'code' => 'ABCDE',
            'notes' => 'brief',
        ]));
    }

    #[Test]
    public function radio_and_select_accept_only_active_configured_options(): void
    {
        $radio = $this->question('attendance', FormQuestionType::Radio, true);
        $select = $this->question('track', FormQuestionType::Select, true);
        $this->addOptions($radio, ['yes', 'no']);
        $this->addOptions($select, ['backend', 'frontend']);
        $select->options()->create([
            'label' => 'Retired',
            'value' => 'retired',
            'is_active' => false,
            'sort_order' => 2,
        ]);
        $service = app(DynamicFormValidator::class);

        $invalid = $service->make($this->form->fresh(), [
            'attendance' => 'maybe',
            'track' => 'retired',
        ]);

        $this->assertTrue($invalid->fails());
        $this->assertArrayHasKey('attendance', $invalid->errors()->toArray());
        $this->assertArrayHasKey('track', $invalid->errors()->toArray());
        $this->assertFalse($service->make($this->form->fresh(), [
            'attendance' => 'yes',
            'track' => 'backend',
        ])->fails());
    }

    #[Test]
    public function checkbox_requires_a_nonempty_array_and_rejects_unknown_options(): void
    {
        $checkbox = $this->question('interests', FormQuestionType::Checkbox, true);
        $this->addOptions($checkbox, ['api', 'testing']);
        $service = app(DynamicFormValidator::class);

        $this->assertTrue($service->make($this->form->fresh(), ['interests' => []])->fails());
        $this->assertTrue($service->make($this->form->fresh(), ['interests' => ['api', 'unknown']])->fails());
        $this->assertFalse($service->make($this->form->fresh(), ['interests' => ['api', 'testing']])->fails());
    }

    #[Test]
    public function upload_enforces_required_mime_and_size_rules(): void
    {
        $this->question('document', FormQuestionType::Upload, true, [
            'mimes' => 'pdf, png',
            'max_kb' => 20,
        ]);
        $service = app(DynamicFormValidator::class);

        $this->assertTrue($service->make($this->form->fresh(), [])->fails());
        $this->assertTrue($service->make($this->form->fresh(), [
            'document' => UploadedFile::fake()->create('notes.txt', 5, 'text/plain'),
        ])->fails());
        $this->assertTrue($service->make($this->form->fresh(), [
            'document' => UploadedFile::fake()->create('large.pdf', 21, 'application/pdf'),
        ])->fails());
        $this->assertFalse($service->make($this->form->fresh(), [
            'document' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
        ])->fails());
    }

    #[Test]
    public function an_existing_required_upload_is_preserved_when_no_replacement_is_submitted(): void
    {
        $upload = $this->question('document', FormQuestionType::Upload, true, [
            'mimes' => 'pdf',
            'max_kb' => 100,
        ]);
        $response = CustomFormResponse::query()->create([
            'custom_form_id' => $this->form->id,
            'respondent_type' => 'draft',
            'respondent_id' => 99,
            'status' => 'submitted',
        ]);
        $answer = $response->answers()->create([
            'custom_form_question_id' => $upload->id,
            'question_key' => $upload->key,
            'question_label' => $upload->label,
            'question_type' => $upload->type,
        ]);
        $answer->files()->create([
            'disk' => 'local',
            'path' => 'existing/document.pdf',
            'original_name' => 'document.pdf',
            'mime' => 'application/pdf',
            'size' => 10,
        ]);

        $service = app(DynamicFormValidator::class);
        $rules = $service->buildRules($this->form->fresh(), [], $response->fresh());

        $this->assertContains('nullable', $rules['document']);
        $this->assertNotContains('required', $rules['document']);
        $this->assertFalse($service->make($this->form->fresh(), [], $response->fresh())->fails());
    }

    private function question(
        string $key,
        FormQuestionType $type,
        bool $required,
        array $validation = []
    ): CustomFormQuestion {
        return $this->form->questions()->create([
            'key' => $key,
            'label' => ucfirst(str_replace('_', ' ', $key)),
            'type' => $type,
            'is_required' => $required,
            'sort_order' => $this->form->questions()->count(),
            'is_active' => true,
            'validation' => $validation,
        ]);
    }

    private function addOptions(CustomFormQuestion $question, array $values): void
    {
        foreach ($values as $index => $value) {
            $question->options()->create([
                'label' => ucfirst($value),
                'value' => $value,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
