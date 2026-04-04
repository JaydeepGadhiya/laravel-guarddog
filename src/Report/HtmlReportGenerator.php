<?php

namespace Jaydeep\GuardDog\Report;

use Illuminate\Support\Facades\View;
use Jaydeep\GuardDog\Core\SecurityScoreCalculator;

class HtmlReportGenerator
{
    protected SecurityScoreCalculator $calculator;

    public function __construct()
    {
        $this->calculator = new SecurityScoreCalculator();
    }

    public function generate(array $issues, int $filesScanned): string
    {
        $score = $this->calculator->setFilesScanned($filesScanned)->calculate($issues);
        $rating = $this->calculator->getRating($score);
        $ratingColor = $this->calculator->getRatingColor($score);

        $criticalCount = $this->countBySeverity($issues, 'CRITICAL');
        $warningCount = $this->countBySeverity($issues, 'WARNING');
        $noticeCount = $this->countBySeverity($issues, 'NOTICE');

        $data = [
            'projectName'   => config('app.name', 'Laravel'),
            'scanDate'      => now()->format('Y-m-d H:i:s'),
            'score'         => $score,
            'rating'        => $rating,
            'ratingColor'   => $ratingColor,
            'filesScanned'  => $filesScanned,
            'totalIssues'   => count($issues),
            'criticalCount' => $criticalCount,
            'warningCount'  => $warningCount,
            'noticeCount'   => $noticeCount,
            'issues'        => $issues,
        ];

        $html = View::make('guarddog::report', $data)->render();

        $outputPath = config('guarddog.report_output_path', storage_path('guarddog-security-report.html'));
        file_put_contents($outputPath, $html);

        return $outputPath;
    }

    protected function countBySeverity(array $issues, string $severity): int
    {
        return count(array_filter($issues, function ($issue) use ($severity) {
            return strtoupper($issue['severity']) === $severity;
        }));
    }
}
