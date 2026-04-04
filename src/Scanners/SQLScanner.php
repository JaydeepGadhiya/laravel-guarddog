<?php

namespace Jaydeep\GuardDog\Scanners;

use SplFileInfo;

class SQLScanner
{
    protected array $dangerousPatterns = [
        [
            'pattern'  => '/DB\s*::\s*statement\s*\(\s*["\'].*\$/',
            'message'  => 'Raw SQL with variable interpolation in DB::statement()',
            'severity' => 'CRITICAL',
        ],
        [
            'pattern'  => '/DB\s*::\s*select\s*\(\s*["\'].*\$/',
            'message'  => 'Raw SQL with variable interpolation in DB::select()',
            'severity' => 'CRITICAL',
        ],
        [
            'pattern'  => '/DB\s*::\s*insert\s*\(\s*["\'].*\$/',
            'message'  => 'Raw SQL with variable interpolation in DB::insert()',
            'severity' => 'CRITICAL',
        ],
        [
            'pattern'  => '/DB\s*::\s*update\s*\(\s*["\'].*\$/',
            'message'  => 'Raw SQL with variable interpolation in DB::update()',
            'severity' => 'CRITICAL',
        ],
        [
            'pattern'  => '/DB\s*::\s*delete\s*\(\s*["\'].*\$/',
            'message'  => 'Raw SQL with variable interpolation in DB::delete()',
            'severity' => 'CRITICAL',
        ],
        [
            'pattern'  => '/DB\s*::\s*statement\s*\(/',
            'message'  => 'Raw SQL statement detected — review for injection risk',
            'severity' => 'WARNING',
        ],
        [
            'pattern'  => '/DB\s*::\s*unprepared\s*\(/',
            'message'  => 'Unprepared SQL query detected — high injection risk',
            'severity' => 'CRITICAL',
        ],
        [
            'pattern'  => '/->whereRaw\s*\(\s*["\'].*\$/',
            'message'  => 'whereRaw() with variable interpolation — SQL injection risk',
            'severity' => 'CRITICAL',
        ],
        [
            'pattern'  => '/->selectRaw\s*\(\s*["\'].*\$/',
            'message'  => 'selectRaw() with variable interpolation — SQL injection risk',
            'severity' => 'CRITICAL',
        ],
        [
            'pattern'  => '/->orderByRaw\s*\(\s*["\'].*\$/',
            'message'  => 'orderByRaw() with variable interpolation — SQL injection risk',
            'severity' => 'CRITICAL',
        ],
    ];

    public function scan(array $files): array
    {
        $issues = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $issues = array_merge($issues, $this->scanFile($file));
        }

        return $issues;
    }

    protected function scanFile(SplFileInfo $file): array
    {
        $issues = [];
        $content = file_get_contents($file->getPathname());
        $lines = explode("\n", $content);
        $relativePath = $this->getRelativePath($file);

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;

            foreach ($this->dangerousPatterns as $check) {
                if (preg_match($check['pattern'], $line)) {
                    $issues[] = [
                        'severity' => $check['severity'],
                        'message'  => $check['message'],
                        'file'     => $relativePath,
                        'line'     => $lineNumber,
                    ];
                    break; // One issue per line max
                }
            }
        }

        return $issues;
    }

    protected function getRelativePath(SplFileInfo $file): string
    {
        $path = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
        return str_replace('\\', '/', $path);
    }
}
