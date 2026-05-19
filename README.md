<div align="center">

# 🐕 Laravel GuardDog

### A security scanner for Laravel that finds vulnerabilities and generates beautiful HTML reports.

[![CI](https://github.com/JaydeepGadhiya/laravel-guarddog/actions/workflows/ci.yml/badge.svg)](https://github.com/JaydeepGadhiya/laravel-guarddog/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jaydeep/laravel-guarddog.svg?style=flat-square)](https://packagist.org/packages/jaydeep/laravel-guarddog)
[![Total Downloads](https://img.shields.io/packagist/dt/jaydeep/laravel-guarddog.svg?style=flat-square)](https://packagist.org/packages/jaydeep/laravel-guarddog)
[![License](https://img.shields.io/packagist/l/jaydeep/laravel-guarddog.svg?style=flat-square)](LICENSE.md)
[![Stars](https://img.shields.io/github/stars/JaydeepGadhiya/laravel-guarddog)]()
[![Code Style](https://img.shields.io/badge/code%20style-pint-blue.svg)]()
[![Static Analysis](https://img.shields.io/badge/static%20analysis-phpstan-brightgreen)]()

**Scan your Laravel app in seconds. Catch SQL injection, missing auth middleware, exposed secrets, and more — before they hit production.**

⭐ Star the repository if GuardDog helps secure your Laravel apps.

[Quick Start](#-quick-start) • [Features](#-features) • [Example Report](#-example-report) • [Configuration](#-configuration) • [FAQ](#-faq)

</div>

---

## 🚀 Why GuardDog?

You shouldn't need to be a security expert to ship a secure Laravel app. **GuardDog** is a zero-config static security analyzer that scans your codebase for the OWASP style mistakes Laravel developers actually make and produces a clean, shareable HTML report with a security score your team can rally around.

- ⚡ **Zero config** — works out of the box on any Laravel 8–13 project
- 🎯 **Laravel-aware** — understands routes, middleware, Eloquent, Blade, and `.env`
- 📊 **Beautiful HTML reports** with a 0–100 security score
- 🆓 **Free and open source** (MIT)

---

## 📦 Installation & Setup

Requires PHP 7.4+ and Laravel 8–13.

```bash
composer require jaydeep/laravel-guarddog --dev
```

Laravel auto-discovers the service provider. Publish the config (optional):

```bash
php artisan vendor:publish --tag=guarddog-config
```
---

## ⚡ Quick Start

Run a full scan:

```bash
php artisan guarddog:scan
```

That's it. GuardDog scans your project and writes an HTML report to `public/security-report.html`.

| Command | What it does |
|---|---|
| `php artisan guarddog:scan` | Full scan + HTML report |
| `php artisan guarddog:scan --no-html` | Console output only |
| `php artisan guarddog:scan --output=storage/report.html` | Custom report path |

## 📸 Screenshots

### Console Output

![Console Scan](https://raw.githubusercontent.com/JaydeepGadhiya/laravel-guarddog/main/docs/console-output.png)

### HTML Security Report

![HTML Report](https://raw.githubusercontent.com/JaydeepGadhiya/laravel-guarddog/main/docs/security-report.png)

---

## 🔍 Features

GuardDog scans for the most common Laravel security vulnerabilities:

### Code-level checks
- 🛑 **Raw SQL injection risks** — `DB::statement()`, `DB::raw()`, and `whereRaw()` with variable interpolation
- 🛑 **Unescaped Blade output** — `{!! $userInput !!}` flagged for XSS
- 🛑 **Mass assignment vulnerabilities** — models missing `$fillable` or `$guarded`
- 🛑 **Unsafe `eval()`, `shell_exec()`, `exec()`, `system()` usage**

### Configuration checks
- ⚠️ **`APP_DEBUG=true` in production**
- ⚠️ **Weak or missing `APP_KEY`**
- ⚠️ **Default database credentials**
- ⚠️ **`.env` accidentally committed to git**
- ⚠️ **Session/cookie security flags** (`SESSION_SECURE_COOKIE`, `SameSite`)

### Routes & middleware
- 🚧 **Routes missing `auth` middleware**
- 🚧 **CSRF exclusions** in `VerifyCsrfToken`
- 🚧 **Overly permissive CORS configuration**

### Dependencies
- 📦 **Outdated packages with known CVEs** (via Packagist advisories)
- 📦 **Composer `minimum-stability` set to dev**

> Don't see a check you need? [Open an issue](https://github.com/jaydeep/laravel-guarddog/issues) — new checks ship regularly.

---

## 📋 Example Report

```
╔══════════════════════════════════════════════════════════╗
║         🐕 Laravel GuardDog Security Report              ║
╚══════════════════════════════════════════════════════════╝

  Files scanned:   142
  Issues found:    5
  Security Score:  83 / 100  (Good)

  ● CRITICAL: 1     ● WARNING: 3     ● NOTICE: 1
──────────────────────────────────────────────────────────

  CRITICAL  Raw SQL with variable interpolation in DB::statement()
            File: app/Repositories/UserRepository.php:54

  WARNING   Route without auth middleware
            File: routes/web.php:23

──────────────────────────────────────────────────────────
  Full HTML report: public/security-report.html
```

The HTML report includes per-issue remediation guidance, code snippets, and a shareable score badge.

---

## 🤖 Continuous Integration

Fail your CI build when GuardDog finds critical issues:

```yaml
# .github/workflows/security.yml
- name: Run GuardDog security scan
  run: php artisan guarddog:scan --no-html --fail-on=critical
```

GitLab, CircleCI, and Bitbucket Pipelines work the same way — just call the artisan command in your job.

---

## ⚙️ Configuration

After publishing the config (`config/guarddog.php`), you can:

- **Disable specific checks** you don't care about
- **Set severity thresholds** for CI failure
- **Exclude paths** (e.g. `vendor/`, `database/seeders/`)
- **Customize the HTML report** title, logo, and theme

```php
return [
    'enabled_checks' => [
        'sql_injection',
        'unescaped_blade',
        'mass_assignment',
        'debug_mode',
        // ...
    ],

    'exclude_paths' => [
        'database/seeders',
        'database/factories',
    ],

    'fail_on' => 'critical', // critical | warning | notice
];
```

---
## 📊 Security Score

GuardDog scores your app from 0 to 100. Every issue subtracts points based on severity:

| Severity | Points lost | Examples |
|---|---|---|
| 🔴 Critical | -15 | SQL injection, `eval()`, debug in prod |
| 🟡 Warning | -5 | Missing auth middleware, weak session config |
| 🔵 Notice | -1 | Style/best-practice nits |

A score of **80+** is good. **90+** is excellent. **100** means GuardDog found nothing — though no scanner catches everything, so manual review still matters.

---
## 📊 Security Score

GuardDog scores your app from 0 to 100. Every issue subtracts points based on severity:

| Severity | Points lost | Examples |
|---|---|---|
| 🔴 Critical | -15 | SQL injection, `eval()`, debug in prod |
| 🟡 Warning | -5 | Missing auth middleware, weak session config |
| 🔵 Notice | -1 | Style/best-practice nits |

A score of **80+** is good. **90+** is excellent. **100** means GuardDog found nothing — though no scanner catches everything, so manual review still matters.

---

## ❓ FAQ

<details>
<summary><strong>How is this different from <code>enlightn/enlightn</code>?</strong></summary>

Enlightn is a fantastic, broader tool covering security, performance, and reliability. GuardDog is **security-focused, lightweight, and zero-config** — designed to drop into any project and run in seconds, with a beautiful report you can hand to a non-technical stakeholder. Use both together for the best coverage.
</details>

<details>
<summary><strong>Does GuardDog replace Roave Security Advisories?</strong></summary>

No — they complement each other. `roave/security-advisories` blocks installation of vulnerable packages at the Composer level. GuardDog scans **your application code** for vulnerabilities you wrote yourself.
</details>

<details>
<summary><strong>Will GuardDog catch every security issue?</strong></summary>

No static analyzer can. GuardDog catches a wide set of common Laravel-specific mistakes, but production security also requires manual code review, dependency scanning, penetration testing, and runtime protection. Treat GuardDog as one layer of defense.
</details>

<details>
<summary><strong>Does it send my code anywhere?</strong></summary>

No. GuardDog runs 100% locally. No telemetry, no phone-home, no external API calls.
</details>

<details>
<summary><strong>Can I add custom checks?</strong></summary>

Yes — GuardDog ships with an extensible check API. See the [Custom Checks guide](docs/custom-checks.md).
</details>

---

## 🤝 Contributing

If you've found a security issue, **please do not open a public issue**. Email `jaydeepgadhiya5699@gmail.com` directly.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent updates.

---
## 📜 License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).

---

<div align="center">

**Built with ❤️ for the Laravel & Open Source community by [Jaydeep Gadhiya](https://github.com/JaydeepGadhiya)**

</div>
