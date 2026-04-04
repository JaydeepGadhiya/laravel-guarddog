<?php

namespace Jaydeep\GuardDog\Scanners;

use SplFileInfo;

class CSRFScanner
{
    public function scan(array $files): array
    {
        $issues = [];

        foreach ($files as $file) {
            $ext = $file->getExtension();
            if (!in_array($ext, ['php', 'blade'])) {
                // Also check blade.php files
                if (!preg_match('/\.blade\.php$/', $file->getFilename())) {
                    continue;
                }
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

        $inForm = false;
        $formStartLine = 0;
        $formHasCsrf = false;
        $formMethod = '';

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;

            // Detect form open tags
            if (preg_match('/<form[^>]*>/i', $line, $matches)) {
                $inForm = true;
                $formStartLine = $lineNumber;
                $formHasCsrf = false;

                // Check method
                if (preg_match('/method\s*=\s*["\']?(POST|PUT|PATCH|DELETE)["\']?/i', $line, $methodMatch)) {
                    $formMethod = strtoupper($methodMatch[1]);
                } else {
                    $formMethod = 'GET';
                }

                // Check if @csrf or csrf_field() is on the same line
                if ($this->lineHasCsrf($line)) {
                    $formHasCsrf = true;
                }
            }

            // Check for CSRF within form
            if ($inForm && $this->lineHasCsrf($line)) {
                $formHasCsrf = true;
            }

            // Detect form close
            if ($inForm && preg_match('/<\/form>/i', $line)) {
                if (!$formHasCsrf && $formMethod !== 'GET') {
                    $issues[] = [
                        'severity' => 'WARNING',
                        'message'  => "Missing @csrf token in {$formMethod} form",
                        'file'     => $relativePath,
                        'line'     => $formStartLine,
                    ];
                }

                $inForm = false;
                $formHasCsrf = false;
            }
        }

        return $issues;
    }

    protected function lineHasCsrf(string $line): bool
    {
        return preg_match('/@csrf\b/', $line)
            || preg_match('/csrf_field\s*\(\s*\)/', $line)
            || preg_match('/csrf_token\s*\(\s*\)/', $line)
            || preg_match('/_token/', $line);
    }

    protected function getRelativePath(SplFileInfo $file): string
    {
        $path = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
        return str_replace('\\', '/', $path);
    }
}
