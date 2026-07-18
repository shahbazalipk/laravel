<?php

namespace App\Submissions\Services;

final class ConditionalRuleEvaluator
{
    /**
     * @param  array<int, array<string, mixed>>  $rules
     * @param  array<string, mixed>  $answers
     * @return array<string, array<string, mixed>>
     */
    public function evaluate(array $rules, array $answers): array
    {
        $state = [];

        foreach ($rules as $rule) {
            if (! ($rule['is_active'] ?? true)) {
                continue;
            }

            $conditions = $rule['conditions'] ?? [[
                'source' => $rule['source'] ?? null,
                'operator' => $rule['operator'] ?? 'equals',
                'value' => $rule['value'] ?? null,
            ]];
            $matches = array_map(
                fn (array $condition): bool => $this->matches(
                    $answers[$condition['source'] ?? ''] ?? null,
                    $condition['operator'] ?? 'equals',
                    $condition['value'] ?? null,
                ),
                $conditions,
            );
            $matched = ($rule['match'] ?? 'all') === 'any'
                ? in_array(true, $matches, true)
                : ! in_array(false, $matches, true);

            if ($matched) {
                $target = (string) ($rule['target'] ?? '');
                $action = (string) ($rule['action'] ?? 'show');
                $state[$target] = array_merge($state[$target] ?? [], $this->actionState($action, $rule));
            }
        }

        return $state;
    }

    public function matches(mixed $actual, string $operator, mixed $expected = null): bool
    {
        $actualValues = is_array($actual) ? $actual : [$actual];
        $expectedValues = is_array($expected) ? $expected : [$expected];

        return match ($operator) {
            'equals' => $actual == $expected,
            'not_equals' => $actual != $expected,
            'contains' => str_contains(mb_strtolower((string) $actual), mb_strtolower((string) $expected)),
            'not_contains' => ! str_contains(mb_strtolower((string) $actual), mb_strtolower((string) $expected)),
            'greater_than' => is_numeric($actual) && $actual > $expected,
            'less_than' => is_numeric($actual) && $actual < $expected,
            'greater_or_equal' => is_numeric($actual) && $actual >= $expected,
            'less_or_equal' => is_numeric($actual) && $actual <= $expected,
            'is_empty' => blank($actual),
            'is_not_empty' => filled($actual),
            'is_selected' => in_array($expected, $actualValues),
            'is_not_selected' => ! in_array($expected, $actualValues),
            'includes_any' => array_intersect($actualValues, $expectedValues) !== [],
            'includes_all' => array_diff($expectedValues, $actualValues) === [],
            'date_before' => filled($actual) && strtotime((string) $actual) < strtotime((string) $expected),
            'date_after' => filled($actual) && strtotime((string) $actual) > strtotime((string) $expected),
            default => false,
        };
    }

    /** @return array<string, mixed> */
    private function actionState(string $action, array $rule): array
    {
        return match ($action) {
            'show' => ['visible' => true],
            'hide' => ['visible' => false],
            'require' => ['required' => true],
            'optional' => ['required' => false],
            'enable' => ['enabled' => true],
            'disable' => ['enabled' => false],
            'set_default' => ['value' => $rule['action_value'] ?? null],
            'clear' => ['value' => null],
            default => [],
        };
    }
}
