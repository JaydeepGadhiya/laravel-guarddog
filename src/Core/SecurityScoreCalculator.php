<?php

namespace Jaydeep\GuardDog\Core;

class SecurityScoreCalculator
{
    protected const WEIGHTS = [
        'CRITICAL' => 3,
        'WARNING'  => 2,
        'NOTICE'   => 1,
    ];

    protected const RATINGS = [
        90  => 'Excellent',
        70  => 'Good',
        50  => 'Risky',
        0   => 'Critical',
    ];

    protected int $filesScanned = 0;

    public function setFilesScanned(int $count): self
    {
        $this->filesScanned = $count;
        return $this;
    }

    public function calculate(array $issues): int
    {
        if (empty($issues)) {
            return 100;
        }

        $filesScanned = max($this->filesScanned, 1);

        // Calculate severity-weighted issue count
        $weightedIssues = 0;
        foreach ($issues as $issue) {
            $severity = strtoupper($issue['severity'] ?? 'NOTICE');
            $weightedIssues += self::WEIGHTS[$severity] ?? 1;
        }

        // Ratio-based score with exponential dampening
        // score = 100 × e^(-weightedIssues / filesScanned)
        $score = (int) round(100 * exp(-$weightedIssues / $filesScanned));

        return max(0, min(100, $score));
    }

    public function getRating(int $score): string
    {
        foreach (self::RATINGS as $threshold => $label) {
            if ($score >= $threshold) {
                return $label;
            }
        }

        return 'Critical';
    }

    public function getRatingColor(int $score): string
    {
        if ($score >= 90) return '#22c55e';
        if ($score >= 70) return '#eab308';
        if ($score >= 50) return '#f97316';
        return '#ef4444';
    }
}
