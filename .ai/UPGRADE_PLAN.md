# Analysis HMS - Laravel 10 → 12 Upgrade Plan

**Created**: August 7, 2026
**Status**: 📋 PLAN (not yet executed)
**References**: `.ai/RESEARCH.md` (2026 ecosystem facts), `.ai/ANALYSIS_REPORT.md`, `.ai/DECISIONS.md` (Decisions 12–14)
**Confidence**: HIGH for L10→L11 steps; MEDIUM-HIGH for L11→L12 (verify each `composer update` result)

---

## 0. Why Upgrade (Risk Drivers)

| Driver | Detail |
|--------|--------|
| 🔴 Laravel 10 **EOL** | Security fixes ended early 2025 — zero security patches today |
| 🔴 PHP 8.2 **EOL Dec 31, 2026** | Security-only now; project already on 8.2.33 (final 8.2 release) |
| 🟠 **AIKIDO-2025-10705** (RCE) | Affects `yajra/laravel-datatables-oracle` 5.11.7–12.5.1; our `^10.11` is in range |
| 🟠 phpspreadsheet CVEs | 2025–26 SSRF/XSS/RCE advisories (CVE-2026-34084 etc.) |
| 🟠 websockets abandoned | `beyondcode/laravel-websockets` unmaintained, incompatible with L11/12 |

**Target stack after upgrade**: Laravel 12.x + PHP 8.3/8.4 + Laravel Reverb + yajra datatables v12.6+/v13 + latest phpspreadsheet.

---

## 1. Current State (verified 2026-08-07)

| Item | Value |
|------|-------|
| PHP | 8.2.33 (ZTS, VC19 x64, XAMPP) |
| Laravel | 10.50.2 |
| Skeleton | **Classic L10**: `app/Http/Kernel.php` (aliases+groups), `app/Exceptions/Handler.php`, `app/Console/Kernel.php`, classic `bootstrap/app.php`, `app/Providers/*` (5 providers incl. RouteServiceProvider) |
| Composer | 2.10.2; `minimum-stability: dev` |
| Key deps | sanctum ^3.2, ui ^4.2, websockets ^1.14, datatables ^10.11, kyslik/column-sortable ^6.5, dompdf ^3.0, phpspreadsheet ^5.0, endroid/qr-code ^5.0, simple-qrcode ~4, stichoza/google-translate-php `*`, symfony/http-client ^7.3, mailgun-mailer ^7.1 |
| Dev deps | collision ^7.0, ignition ^2.0, pint ^1.0, phpunit ^10.1, kitloong/migrations-generator ^7.0, sail, mockery, faker |
| Structure | `routes/console.php` ✅ exists, `routes/channels.php` ✅ exists, `lang/` ❌ none, 18 config files, `config/websockets.php` + `config/datatables.php` exist |
| Events | `SaleBillPrintEvent`, `PrintEvent` (ShouldBroadcast, Pusher-compatible) |
| Tests | 26 passing (SQLite in-memory, array cache) — good baseline |
| Git | ❌ **NO git repo** (must fix FIRST) |

---

## 2. Phase 0 — Pre-Flight (DO NOT SKIP)

> ⏱ Estimate: 0.5 day. No risk. Prerequisite for everything below.

1. **Initialize Git (critical)** — no rollback without it:
   ```bash
   git init
   git add -A
   git commit -m "chore: baseline before Laravel 12 upgrade"
   ```
2. **Full backup** (zip project minus `vendor/`, plus a MySQL dump):
   ```bash
   mysqldump -u root db_analysishms > backup_analysishms_2026-08-07.sql
   ```
3. **Baseline checks**:
   ```bash
   composer audit                      # record current CVEs
   php artisan test                    # 26 tests must pass BEFORE any change
   php artisan about                   # sanity
   ```
4. **Enable deprecation logging** (catch L11/12 deprecations during upgrade):
   - In `.env`: `LOG_DEPRECATIONS_CHANNEL=deprecations`; ensure `config/logging.php` has a `deprecations` channel (add if missing).
5. **Know your helpers**: 7 helper files are in `composer.json` autoload `files` — they keep loading; no change needed.

---

## 3. Phase 1 — Safe Pre-Upgrades (still on Laravel 10)

> ⏱ Estimate: 1–1.5 days. Low risk, high value; reduces the L11/12 blast radius.

