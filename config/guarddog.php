<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scan Paths
    |--------------------------------------------------------------------------
    |
    | Directories to scan for security vulnerabilities. Paths are relative
    | to the Laravel project base path.
    |
    */
    'scan_paths' => [
        'app/',
        'routes/',
        'resources/views/',
        'config/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore Paths
    |--------------------------------------------------------------------------
    |
    | Directories or files to exclude from scanning. Supports glob patterns.
    |
    */
    'ignore_paths' => [
        'vendor/',
        'node_modules/',
        'storage/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Output Path
    |--------------------------------------------------------------------------
    |
    | Where to save the generated HTML security report.
    |
    */
    'report_output_path' => storage_path('guarddog-security-report.html'),

];
