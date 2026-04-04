<?php

namespace Jaydeep\GuardDog\Scanners;

use SplFileInfo;

class UploadScanner
{
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

            // Detect file upload without validation
            if ($this->hasFileUpload($line)) {
                // Check surrounding lines for validation
                $context = $this->getSurroundingLines($lines, $index, 10);

                if (!$this->hasValidation($context)) {
                    $issues[] = [
                        'severity' => 'WARNING',
                        'message'  => 'File upload without validation',
                        'file'     => $relativePath,
                        'line'     => $lineNumber,
                    ];
                }
            }

            // Detect move_uploaded_file usage
            if (preg_match('/move_uploaded_file\s*\(/', $line)) {
                $issues[] = [
                    'severity' => 'CRITICAL',
                    'message'  => 'Native move_uploaded_file() used — use Laravel file handling with validation',
                    'file'     => $relativePath,
                    'line'     => $lineNumber,
                ];
            }
        }

        return $issues;
    }

    protected function hasFileUpload(string $line): bool
    {
        $patterns = [
            '/\$request\s*->\s*file\s*\(/',
            '/->store\s*\(/',
            '/->storeAs\s*\(/',
            '/->storePublicly\s*\(/',
            '/->storePubliclyAs\s*\(/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    protected function hasValidation(string $context): bool
    {
        $validationPatterns = [
            '/->validate\s*\(/',
            '/Validator\s*::\s*make\s*\(/',
            '/\$this\s*->\s*validate\s*\(/',
            '/\'mimes\s*:/',
            '/"mimes\s*:/',
            '/\'mimetypes\s*:/',
            '/\'file\'/',
            '/\'image\'/',
            '/\'max\s*:/',
        ];

        foreach ($validationPatterns as $pattern) {
            if (preg_match($pattern, $context)) {
                return true;
            }
        }

        return false;
    }

    protected function getSurroundingLines(array $lines, int $index, int $range): string
    {
        $start = max(0, $index - $range);
        $end = min(count($lines) - 1, $index + $range);
        $context = '';

        for ($i = $start; $i <= $end; $i++) {
            $context .= $lines[$i] . "\n";
        }

        return $context;
    }

    protected function getRelativePath(SplFileInfo $file): string
    {
        $path = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
        return str_replace('\\', '/', $path);
    }
}