### 3.1 Patch vulnerable packages (stay on L10)
```bash
composer update phpoffice/phpspreadsheet --with-dependencies   # latest patched 5.x
composer audit                                                  # phpspreadsheet advisories should clear
```

### 3.2 Replace abandoned websockets → Laravel Reverb (⚠️ big one)
**Reverb works on Laravel 10.47+ / PHP 8.2+** — so we migrate NOW, before touching the framework. This keeps POS/print realtime working and removes the L11-incompatible package.

```bash
composer remove beyondcode/laravel-websockets
composer require laravel/reverb
php artisan reverb:install          # publishes config/reverb.php + updates broadcasting.php
```
Then:
- `.env`: set `BROADCAST_DRIVER=reverb`, `REVERB_SERVER_HOST=127.0.0.1`, `REVERB_SERVER_PORT=8080`, `REVERB_APP_ID=...`, `REVERB_APP_KEY=...`, `REVERB_APP_SECRET=...`; same values as old PUSHER vars so **Echo client config stays unchanged** (Echo uses the Pusher protocol; only `pusher.host/port` come from env).
- Remove `config/websockets.php`; update `config/broadcasting.php` `pusher` options to point at Reverb host/port.
- Start the server: `php artisan reverb:start` (test) / Supervisor in production.
- **Verify**: check-in/print flow still prints (SaleBillPrintEvent/PrintEvent) and POS screens receive updates.

### 3.3 (Optional) Upgrade datatables to the fixed line on L10
`composer require yajra/laravel-datatables-oracle:^12.6 --with-all-dependencies` — fixes AIKIDO-2025-10705 while still supporting Laravel 10.
- Re-publish config: `php artisan vendor:publish --tag=datatables --force`
- If any DataTables JS/API usage differs (v10→v12 config keys), fix per yajra upgrade notes; test ReportController/ReportController datatables endpoints.
- If this causes friction, **defer to Phase 3** — but don't ship without the fix.

### 3.4 Re-verify after Phase 1
```bash
php artisan test && php artisan route:list > /tmp/routes_before.txt
```
Manual smoke: Login → POS (KOT + print) → Reservation → Reports (any DataTables page) → E-Invoice.

---

## 4. Phase 2 — Laravel 10 → 11

> ⏱ Estimate: 1–2 days. The biggest structural change (skeleton migration).

### 4.1 Update constraints
```bash
composer require laravel/framework:^11.0 --with-all-dependencies
composer require laravel/sanctum:^4.0
composer require --dev nunomaduro/collision:^8.0
composer require --dev spatie/laravel-ignition:^2.5
composer require --dev phpunit/phpunit:^11.0
composer require --dev kitloong/laravel-migrations-generator:^8.0
composer require --dev laravel/pint:^1.15   # or ^2 if resolver allows
composer update
```

### 4.2 Migrate skeleton (L11 removed 3 kernel files)
1. **Rewrite `bootstrap/app.php`** to the fluent builder:
   ```php
   <?php
   use Illuminate\Foundation\Application;
   use Illuminate\Foundation\Configuration\Exceptions;
   use Illuminate\Foundation\Configuration\Middleware;

   return Application::configure(basePath: dirname(__DIR__))
       ->withRouting(
           web: __DIR__.'/../routes/web.php',
           commands: __DIR__.'/../routes/console.php',
           channels: __DIR__.'/../routes/channels.php',
           health: '/up',
       )
       ->withMiddleware(function (Middleware $middleware) {
           // Move EVERYTHING from app/Http/Kernel.php here:
           $middleware->append(\App\Http\Middleware\TrustHosts::class);
           $middleware->append(\App\Http\Middleware\TrustProxies::class);
           $middleware->append(\App\Http\Middleware\PreventRequestsDuringMaintenance::class);
           $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
           $middleware->append(\App\Http\Middleware\EncryptCookies::class);
           $middleware->append(\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class);
           $middleware->append(\Illuminate\Session\Middleware\StartSession::class);
           // ... session lifetime / share errors / verify csrf / substitute bindings ...
           $middleware->append(\App\Http\Middleware\LogActivity::class);       // if global
           $middleware->append(\App\Http\Middleware\LogThirdPartyActivity::class);

           $middleware->alias([
               'auth'        => \App\Http\Middleware\Authenticate::class,
               'company'     => \App\Http\Middleware\company::class,
               'staff'       => \App\Http\Middleware\staff::class,
               'superadmin'  => \App\Http\Middleware\superadmin::class,
               'user'        => \App\Http\Middleware\user::class,
               'frontlogin'  => \App\Http\Middleware\frontlogin::class,
               'api.auth'    => \App\Http\Middleware\ApiAuth::class,
               'protect'     => \App\Http\Middleware\ProtectRoute::class,
               'session.lifetime' => \App\Http\Middleware\SetToolsSessionLifetime::class,
               'guest'       => \App\Http\Middleware\RedirectIfAuthenticated::class,
               'signed'      => \Illuminate\Routing\Middleware\ValidateSignature::class,
               'throttle'    => \Illuminate\Routing\Middleware\ThrottleRequests::class,
               'verified'    => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
               // ... plus any other aliases currently in Kernel::$middlewareAliases
           ]);

           $middleware->group('web', [ /* any custom group additions */ ]);
           $middleware->group('api', [ \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class ]);
       })
       ->withExceptions(function (Exceptions $exceptions) {
           // Move app/Exceptions/Handler.php logic here (report(), render(), register())
       })->create();
   ```
