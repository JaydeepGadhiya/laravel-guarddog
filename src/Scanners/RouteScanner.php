<?php

namespace Jaydeep\GuardDog\Scanners;

use SplFileInfo;

class RouteScanner
{
    protected array $authMiddleware = [
        'auth',
        'auth:sanctum',
        'auth:api',
        'auth:web',
        'verified',
    ];

    /**
     * Route patterns that should be protected — flagged as WARNING.
     */
    protected array $sensitivePatterns = [
        '/admin',
        '/dashboard',
        '/account',
        '/settings',
        '/profile',
        '/user',
        '/users',
        '/billing',
        '/payment',
        '/order',
        '/manage',
        '/panel',
        '/api/',
    ];

    /**
     * Routes that are normally public — skip entirely.
     */
    protected array $publicPatterns = [
        '/',
        '/login',
        '/register',
        '/password',
        '/forgot-password',
        '/reset-password',
        '/email/verify',
        '/home',
        '/about',
        '/contact',
        '/privacy',
        '/terms',
        '/welcome',
        '/health',
        '/up',
    ];

    public function scan(array $files): array
    {
        $issues = [];

        foreach ($files as $file) {
            if (!$this->isRouteFile($file)) {
                continue;
            }

            $issues = array_merge($issues, $this->scanFile($file));
        }

        return $issues;
    }

    protected function isRouteFile(SplFileInfo $file): bool
    {
        $path = str_replace('\\', '/', $file->getPathname());
        return strpos($path, 'routes/') !== false && $file->getExtension() === 'php';
    }

    protected function scanFile(SplFileInfo $file): array
    {
        $issues = [];
        $content = file_get_contents($file->getPathname());
        $lines = explode("\n", $content);
        $relativePath = $this->getRelativePath($file);

        $inMiddlewareGroup = false;
        $braceDepth = 0;

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;
            $trimmed = trim($line);

            // Track middleware group blocks
            if (preg_match('/->middleware\s*\(\s*[\'"](?:auth|auth:\w+|verified)[\'"]/', $line)) {
                $inMiddlewareGroup = true;
                $braceDepth = 0;
            }

            // Track Route::middleware('auth')->group(...)
            if (preg_match('/Route\s*::\s*middleware\s*\(\s*[\[\'"].*(?:auth|auth:\w+|verified)/', $line)) {
                $inMiddlewareGroup = true;
                $braceDepth = 0;
            }

            if ($inMiddlewareGroup) {
                $braceDepth += substr_count($line, '{') + substr_count($line, '(');
                $braceDepth -= substr_count($line, '}') + substr_count($line, ')');
                if ($braceDepth <= 0 && (strpos($line, '}') !== false || strpos($line, ')') !== false)) {
                    $inMiddlewareGroup = false;
                }
            }

            // Detect route definitions without auth
            if (preg_match('/Route::(get|post|put|patch|delete|any|match)\s*\(/', $trimmed)) {
                if ($inMiddlewareGroup) {
                    continue;
                }

                if ($this->lineHasAuthMiddleware($line) || $this->lineHasAuthMiddleware($this->peekNextLines($lines, $index, 3))) {
                    continue;
                }

                // Extract route path from the line
                $routePath = $this->extractRoutePath($line);

                // Skip known public routes
                if ($this->isPublicRoute($routePath)) {
                    continue;
                }

                // Only flag sensitive routes as WARNING, ignore the rest
                if ($this->isSensitiveRoute($routePath)) {
                    $issues[] = [
                        'severity' => 'WARNING',
                        'message'  => "Sensitive route without auth middleware: {$routePath}",
                        'file'     => $relativePath,
                        'line'     => $lineNumber,
                    ];
                }
            }
        }

        return $issues;
    }

    protected function extractRoutePath(string $line): string
    {
        if (preg_match('/Route::\w+\s*\(\s*[\'"]([^\'"]+)[\'"]/', $line, $matches)) {
            return $matches[1];
        }
        return '';
    }

    protected function isSensitiveRoute(string $routePath): bool
    {
        $path = strtolower($routePath);
        foreach ($this->sensitivePatterns as $pattern) {
            if (strpos($path, strtolower($pattern)) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function isPublicRoute(string $routePath): bool
    {
        $path = rtrim(strtolower($routePath), '/');
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->publicPatterns as $pattern) {
            if ($path === strtolower($pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function lineHasAuthMiddleware(string $text): bool
    {
        foreach ($this->authMiddleware as $middleware) {
            if (strpos($text, $middleware) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function peekNextLines(array $lines, int $currentIndex, int $count): string
    {
        $peek = '';
        for ($i = 1; $i <= $count; $i++) {
            if (isset($lines[$currentIndex + $i])) {
                $peek .= $lines[$currentIndex + $i];
            }
        }
        return $peek;
    }

    protected function getRelativePath(SplFileInfo $file): string
    {
        $path = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
        return str_replace('\\', '/', $path);
    }
}
