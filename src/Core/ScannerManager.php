<?php

namespace Jaydeep\GuardDog\Core;

use Illuminate\Support\Facades\File;
use Jaydeep\GuardDog\Scanners\RouteScanner;
use Jaydeep\GuardDog\Scanners\SQLScanner;
use Jaydeep\GuardDog\Scanners\UploadScanner;
use Jaydeep\GuardDog\Scanners\EnvScanner;
use Jaydeep\GuardDog\Scanners\CSRFScanner;

class ScannerManager
{
    protected array $scanners = [];
    protected array $issues = [];
    protected int $filesScanned = 0;

    public function __construct()
    {
        $this->scanners = [
            new RouteScanner(),
            new SQLScanner(),
            new UploadScanner(),
            new EnvScanner(),
            new CSRFScanner(),
        ];
    }

    public function scan(): array
    {
        $this->issues = [];
        $this->filesScanned = 0;

        $files = $this->collectFiles();
        $this->filesScanned = count($files);

        foreach ($this->scanners as $scanner) {
            $scannerIssues = $scanner->scan($files);
            $this->issues = array_merge($this->issues, $scannerIssues);
        }

        return $this->issues;
    }

    protected function collectFiles(): array
    {
        $files = [];
        $scanPaths = config('guarddog.scan_paths', ['app/', 'routes/', 'resources/views/', 'config/']);
        $ignorePaths = config('guarddog.ignore_paths', ['vendor/', 'node_modules/', 'storage/']);

        foreach ($scanPaths as $scanPath) {
            $fullPath = base_path($scanPath);

            if (!File::isDirectory($fullPath)) {
                continue;
            }

            $allFiles = File::allFiles($fullPath);

            foreach ($allFiles as $file) {
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $relativePath = str_replace('\\', '/', $relativePath);

                $skip = false;
                foreach ($ignorePaths as $ignorePath) {
                    if (strpos($relativePath, rtrim($ignorePath, '/')) === 0) {
                        $skip = true;
                        break;
                    }
                }

                if (!$skip) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }

    public function getIssues(): array
    {
        return $this->issues;
    }

    public function getFilesScanned(): int
    {
        return $this->filesScanned;
    }
}
