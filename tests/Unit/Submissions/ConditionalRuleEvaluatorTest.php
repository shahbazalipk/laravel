<?php

namespace Tests\Unit\Submissions;

use App\Submissions\Services\ConditionalRuleEvaluator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConditionalRuleEvaluatorTest extends TestCase
{
    private ConditionalRuleEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new ConditionalRuleEvaluator;
    }

    #[Test]
    #[DataProvider('operatorCases')]
    public function it_evaluates_supported_operators(mixed $actual, string $operator, mixed $expected, bool $result): void
    {
        $this->assertSame($result, $this->evaluator->matches($actual, $operator, $expected));
    }

    public static function operatorCases(): array
    {
        return [
            'equals' => ['Keynote', 'equals', 'Keynote', true],
            'not equals' => ['Workshop', 'not_equals', 'Keynote', true],
            'contains case insensitive' => ['Applied AI Workshop', 'contains', 'ai', true],
            'not contains' => ['Leadership', 'not_contains', 'security', true],
            'greater than' => [8, 'greater_than', 5, true],
            'less or equal' => [5, 'less_or_equal', 5, true],
            'empty' => ['', 'is_empty', null, true],
            'not empty' => ['value', 'is_not_empty', null, true],
            'selected' => [['ai', 'cloud'], 'is_selected', 'cloud', true],
            'includes any' => [['ai', 'cloud'], 'includes_any', ['security', 'cloud'], true],
            'includes all' => [['ai', 'cloud'], 'includes_all', ['cloud', 'ai'], true],
            'date before' => ['2026-07-01', 'date_before', '2026-07-18', true],
            'date after' => ['2026-07-19', 'date_after', '2026-07-18', true],
            'unknown' => ['value', 'unsupported', 'value', false],
        ];
    }

    #[Test]
    public function it_combines_all_and_any_conditions_and_ignores_inactive_rules(): void
    {
        $state = $this->evaluator->evaluate([
            [
                'target' => 'workshop_details',
                'action' => 'show',
                'conditions' => [
                    ['source' => 'format', 'operator' => 'equals', 'value' => 'workshop'],
                    ['source' => 'duration', 'operator' => 'greater_or_equal', 'value' => 60],
                ],
            ],
            [
                'target' => 'supporting_file',
                'action' => 'require',
                'match' => 'any',
                'conditions' => [
                    ['source' => 'track', 'operator' => 'equals', 'value' => 'research'],
                    ['source' => 'has_results', 'operator' => 'equals', 'value' => true],
                ],
            ],
            [
                'target' => 'ignored',
                'action' => 'hide',
                'is_active' => false,
                'source' => 'format',
                'value' => 'workshop',
            ],
        ], [
            'format' => 'workshop',
            'duration' => 90,
            'track' => 'general',
            'has_results' => true,
        ]);

        $this->assertSame(['visible' => true], $state['workshop_details']);
        $this->assertSame(['required' => true], $state['supporting_file']);
        $this->assertArrayNotHasKey('ignored', $state);
    }

    #[Test]
    public function later_matching_actions_merge_into_the_target_state(): void
    {
        $state = $this->evaluator->evaluate([
            ['source' => 'format', 'operator' => 'equals', 'value' => 'panel', 'target' => 'panelists', 'action' => 'show'],
            ['source' => 'format', 'operator' => 'equals', 'value' => 'panel', 'target' => 'panelists', 'action' => 'require'],
            ['source' => 'format', 'operator' => 'equals', 'value' => 'panel', 'target' => 'topic', 'action' => 'set_default', 'action_value' => 'Panel'],
        ], ['format' => 'panel']);

        $this->assertSame(['visible' => true, 'required' => true], $state['panelists']);
        $this->assertSame(['value' => 'Panel'], $state['topic']);
    }
}
