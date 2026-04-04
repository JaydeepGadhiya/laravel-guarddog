<?php

namespace Jaydeep\GuardDog\Scanners;

class EnvScanner
{
    public function scan(array $files): array
    {
        $issues = [];
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $issues[] = [
                'severity' => 'NOTICE',
                'message'  => 'No .env file found',
                'file'     => '.env',
                'line'     => 0,
            ];
            return $issues;
        }

        $content = file_get_contents($envPath);
        $lines = explode("\n", $content);

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;
            $trimmed = trim($line);

            // Skip comments and empty lines
            if (empty($trimmed) || strpos($trimmed, '#') === 0) {
                continue;
            }

            // APP_DEBUG=true in non-local environments
            if (preg_match('/^APP_DEBUG\s*=\s*true$/i', $trimmed)) {
                $issues[] = [
                    'severity' => 'WARNING',
                    'message'  => 'APP_DEBUG is set to true — disable in production',
                    'file'     => '.env',
                    'line'     => $lineNumber,
                ];
            }

            // APP_ENV=local
            if (preg_match('/^APP_ENV\s*=\s*local$/i', $trimmed)) {
                $issues[] = [
                    'severity' => 'NOTICE',
                    'message'  => 'APP_ENV is set to local — ensure this is not a production server',
                    'file'     => '.env',
                    'line'     => $lineNumber,
                ];
            }

            // Empty APP_KEY
            if (preg_match('/^APP_KEY\s*=\s*$/', $trimmed)) {
                $issues[] = [
                    'severity' => 'CRITICAL',
                    'message'  => 'APP_KEY is empty — run php artisan key:generate',
                    'file'     => '.env',
                    'line'     => $lineNumber,
                ];
            }

            // Default/weak database password
            if (preg_match('/^DB_PASSWORD\s*=\s*$/', $trimmed)) {
                $issues[] = [
                    'severity' => 'WARNING',
                    'message'  => 'DB_PASSWORD is empty — set a strong database password',
                    'file'     => '.env',
                    'line'     => $lineNumber,
                ];
            }

            // Session driver set to file in production check
            if (preg_match('/^SESSION_DRIVER\s*=\s*file$/i', $trimmed)) {
                $issues[] = [
                    'severity' => 'NOTICE',
                    'message'  => 'SESSION_DRIVER is file — consider database or redis for production',
                    'file'     => '.env',
                    'line'     => $lineNumber,
                ];
            }
        }

        return $issues;
    }
}
