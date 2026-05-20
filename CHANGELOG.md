# Changelog

All notable changes to **Laravel GuardDog** are documented here.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning follows [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

- Dependency vulnerability scanner (check `composer.json` against CVE advisories)
- Auto fix suggestions for detected issues
- GitHub Actions report annotations
- Historical security score tracking
- Dashboard UI

---

## [1.0.0] — 2025-05-19

### Added

- Initial release of Laravel GuardDog
- **SQL injection scanner** — detects raw `DB::statement()`, `DB::raw()`, and `whereRaw()` calls with variable interpolation
- **CSRF scanner** — detects Blade forms missing `@csrf`
- **Route scanner** — finds routes with no `auth` middleware
- **File upload scanner** — detects `$request->file()` calls without MIME or size validation
- **Environment scanner** — flags `APP_DEBUG=true`, missing `APP_KEY`, and unsafe `.env` values
- **Security score system** — scores your app 0–100; deducts points per severity (Critical −15, Warning −7, Notice −3)
- **Beautiful HTML report** — circular progress bar, severity badges, file paths with line numbers
- **Console reporter** — colored terminal output with CRITICAL / WARNING / NOTICE grouping
- **`php artisan guarddog:scan`** command with `--no-html` and `--output=` options
- **`config/guarddog.php`** for customising scan paths, ignore paths, and report output location
- Support for Laravel **8, 9, 10, 11, 12, 13** and PHP **7.4, 8.0, 8.1, 8.2, 8.3**
- MIT licence

---

[Unreleased]: https://github.com/JaydeepGadhiya/laravel-guarddog/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/JaydeepGadhiya/laravel-guarddog/releases/tag/v1.0.0
