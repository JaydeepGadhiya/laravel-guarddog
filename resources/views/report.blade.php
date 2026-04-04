<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GuardDog Security Report — {{ $projectName }}</title>
    <style>
        :root {
            --bg: #0f172a;
            --card: #1e293b;
            --border: #334155;
            --text: #e2e8f0;
            --text-muted: #94a3b8;
            --critical: #ef4444;
            --critical-bg: rgba(239, 68, 68, 0.1);
            --warning: #f59e0b;
            --warning-bg: rgba(245, 158, 11, 0.1);
            --notice: #3b82f6;
            --notice-bg: rgba(59, 130, 246, 0.1);
            --success: #22c55e;
            --accent: #8b5cf6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 48px;
        }

        .header .logo {
            font-size: 48px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 4px;
        }

        .header .subtitle {
            color: var(--text-muted);
            font-size: 14px;
        }

        .header .project-name {
            font-size: 18px;
            color: var(--text-muted);
            margin-top: 8px;
        }

        /* Score Card */
        .score-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }

        .score-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, {{ $ratingColor }}, {{ $ratingColor }}88);
        }

        .score-circle {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            margin: 0 auto 24px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: conic-gradient(
                {{ $ratingColor }} {{ $score * 3.6 }}deg,
                var(--border) {{ $score * 3.6 }}deg
            );
        }

        .score-circle-inner {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: var(--card);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .score-number {
            font-size: 42px;
            font-weight: 800;
            color: {{ $ratingColor }};
            line-height: 1;
        }

        .score-total {
            font-size: 14px;
            color: var(--text-muted);
        }

        .score-rating {
            font-size: 24px;
            font-weight: 600;
            color: {{ $ratingColor }};
            margin-bottom: 8px;
        }

        .score-bar {
            width: 100%;
            max-width: 400px;
            height: 8px;
            background: var(--border);
            border-radius: 4px;
            margin: 16px auto 0;
            overflow: hidden;
        }

        .score-bar-fill {
            height: 100%;
            width: {{ $score }}%;
            background: {{ $ratingColor }};
            border-radius: 4px;
            transition: width 1s ease;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
        }

        .stat-card .stat-value {
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-card .stat-label {
            color: var(--text-muted);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-critical .stat-value { color: var(--critical); }
        .stat-warning .stat-value { color: var(--warning); }
        .stat-notice .stat-value { color: var(--notice); }
        .stat-files .stat-value { color: var(--accent); }

        /* Issues Table */
        .issues-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 32px;
        }

        .issues-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .issues-header h2 {
            font-size: 18px;
            font-weight: 600;
        }

        .issues-count {
            background: var(--border);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .issues-table {
            width: 100%;
            border-collapse: collapse;
        }

        .issues-table thead th {
            text-align: left;
            padding: 12px 24px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            font-weight: 600;
        }

        .issues-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .issues-table tbody tr:last-child {
            border-bottom: none;
        }

        .issues-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .issues-table td {
            padding: 14px 24px;
            font-size: 14px;
        }

        /* Severity Badges */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .badge-critical {
            background: var(--critical-bg);
            color: var(--critical);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .badge-warning {
            background: var(--warning-bg);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-notice {
            background: var(--notice-bg);
            color: var(--notice);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .file-path {
            color: var(--text-muted);
            font-family: 'Cascadia Code', 'Fira Code', monospace;
            font-size: 13px;
        }

        .line-number {
            color: var(--accent);
            font-family: 'Cascadia Code', 'Fira Code', monospace;
            font-size: 13px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            color: var(--text-muted);
        }

        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .empty-state p {
            font-size: 16px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 32px;
            color: var(--text-muted);
            font-size: 13px;
        }

        .footer a {
            color: var(--accent);
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px 16px;
            }

            .score-card {
                padding: 24px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .issues-table td,
            .issues-table th {
                padding: 10px 12px;
            }

            .issues-table .hide-mobile {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">&#128021;</div>
            <h1>GuardDog Security Report</h1>
            <div class="project-name">{{ $projectName }}</div>
            <div class="subtitle">Scanned on {{ $scanDate }}</div>
        </div>

        <!-- Score Card -->
        <div class="score-card">
            <div class="score-circle">
                <div class="score-circle-inner">
                    <div class="score-number">{{ $score }}</div>
                    <div class="score-total">/ 100</div>
                </div>
            </div>
            <div class="score-rating">{{ $rating }}</div>
            <div class="score-bar">
                <div class="score-bar-fill"></div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card stat-files">
                <div class="stat-value">{{ $filesScanned }}</div>
                <div class="stat-label">Files Scanned</div>
            </div>
            <div class="stat-card stat-critical">
                <div class="stat-value">{{ $criticalCount }}</div>
                <div class="stat-label">Critical</div>
            </div>
            <div class="stat-card stat-warning">
                <div class="stat-value">{{ $warningCount }}</div>
                <div class="stat-label">Warnings</div>
            </div>
            <div class="stat-card stat-notice">
                <div class="stat-value">{{ $noticeCount }}</div>
                <div class="stat-label">Notices</div>
            </div>
        </div>

        <!-- Issues Table -->
        <div class="issues-section">
            <div class="issues-header">
                <h2>Issues Found</h2>
                <span class="issues-count">{{ $totalIssues }} total</span>
            </div>

            @if($totalIssues > 0)
                <table class="issues-table">
                    <thead>
                        <tr>
                            <th>Severity</th>
                            <th>Message</th>
                            <th>File</th>
                            <th>Line</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($issues as $issue)
                            <tr>
                                <td>
                                    @php $sev = strtoupper($issue['severity']); @endphp
                                    <span class="badge badge-{{ strtolower($sev) }}">{{ $sev }}</span>
                                </td>
                                <td>{{ $issue['message'] }}</td>
                                <td class="file-path">{{ $issue['file'] }}</td>
                                <td class="line-number">{{ $issue['line'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="icon">&#9989;</div>
                    <p>No security issues found. Your project looks great!</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            Generated by <strong>Laravel GuardDog</strong> &mdash; jaydeep/laravel-guarddog
        </div>
    </div>
</body>
</html>