2. **Delete**: `app/Http/Kernel.php`, `app/Exceptions/Handler.php`, `app/Console/Kernel.php`.
3. **Move scheduled tasks** from `Console\Kernel::schedule()` into `routes/console.php`:
   ```php
   use Illuminate\Support\Facades\Schedule;
   Schedule::call(function () { /* autoCharge */ })->hourly(); // preserve existing cron jobs
   // Also register any existing Artisan commands via Artisan::command(...) as needed
   ```
4. **Providers**: remove `App\Providers\RouteServiceProvider` (and optionally `BroadcastServiceProvider`) from `config/app.php` `providers`; delete the files (routing + channels are now handled by `withRouting`).

### 4.3 Deprecation sweep
- Run app with `LOG_DEPRECATIONS_CHANNEL=deprecations`; fix any L11 deprecations logged (helpers/`Str::random`, container binding style).
- Run `php artisan optimize:clear` and `php artisan test`; fix failures.
- `composer audit` again.

### 4.4 Verify
- `php artisan about` → Laravel 11.x
- `php artisan route:list` and compare to `/tmp/routes_before.txt` (should be identical — route names/URIs unchanged)
- Smoke: all 10 modules' main pages (use the routes file list from `.ai/ROUTES.md`).

---

## 5. Phase 3 — Laravel 11 → 12

> ⏱ Estimate: 0.5–1 day. Much smaller than Phase 2 (same skeleton).

