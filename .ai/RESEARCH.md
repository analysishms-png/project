# Analysis HMS - Web Research Log (2026)

**Research Date**: August 7, 2026
**Purpose**: Keep the AI workspace and project roadmap aligned with current (mid-2026) ecosystem reality — framework support, PHP lifecycle, package security advisories, and best practices.
**Method**: Multi-agent web research (official docs, PHP.net, Laravel release notes, Packagist, GitHub Security Advisories, 2026 community guides).

---

## 1. Laravel Framework Status

### Laravel 10 — END OF LIFE ⚠️
- Released: February 2023
- Bug fixes ended: August 2024
- **Security fixes ended: early 2025 → Laravel 10 is EOL and NO LONGER receives security patches**
- Project is on `laravel/framework 10.50.2` (the final 10.x release)

### Laravel 11
- Released: March 12, 2024
- **Security fixes concluded: March 12, 2026** (now EOL)
- Requires PHP 8.2–8.4
- Slimmer application skeleton (fewer boilerplate files), streamlined middleware

### Laravel 12 (current recommended target)
- Released: February 2025
- Security fixes until: ~February 2027
- Requires **PHP 8.2+**, supports PHP 8.2–8.5
- Production recommendation: **PHP 8.3 or 8.4**
- Upgrades to Symfony 7 (faster routing), modern starter kits

### Upgrade Path for This Project
```
Laravel 10.50.2 (EOL) → Laravel 11 → Laravel 12 (current)
```
- Follow official upgrade guides: `laravel.com/docs/12.x/upgrade`
- Must run: `composer update` with new constraints, review deprecations, update `bootstrap/app.php` structure if moving to L11 style.

---

## 2. PHP Support Status

| Version | Release | Active Support | Security-Only | **EOL** |
|---------|---------|----------------|---------------|---------|
| PHP 8.2 | Dec 2022 | ended Dec 31, 2024 | **since Jan 1, 2025** | **Dec 31, 2026** ⚠️ |
| PHP 8.3 | Nov 2023 | ended Dec 31, 2025 | security-only | Dec 31, 2027 |
| PHP 8.4 | Nov 2024 | **until Dec 31, 2026** | after | Dec 31, 2028 |
| PHP 8.5 | Nov 2025 | until Dec 31, 2027 | after | Dec 31, 2029 |

- Project runs **PHP 8.2.33** — this is the final 8.2 point release and is correctly patched, but **8.2 reaches EOL on Dec 31, 2026**.
- **Action**: After upgrading Laravel to 12, plan PHP upgrade to **8.3 or 8.4** (before Dec 2026).
- Historical note: Windows/CGI flaw **CVE-2024-4577** was fixed in 8.2 point releases — staying current on point releases matters (8.2.33 ✅).
- Post-EOL options if stuck: commercial extended support (e.g., HeroDevs NES).

---

## 3. Package Security & Maintenance Status (2025–2026)

| Package | Installed | Status | Advisories / Notes | Action |
|---------|-----------|--------|--------------------|--------|
| `beyondcode/laravel-websockets` | ^1.14 | 🔴 **ABANDONED** | Unmaintained for years; incompatible with Laravel 11/12; relies on old ReactPHP/Ratchet | Replace with **Laravel Reverb** (official first-party) or **Soketi** |
| `yajra/laravel-datatables-oracle` | ^10.11 | 🟠 Active | **AIKIDO-2025-10705 (High, Oct 2025): RCE via dynamic Blade template eval** — affects versions 5.11.7 → 12.5.1 (our 10.11.x is in range) | Upgrade to **v12.6.0+ / v13+** (requires Laravel 11/12) |
| `phpoffice/phpspreadsheet` | ^5.0 (5.7.0) | 🟠 Active, HIGH CVE churn | CVE-2025-54370 (SSRF), CVE-2025-22131 (XSS), **CVE-2026-34084 (RCE) + bypass CVE-2026-45034**, July 2026 memory-exhaustion GHSA (OLE/Gzip) | Pin to **latest patched version**; run `composer audit` regularly |
| `simplesoftwareio/simple-qrcode` | ~4 | 🟡 Stagnant | No direct CVE; depends on old bacon/qr-code | Migrate to **endroid/qr-code** (already installed, ^5.0; upgrade to 6.x) |
| `barryvdh/laravel-dompdf` | ^3.0 | 🟢 Active | Remote access **disabled by default** since 3.x (good); keep updated for underlying dompdf fixes | Update to latest 3.x |
| `endroid/qr-code` | ^5.0 | 🟢 Active | v6.x supports PHP 8.4; no advisories | Upgrade to 6.x when convenient |
| `laravel/sanctum` | ^3.2 | 🟢 Active (first-party) | No critical unpatched CVEs for standard token/SPA flows | Keep updated |

