<?php

namespace App\Submissions\Services;

use App\Submissions\Models\Review;

final class ReviewScoringService
{
    /**
     * @param  array<int, array{id:int, min?:float, max?:float, weight?:float}>  $criteria
     * @param  array<int|string, mixed>  $responses
     * @return array{raw:float, weighted:float, normalized:float, answers:array<int, array<string, mixed>>}
     */
    public function calculate(array $criteria, array $responses): array
    {
        $raw = 0.0;
        $weighted = 0.0;
        $weightTotal = 0.0;
        $answers = [];

        foreach ($criteria as $criterion) {
            $value = (float) ($responses[$criterion['id']] ?? 0);
            $min = (float) ($criterion['min'] ?? 0);
            $max = max($min + 1, (float) ($criterion['max'] ?? 5));
            $weight = max(0, (float) ($criterion['weight'] ?? 1));
            $normalized = (($value - $min) / ($max - $min)) * 100;

            $raw += $value;
            $weighted += $normalized * $weight;
            $weightTotal += $weight;
            $answers[] = [
                'criterion_id' => $criterion['id'],
                'value' => ['raw' => $value, 'weighted' => $normalized * $weight],
                'score' => $value,
            ];
        }

        $normalizedTotal = $weightTotal > 0 ? $weighted / $weightTotal : 0;

        return [
            'raw' => round($raw, 4),
            'weighted' => round($weighted, 4),
            'normalized' => round($normalizedTotal, 4),
            'answers' => $answers,
        ];
    }

    public function recalculate(Review $review): Review
    {
        $review->loadMissing('answers.criterion');
        $criteria = $review->answers->map(fn ($answer): array => [
            'id' => $answer->criterion_id,
            'min' => $answer->criterion->min_score ?? 0,
            'max' => $answer->criterion->max_score ?? 5,
            'weight' => $answer->criterion->weight ?? 1,
        ])->all();
        $responses = $review->answers->pluck('score', 'criterion_id')->all();
        $totals = $this->calculate($criteria, $responses);

        $review->forceFill([
            'total_score' => $totals['normalized'],
            'settings' => [
                ...($review->settings ?? []),
                'raw_score' => $totals['raw'],
                'weighted_score' => $totals['weighted'],
            ],
        ])->save();

        return $review->refresh();
    }
}
