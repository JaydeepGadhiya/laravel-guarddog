<?php

namespace Jaydeep\GuardDog\Tests\Unit;

use Jaydeep\GuardDog\Core\SecurityScoreCalculator;
use PHPUnit\Framework\TestCase;

class SecurityScoreCalculatorTest extends TestCase
{
    private SecurityScoreCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new SecurityScoreCalculator();
    }

    public function test_perfect_score_when_no_issues(): void
    {
        $score = $this->calculator->calculate([]);
        $this->assertSame(100, $score);
    }

    public function test_score_is_between_0_and_100(): void
    {
        $issues = array_fill(0, 50, ['severity' => 'CRITICAL']);
        $score = $this->calculator->setFilesScanned(10)->calculate($issues);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_more_issues_produce_lower_score(): void
    {
        $calculator = (new SecurityScoreCalculator())->setFilesScanned(100);

        $scoreClean  = $calculator->calculate([]);
        $scoreOne    = $calculator->calculate([['severity' => 'CRITICAL']]);
        $scoreMany   = $calculator->calculate(array_fill(0, 10, ['severity' => 'CRITICAL']));

        $this->assertGreaterThan($scoreOne, $scoreClean);
        $this->assertGreaterThan($scoreMany, $scoreOne);
    }

    public function test_rating_excellent_at_90_or_above(): void
    {
        $this->assertSame('Excellent', $this->calculator->getRating(100));
        $this->assertSame('Excellent', $this->calculator->getRating(90));
    }

    public function test_rating_good_between_70_and_89(): void
    {
        $this->assertSame('Good', $this->calculator->getRating(89));
        $this->assertSame('Good', $this->calculator->getRating(70));
    }

    public function test_rating_risky_between_50_and_69(): void
    {
        $this->assertSame('Risky', $this->calculator->getRating(69));
        $this->assertSame('Risky', $this->calculator->getRating(50));
    }

    public function test_rating_critical_below_50(): void
    {
        $this->assertSame('Critical', $this->calculator->getRating(49));
        $this->assertSame('Critical', $this->calculator->getRating(0));
    }

    public function test_rating_color_matches_score_band(): void
    {
        $this->assertSame('#22c55e', $this->calculator->getRatingColor(100));
        $this->assertSame('#eab308', $this->calculator->getRatingColor(80));
        $this->assertSame('#f97316', $this->calculator->getRatingColor(60));
        $this->assertSame('#ef4444', $this->calculator->getRatingColor(30));
    }
}
