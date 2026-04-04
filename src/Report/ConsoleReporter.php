<?php

namespace Jaydeep\GuardDog\Report;

use Illuminate\Console\Command;
use Jaydeep\GuardDog\Core\SecurityScoreCalculator;

class ConsoleReporter
{
    protected Command $command;
    protected SecurityScoreCalculator $calculator;

    public function __construct(Command $command)
    {
        $this->command = $command;
        $this->calculator = new SecurityScoreCalculator();
    }

    public function render(array $issues, int $filesScanned): void
    {
        $score = $this->calculator->setFilesScanned($filesScanned)->calculate($issues);
        $rating = $this->calculator->getRating($score);
        $issueCount = count($issues);

        $criticalCount = $this->countBySeverity($issues, 'CRITICAL');
        $warningCount = $this->countBySeverity($issues, 'WARNING');
        $noticeCount = $this->countBySeverity($issues, 'NOTICE');

        $this->command->newLine();
        $this->command->line('╔══════════════════════════════════════════════════════════╗');
        $this->command->line('║         🐕 Laravel GuardDog Security Report              ║');
        $this->command->line('╚══════════════════════════════════════════════════════════╝');
        $this->command->newLine();

        $this->command->line("  Files scanned:  <info>{$filesScanned}</info>");
        $this->command->line("  Issues found:   <comment>{$issueCount}</comment>");
        $this->command->newLine();

        // Score display
        $scoreStyle = $this->getScoreStyle($score);
        $this->command->line("  Security Score: <{$scoreStyle}>{$score} / 100 ({$rating})</{$scoreStyle}>");
        $this->command->newLine();

        // Summary counts
        if ($criticalCount > 0) {
            $this->command->line("  <fg=red>● CRITICAL: {$criticalCount}</>");
        }
        if ($warningCount > 0) {
            $this->command->line("  <fg=yellow>● WARNING:  {$warningCount}</>");
        }
        if ($noticeCount > 0) {
            $this->command->line("  <fg=blue>● NOTICE:   {$noticeCount}</>");
        }

        $this->command->newLine();
        $this->command->line('──────────────────────────────────────────────────────────');
        $this->command->newLine();

        // Display each issue
        if ($issueCount === 0) {
            $this->command->info('  No security issues found. Great job!');
        } else {
            foreach ($issues as $issue) {
                $this->renderIssue($issue);
            }
        }

        $this->command->newLine();
        $this->command->line('──────────────────────────────────────────────────────────');
    }

    protected function renderIssue(array $issue): void
    {
        $severity = strtoupper($issue['severity']);
        $tag = $this->getSeverityTag($severity);

        $this->command->line("  <{$tag}>{$severity}</{$tag}>");
        $this->command->line("  {$issue['message']}");
        $this->command->line("  <fg=gray>File: {$issue['file']}:{$issue['line']}</>");
        $this->command->newLine();
    }

    protected function getSeverityTag(string $severity): string
    {
        switch ($severity) {
            case 'CRITICAL': return 'fg=white;bg=red';
            case 'WARNING':  return 'fg=black;bg=yellow';
            case 'NOTICE':   return 'fg=white;bg=blue';
            default:         return 'info';
        }
    }

    protected function getScoreStyle(int $score): string
    {
        if ($score >= 90) return 'info';
        if ($score >= 70) return 'comment';
        if ($score >= 50) return 'fg=yellow';
        return 'error';
    }

    protected function countBySeverity(array $issues, string $severity): int
    {
        return count(array_filter($issues, function ($issue) use ($severity) {
            return strtoupper($issue['severity']) === $severity;
        }));
    }
}
