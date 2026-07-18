<?php

namespace Tests\Unit\Submissions;

use App\Submissions\Services\ReviewScoringService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReviewScoringServiceTest extends TestCase
{
    #[Test]
    public function it_calculates_raw_weighted_and_normalized_scores(): void
    {
        $result = (new ReviewScoringService)->calculate([
            ['id' => 10, 'min' => 0, 'max' => 5, 'weight' => 2],
            ['id' => 20, 'min' => 1, 'max' => 5, 'weight' => 1],
        ], [
            10 => 4,
            20 => 3,
        ]);

        $this->assertSame(7.0, $result['raw']);
        $this->assertSame(210.0, $result['weighted']);
        $this->assertSame(70.0, $result['normalized']);
        $this->assertSame(10, $result['answers'][0]['criterion_id']);
        $this->assertSame(['raw' => 4.0, 'weighted' => 160.0], $result['answers'][0]['value']);
    }

    #[Test]
    public function missing_responses_default_to_zero_and_negative_weights_are_ignored(): void
    {
        $result = (new ReviewScoringService)->calculate([
            ['id' => 1, 'min' => 0, 'max' => 10, 'weight' => -2],
            ['id' => 2, 'min' => 0, 'max' => 5, 'weight' => 1],
        ], [2 => 5]);

        $this->assertSame(5.0, $result['raw']);
        $this->assertSame(100.0, $result['weighted']);
        $this->assertSame(100.0, $result['normalized']);
    }

    #[Test]
    public function zero_total_weight_produces_a_zero_normalized_score(): void
    {
        $result = (new ReviewScoringService)->calculate([
            ['id' => 1, 'min' => 0, 'max' => 5, 'weight' => 0],
        ], [1 => 5]);

        $this->assertSame(5.0, $result['raw']);
        $this->assertSame(0.0, $result['weighted']);
        $this->assertSame(0.0, $result['normalized']);
    }

    #[Test]
    public function it_uses_safe_defaults_for_criteria_ranges_and_weights(): void
    {
        $result = (new ReviewScoringService)->calculate([
            ['id' => 1],
            ['id' => 2, 'min' => 5, 'max' => 5],
        ], [1 => 2.5, 2 => 6]);

        $this->assertSame(8.5, $result['raw']);
        $this->assertSame(150.0, $result['weighted']);
        $this->assertSame(75.0, $result['normalized']);
    }
}
