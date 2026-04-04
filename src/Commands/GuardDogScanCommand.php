<?php

namespace Jaydeep\GuardDog\Commands;

use Illuminate\Console\Command;
use Jaydeep\GuardDog\Core\ScannerManager;
use Jaydeep\GuardDog\Report\ConsoleReporter;
use Jaydeep\GuardDog\Report\HtmlReportGenerator;

class GuardDogScanCommand extends Command
{
    protected $signature = 'guarddog:scan
                            {--no-html : Skip HTML report generation}
                            {--output= : Custom output path for HTML report}';

    protected $description = 'Scan your Laravel project for common security vulnerabilities';

    public function handle(): int
    {
        $this->info('');
        $this->info('  🐕 GuardDog is sniffing your project for vulnerabilities...');
        $this->info('');

        $manager = new ScannerManager();
        $issues = $manager->scan();
        $filesScanned = $manager->getFilesScanned();

        // Console report
        $consoleReporter = new ConsoleReporter($this);
        $consoleReporter->render($issues, $filesScanned);

        // HTML report
        if (!$this->option('no-html')) {
            if ($outputPath = $this->option('output')) {
                config(['guarddog.report_output_path' => $outputPath]);
            }

            $htmlGenerator = new HtmlReportGenerator();
            $reportPath = $htmlGenerator->generate($issues, $filesScanned);

            $this->newLine();
            $this->info("  HTML report saved to: {$reportPath}");
        }

        $this->newLine();

        // Return non-zero exit code if critical issues found
        $hasCritical = count(array_filter($issues, function ($issue) {
            return strtoupper($issue['severity']) === 'CRITICAL';
        })) > 0;

        return $hasCritical ? 1 : 0;
    }
}