**Recommended habit**: run `composer audit` after every `composer install/update` and in CI.

---

## 4. Performance Best Practices (2026) — for this project

### 4.1 OPcache for PHP 8.2 on XAMPP/Windows
Add to `C:\xampp\php\php.ini`:
```ini
[opcache]
zend_extension=opcache
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=1
opcache.revalidate_freq=2
opcache.fast_shutdown=1

; PHP 8.2+ JIT
opcache.jit=tracing
opcache.jit_buffer_size=64M
```
(Tip: `opcache.validate_timestamps=0` in strict production only; keep 1 during development.)

### 4.2 Kill N+1 Queries
- The project currently has **zero `->with()` eager loading** (verified in deep analysis) — biggest single performance win available.
- Use `Model::preventLazyLoading(! app()->isProduction())` in `AppServiceProvider::boot()` to catch N+1 during dev.

### 4.3 Redis for Production
- Replace `CACHE_DRIVER=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync` with Redis in production.
- File sessions cause lock contention; DB sessions churn the DB; Redis handles thousands of concurrent ops.
- Run queue workers as daemons: `php artisan queue:work redis --tries=3 --timeout=60 --memory=512`
- Use **Laravel Horizon** for Redis queue monitoring; always create the failed-jobs table.

### 4.4 composer audit in CI
Block deploys that introduce known CVEs.

---

## 5. Security Best Practices (2026) — direct applicability

| Topic | Rule |
|-------|------|
| XSS | Blade `{{ }}` escapes automatically. **Never `{!! !!}` user input** (project has 3 unescaped `$ticket->problem` outputs — fix) |
| SQL Injection | Never concatenate user input into `DB::raw()` / `whereRaw()`. Use `whereRaw('col = ?', [$val])` bindings. Verify Reporting/Tools dynamic SQL sources |
| CSRF | Keep `VerifyCsrfToken` on; use `@csrf` on all forms; Sanctum `csrf-cookie` flow for SPAs |
| Mass Assignment | Keep `$fillable` (project already does this everywhere ✅) |
| Debug | `APP_DEBUG=false`, `APP_ENV=production` before public deployment |
| Secrets | `.env` is gitignored ✅ — but **no git repo exists yet** → initialize Git |

### Laravel 11/12 feature highlights relevant to upgrading
- Slimmer skeleton → faster bootstrap, less boilerplate
- Symfony 7 under the hood (Laravel 12) → faster routing
- New starter kits (Inertia 2, WorkOS AuthKit) — optional, not required for this legacy-style app

---

## Sources
- PHP.net — release/support lifecycle & OPcache docs
- laravel.com — release notes & support policy (docs/12.x/upgrade)
- Packagist — abandonment flags (beyondcode/laravel-websockets, simple-qrcode)
- GitHub Security Advisories / GHSA — phpspreadsheet 2025–2026 advisories, datatables AIKIDO-2025-10705
- Laracopilot — Laravel Performance Optimization Checklist (2026)
- ZeriFlow / JustTotalTech — Laravel security best practices 2026

---

## Impact Summary for This Project
0. **VERIFIED 2026-08-07 via `composer audit`**: 29 advisories / 6 packages (dompdf 3.1.5, guzzle 7.11.0 HIGH CVE-2026-69246, psr7 2.11.0, commonmark 2.8.2, phpspreadsheet 5.7.0 HIGH, laravel/framework 10.50.2) + abandoned websockets. 5 packages fixable on Laravel 10; framework needs L12 (12.60.0+).
1. 🔴 **Laravel 10 is EOL** → plan upgrade to Laravel 12 (PHP 8.3/8.4) — high priority roadmap item
2. 🔴 **PHP 8.2 EOL Dec 31, 2026** → plan PHP 8.3/8.4 upgrade alongside Laravel
3. 🟠 **yajra datatables ^10.11 is in the RCE advisory range** (AIKIDO-2025-10705) → upgrade to 12.6.0+/13 with the Laravel upgrade
4. 🟠 **phpspreadsheet** needs latest patched version + `composer audit` in workflow
5. 🟠 **laravel-websockets abandoned** → migrate to Laravel Reverb
6. 🟢 OPcache + JIT, eager loading, and Redis remain the top performance wins