```bash
composer require laravel/framework:^12.0 --with-all-dependencies
composer require yajra/laravel-datatables-oracle:^13.0   # or keep ^12.6+ (already fixed)
composer require --dev nunomaduro/collision:^8.6         # resolves for L12
composer update
```
- Symfony is already at ^7.3 (compatible with L12's Symfony 7 stack).
- Re-publish any package configs flagged by `php artisan vendor:publish`.
- Check `bootstrap/app.php` still valid (no changes expected between L11→L12 for this skeleton).
- `php artisan test`, `route:list` diff, smoke test again.

---

## 6. Phase 4 — Hardening After Upgrade

> ⏱ Estimate: 1 day. Can run in parallel with production testing.

1. **PHP upgrade** (Decision 14): install PHP 8.4 in XAMPP, re-enable extensions (gd, intl, bcmath, sqlsrv/pdo_sqlsrv, zip, openssl, mbstring, fileinfo), re-verify `php artisan test`.
2. **OPcache + JIT** for XAMPP `php.ini` — full snippet in `.ai/RESEARCH.md` §4.1.
3. **Production config**: `APP_ENV=production`, `APP_DEBUG=false`; `php artisan config:cache route:cache view:cache`.
4. **Redis (optional but recommended)**: `QUEUE_CONNECTION=redis`, `CACHE_DRIVER=redis`, `SESSION_DRIVER=redis` + queue workers via Supervisor + `php artisan queue:failed-table`.
5. **CI baseline**: add `composer audit` + `php artisan test` to CI once a repo exists.
6. **Reverb in production**: run `php artisan reverb:start` under Supervisor (tls option for wss in production).

---

## 7. Project-Specific Risk Register

| # | Risk | Likelihood | Mitigation |
|---|------|-----------|------------|
| R1 | **Middleware aliases missing after Kernel deletion** → 401/403 everywhere | HIGH | Copy `$middlewareAliases` from old `app/Http/Kernel.php` verbatim into `withMiddleware()->alias()` (Phase 2.2) — file is only 6 KB, easy to mirror |
| R2 | God controllers (22K-line CompanyController) trigger deprecations/edge breaks | MED | Deprecation log on; fix top deprecations; keep old `$request->input()` style (still supported in L12) |
| R3 | `kyslik/column-sortable` unmaintained → sortable() macro breaks | MED | Test; if broken, remove and implement `orderBy` from request params in affected controllers (small, localised) |
| R4 | `stichoza/google-translate-php` `"*"` constraint drifts | LOW | Pin to a known-good version after upgrade |
| R5 | `laravel/ui` v4 vs L12 | LOW | Pin `^4.5`; app uses custom auth (PythonAuth/auto-login), ui is only used for scaffolding |
| R6 | Datatables v10→v13 config/API changes | MED | Re-publish config; smoke every DataTables page (Reporting, ReportController) |
| R7 | Reverb vs old websockets ports/SSL | MED | Keep Echo client config (Pusher protocol); only host/port/env change |
| R8 | Migration runner changes (Doctrine removal in L11) | LOW | No runtime DB tooling in app; `migrations-generator` is dev-only, kitloong v8 handles it |
| R9 | `minimum-stability: dev` + `"*"` constraints cause surprise updates | MED | Keep `prefer-stable`; pin `stichoza`; review `composer update` output |

**Rollback**: `git revert`/checkout of the Phase-baseline commits + restore MySQL dump. Never upgrade without Phase 0.

---

## 8. Validation Checklist (run at EVERY phase end)

```bash
php artisan about                      # framework + PHP version
php artisan test                       # 26 tests green
php artisan route:list > current.txt   # diff vs baseline
composer audit                         # zero HIGH findings for runtime deps
composer validate                      # composer.json valid
php artisan optimize:clear             # stale cache can't hide issues
```

**Manual smoke (minimum)**: Login → POS KOT + print (Reverb) → Reservation → Check-in → Night Audit → Reports (DataTables) → E-Invoice → Excel export → QR print.

---

## 9. Suggested Sequencing & Timeline

| Phase | Duration | Depends on |
|-------|----------|------------|
| Phase 0 Pre-flight (git + backup + baseline) | 0.5 d | — |
| Phase 1 Safe upgrades (Reverb, patches) | 1–1.5 d | Phase 0 |
| Phase 2 Laravel 11 (skeleton migration) | 1–2 d | Phase 1 |
| Phase 3 Laravel 12 (datatables v13) | 0.5–1 d | Phase 2 |
| Phase 4 Hardening (PHP 8.4, OPcache, Redis, CI) | 1 d | Phase 3 |
| **Total** | **≈ 4–6 dev-days** | — |

**Suggested**: Do Phase 1 immediately (security + websockets), then Phase 2/3 in one focused sprint with a staging copy of the app.

---

## 10. Commands Summary (cheat sheet)

```bash
# Phase 0
git init && git add -A && git commit -m "chore: baseline"
mysqldump -u root db_analysishms > backup_analysishms_2026-08-07.sql
composer audit && php artisan test

# Phase 1
composer update phpoffice/phpspreadsheet --with-dependencies
composer remove beyondcode/laravel-websockets
composer require laravel/reverb && php artisan reverb:install
# (optional) composer require yajra/laravel-datatables-oracle:^12.6 --with-all-dependencies

# Phase 2
composer require laravel/framework:^11.0 --with-all-dependencies
composer require laravel/sanctum:^4.0
composer require --dev nunomaduro/collision:^8.0 spatie/laravel-ignition:^2.5 phpunit/phpunit:^11.0 kitloong/laravel-migrations-generator:^8.0
composer update
# rewrite bootstrap/app.php; delete 3 kernel files; move schedule to routes/console.php

# Phase 3
composer require laravel/framework:^12.0 --with-all-dependencies
composer require yajra/laravel-datatables-oracle:^13.0 --with-all-dependencies
composer update

# Phase 4
php artisan config:cache route:cache view:cache
php artisan queue:failed-table
```

---

*This plan is documentation only — executing it is a separate task. Suggested next step: run Phase 0 + Phase 1.2 (Reverb migration) as the first concrete action.*
