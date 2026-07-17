<?php

namespace Tests\Unit\Forms;

use App\Forms\Enums\FormAudience;
use App\Forms\Enums\FormConditionAction;
use App\Forms\Enums\FormConditionOperator;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormCondition;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Services\FormVisibilityEvaluator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithCustomFormSchema;
use Tests\TestCase;

class FormVisibilityEvaluatorTest extends TestCase
{
    use InteractsWithCustomFormSchema;

    protected function setUp(): void
    {
        parent::setUp();
        config(['event.event_id' => 1, 'event.org_id' => 1]);
        $this->dropCustomFormTables();
        $this->createCustomFormTables();
    }

    protected function tearDown(): void
    {
        $this->dropCustomFormTables();
        parent::tearDown();
    }

    public static function operatorCases(): array
    {
        return [
            'equals scalar' => [FormConditionOperator::Equals, 'yes', 'yes', true],
            'equals selected checkbox value' => [FormConditionOperator::Equals, ['vip', 'press'], 'vip', true],
            'not equals' => [FormConditionOperator::NotEquals, 'no', 'yes', true],
            'contains text' => [FormConditionOperator::Contains, 'Laravel conference', 'conference', true],
            'contains array member' => [FormConditionOperator::Contains, ['general', 'vip-pass'], 'vip', true],
            'is empty null' => [FormConditionOperator::IsEmpty, null, null, true],
            'is empty whitespace' => [FormConditionOperator::IsEmpty, '  ', null, true],
            'is empty nested empty array' => [FormConditionOperator::IsEmpty, ['', null], null, true],
            'is not empty' => [FormConditionOperator::IsNotEmpty, 'answer', null, true],
            'equals mismatch' => [FormConditionOperator::Equals, 'no', 'yes', false],
        ];
    }

    #[Test]
    #[DataProvider('operatorCases')]
    public function it_evaluates_every_operator(
        FormConditionOperator $operator,
        mixed $answer,
        mixed $compare,
        bool $expected
    ): void {
        [$form, $source, $target] = $this->formWithTwoQuestions();
        $this->condition($form, $source, $target, $operator, FormConditionAction::Show, $compare);

        $visibility = app(FormVisibilityEvaluator::class)->evaluate(
            $form->fresh(),
            [$source->key => $answer]
        );

        $this->assertSame($expected, $visibility[$target->id]);
    }

    #[Test]
    public function show_and_hide_actions_apply_the_opposite_visibility_when_a_condition_fails(): void
    {
        [$showForm, $showSource, $showTarget] = $this->formWithTwoQuestions('show');
        $this->condition(
            $showForm,
            $showSource,
            $showTarget,
            FormConditionOperator::Equals,
            FormConditionAction::Show,
            'yes'
        );

        [$hideForm, $hideSource, $hideTarget] = $this->formWithTwoQuestions('hide');
        $this->condition(
            $hideForm,
            $hideSource,
            $hideTarget,
            FormConditionOperator::Equals,
            FormConditionAction::Hide,
            'yes'
        );

        $evaluator = app(FormVisibilityEvaluator::class);
        $this->assertFalse($evaluator->evaluate($showForm->fresh(), ['source_show' => 'no'])[$showTarget->id]);
        $this->assertTrue($evaluator->evaluate($hideForm->fresh(), ['source_hide' => 'no'])[$hideTarget->id]);
        $this->assertFalse($evaluator->evaluate($hideForm->fresh(), ['source_hide' => 'yes'])[$hideTarget->id]);
    }

    #[Test]
    public function active_conditions_for_one_target_are_combined_with_flat_and(): void
    {
        $form = $this->form('and');
        $first = $this->question($form, 'first', 0);
        $second = $this->question($form, 'second', 1);
        $target = $this->question($form, 'target', 2);
        $this->condition($form, $first, $target, FormConditionOperator::Equals, FormConditionAction::Show, 'yes', 0);
        $this->condition($form, $second, $target, FormConditionOperator::IsNotEmpty, FormConditionAction::Show, null, 1);

        $evaluator = app(FormVisibilityEvaluator::class);

        $this->assertTrue($evaluator->evaluate($form->fresh(), ['first' => 'yes', 'second' => 'set'])[$target->id]);
        $this->assertFalse($evaluator->evaluate($form->fresh(), ['first' => 'yes', 'second' => ''])[$target->id]);
    }

    #[Test]
    public function inactive_conditions_and_questions_are_ignored(): void
    {
        [$form, $source, $target] = $this->formWithTwoQuestions('inactive');
        $this->condition(
            $form,
            $source,
            $target,
            FormConditionOperator::Equals,
            FormConditionAction::Show,
            'yes',
            0,
            false
        );
        $inactiveQuestion = $this->question($form, 'inactive_question', 2, false);

        $visibility = app(FormVisibilityEvaluator::class)->evaluate($form->fresh(), []);

        $this->assertTrue($visibility[$target->id]);
        $this->assertArrayNotHasKey($inactiveQuestion->id, $visibility);
    }

    private function form(string $suffix): CustomForm
    {
        CustomForm::query()
            ->where('audience_unique', FormAudience::Registration->value)
            ->get()
            ->each->delete();

        return CustomForm::query()->create([
            'name' => 'Form '.$suffix,
            'slug' => 'form-'.$suffix,
            'audience' => FormAudience::Registration,
            'is_active' => true,
        ]);
    }

    private function formWithTwoQuestions(string $suffix = 'operators'): array
    {
        $form = $this->form($suffix);

        return [
            $form,
            $this->question($form, 'source_'.$suffix, 0),
            $this->question($form, 'target_'.$suffix, 1),
        ];
    }

    private function question(
        CustomForm $form,
        string $key,
        int $sortOrder,
        bool $active = true
    ): CustomFormQuestion {
        return $form->questions()->create([
            'key' => $key,
            'label' => ucfirst(str_replace('_', ' ', $key)),
            'type' => FormQuestionType::Text,
            'sort_order' => $sortOrder,
            'is_active' => $active,
        ]);
    }

    private function condition(
        CustomForm $form,
        CustomFormQuestion $source,
        CustomFormQuestion $target,
        FormConditionOperator $operator,
        FormConditionAction $action,
        mixed $compare,
        int $sortOrder = 0,
        bool $active = true
    ): CustomFormCondition {
        return $form->conditions()->create([
            'source_question_id' => $source->id,
            'target_question_id' => $target->id,
            'operator' => $operator,
            'action' => $action,
            'compare_value' => $compare,
            'sort_order' => $sortOrder,
            'is_active' => $active,
        ]);
    }
}
