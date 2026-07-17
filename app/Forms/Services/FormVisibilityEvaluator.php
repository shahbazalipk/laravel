<?php

namespace App\Forms\Services;

use App\Forms\Enums\FormConditionAction;
use App\Forms\Enums\FormConditionOperator;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormCondition;
use App\Forms\Models\CustomFormQuestion;
use Illuminate\Support\Collection;

/**
 * Evaluates flat AND visibility rules per target question (v1).
 *
 * All active conditions for a given target are combined with AND.
 * When the combined result is true, the condition action applies:
 * - show → target is visible
 * - hide → target is hidden
 * When false, the opposite applies. Questions with no conditions stay visible.
 */
class FormVisibilityEvaluator
{
    /**
     * @param  array<string, mixed>  $answers  Map of question key => submitted value
     * @return array<int, bool> Map of question id => is visible
     */
    public function evaluate(CustomForm $form, array $answers): array
    {
        $questions = $this->questions($form);
        $conditions = $this->conditions($form);

        $visibility = [];
        foreach ($questions as $question) {
            $visibility[(int) $question->id] = true;
        }

        $byTarget = $conditions
            ->filter(fn (CustomFormCondition $condition) => $condition->is_active)
            ->groupBy('target_question_id');

        foreach ($byTarget as $targetId => $targetConditions) {
            $targetId = (int) $targetId;
            if (!array_key_exists($targetId, $visibility)) {
                continue;
            }

            $ordered = $targetConditions->sortBy('sort_order')->values();
            $allMatch = $ordered->every(
                fn (CustomFormCondition $condition) => $this->conditionMatches($condition, $answers, $questions)
            );

            /** @var CustomFormCondition $first */
            $first = $ordered->first();
            $action = $first->action;

            $visibility[$targetId] = $action === FormConditionAction::Show
                ? $allMatch
                : !$allMatch;
        }

        return $visibility;
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return Collection<int, CustomFormQuestion>
     */
    public function visibleQuestions(CustomForm $form, array $answers): Collection
    {
        $visibility = $this->evaluate($form, $answers);

        return $this->questions($form)
            ->filter(fn (CustomFormQuestion $question) => $visibility[(int) $question->id] ?? true)
            ->values();
    }

    /**
     * @param  array<string, mixed>  $answers
     * @param  Collection<int, CustomFormQuestion>  $questions
     */
    public function conditionMatches(
        CustomFormCondition $condition,
        array $answers,
        Collection $questions
    ): bool {
        $source = $questions->firstWhere('id', $condition->source_question_id);
        if (!$source instanceof CustomFormQuestion) {
            return false;
        }

        $value = $answers[$source->key] ?? null;
        $operator = $condition->operator;
        $compare = $condition->compare_value;

        return match ($operator) {
            FormConditionOperator::IsEmpty => $this->isEmpty($value),
            FormConditionOperator::IsNotEmpty => !$this->isEmpty($value),
            FormConditionOperator::Equals => $this->equals($value, $compare),
            FormConditionOperator::NotEquals => !$this->equals($value, $compare),
            FormConditionOperator::Contains => $this->contains($value, $compare),
        };
    }

    /**
     * @return Collection<int, CustomFormQuestion>
     */
    protected function questions(CustomForm $form): Collection
    {
        if ($form->relationLoaded('questions')) {
            return $form->questions->where('is_active', true)->values();
        }

        return $form->questions()->active()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, CustomFormCondition>
     */
    protected function conditions(CustomForm $form): Collection
    {
        if ($form->relationLoaded('conditions')) {
            return $form->conditions->where('is_active', true)->values();
        }

        return $form->conditions()->active()->orderBy('sort_order')->get();
    }

    protected function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return count(array_filter($value, fn ($item) => !$this->isEmpty($item))) === 0;
        }

        return false;
    }

    protected function equals(mixed $value, mixed $compare): bool
    {
        $compareNormalized = $this->normalizeCompareValue($compare);

        if (is_array($value)) {
            $normalized = array_map(fn ($item) => $this->stringify($item), $value);
            sort($normalized);

            if (is_array($compareNormalized)) {
                $expected = array_map(fn ($item) => $this->stringify($item), $compareNormalized);
                sort($expected);

                return $normalized === $expected;
            }

            return in_array($this->stringify($compareNormalized), $normalized, true);
        }

        if (is_array($compareNormalized)) {
            return in_array($this->stringify($value), array_map(fn ($item) => $this->stringify($item), $compareNormalized), true);
        }

        return $this->stringify($value) === $this->stringify($compareNormalized);
    }

    protected function contains(mixed $value, mixed $compare): bool
    {
        $needle = $this->stringify($this->normalizeCompareValue($compare));

        if (is_array($value)) {
            foreach ($value as $item) {
                if (str_contains($this->stringify($item), $needle)) {
                    return true;
                }
            }

            return false;
        }

        return str_contains($this->stringify($value), $needle);
    }

    protected function normalizeCompareValue(mixed $compare): mixed
    {
        if (!is_array($compare)) {
            return $compare;
        }

        // JSON compare_value may be a scalar wrapped as {"value": "..."} or a list.
        if (array_key_exists('value', $compare) && count($compare) === 1) {
            return $compare['value'];
        }

        return $compare;
    }

    protected function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            return json_encode(array_values($value)) ?: '';
        }

        return trim((string) $value);
    }
}
