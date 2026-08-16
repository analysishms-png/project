# Analysis HMS - Known Bugs

## Bugs Overview

This document tracks known bugs and issues in the Analysis HMS project.

---

## Critical Issues

### BUG-001: PHP Version Incompatibility

**Status**: ✅ RESOLVED  
**Priority**: Critical  
**Module**: System  

**Description**:  
Project required PHP ^8.1 but was running PHP 8.0.30.

**Root Cause**:  
XAMPP installation had PHP 8.0.30.

**Resolution**:  
Upgraded to PHP 8.2.33.

**Date Resolved**: August 7, 2026

---

### BUG-002: Missing PHP Extensions

**Status**: ✅ RESOLVED  
**Priority**: Critical  
**Module**: System  

**Description**:  
Missing gd and intl extensions.

**Root Cause**:  
Extensions were disabled in php.ini.

**Resolution**:  
Enabled all required extensions in php.ini.

**Date Resolved**: August 7, 2026

---

### BUG-003: Composer Dependencies Not Installed

**Status**: ✅ RESOLVED  
**Priority**: Critical  
**Module**: System  

**Description**:  
Vendor directory missing.

**Root Cause**:  
Composer install not run.

**Resolution**:  
Ran composer install successfully.

**Date Resolved**: August 7, 2026

---

## Medium Issues

### BUG-004: Abandoned Package Warning

**Status**: ✅ RESOLVED (2026-08-07)  
**Priority**: Medium  
**Module**: WebSocket  

**Description**:  
Package beyondcode/laravel-websockets is abandoned.

**Root Cause**:  
Package maintainer stopped development.

**Impact**:  
Still functional but may have security risks.

**Resolution**:  
Migrated to **Laravel Reverb ^1.0** (Phase 1, 2026-08-07). BROADCAST_DRIVER=reverb, server 127.0.0.1:8080, events unchanged, dispatch sites try/catch-wrapped, legacy config + LoggingChannelManager deleted. Verified: reverb:start works, 26 tests pass.

**Remaining note**:  
REVERB_APP_* keys empty in dev — set real values before enabling frontend Echo client. Start server in production via Supervisor: `php artisan reverb:start`.

---

### BUG-005: Missing .env File

**Status**: ✅ RESOLVED  
**Priority**: Medium  
**Module**: Configuration  

**Description**:  
No .env file present.

**Root Cause**:  
.env file not included in repository.

**Resolution**:  
Created .env file with default configuration.

**Date Resolved**: August 7, 2026

---

### BUG-006: Missing Bootstrap Cache Directory

**Status**: ✅ RESOLVED  
**Priority**: Medium  
**Module**: System  

**Description**:  
bootstrap/cache directory missing.

**Root Cause**:  
Directory not created during installation.

**Resolution**:  
Created directory with proper permissions.

**Date Resolved**: August 7, 2026

---

## Low Issues

### BUG-007: Session Configuration

**Status**: ⚠️ PENDING  
**Priority**: Low  
**Module**: Session  

**Description**:  
Session driver set to 'file'.

**Impact**:  
May need Redis for production.

**Recommendation**:  
Configure Redis for session in production.

---

### BUG-008: Cache Configuration

**Status**: ⚠️ PENDING  
**Priority**: Low  
**Module**: Cache  

**Description**:  
Cache driver set to 'file'.

**Impact**:  
May need Redis for production.

**Recommendation**:  
Configure Redis for cache in production.

---

### BUG-009: Queue Configuration

**Status**: ⚠️ PENDING  
**Priority**: Low  
**Module**: Queue  

**Description**:  
Queue driver set to 'sync'.

**Impact**:  
May need database/Redis for production.

**Recommendation**:  
Configure proper queue driver for background jobs.

---

## Security Issues

### BUG-010: Debug Mode Enabled

**Status**: ⚠️ PENDING  
**Priority**: Medium  
**Module**: Security  

**Description**:  
APP_DEBUG=true in .env.

**Impact**:  
Exposes error details in production.

**Recommendation**:  
Set APP_DEBUG=false in production.

---

### BUG-011: Database Credentials

**Status**: ⚠️ PENDING  
**Priority**: Medium  
**Module**: Security  

**Description**:  
Default database credentials in .env.

**Impact**:  
Security risk if exposed.

**Recommendation**:  
Use strong, unique credentials.

---

## Performance Issues

### BUG-012: N+1 Query Potential

**Status**: ⚠️ MONITORING  
**Priority**: Low  
**Module**: Performance  

**Description**:  
Some controllers may have N+1 query issues.

**Impact**:  
Slow page loads with large datasets.

**Recommendation**:  
Review and optimize queries.

---

### BUG-013: Large Dataset Exports

**Status**: ⚠️ MONITORING  
**Priority**: Low  
**Module**: Performance  

**Description**:  
Excel/CSV exports may timeout with large datasets.

**Impact**:  
User experience degradation.

**Recommendation**:  
Implement chunked exports.

---

## Code Quality Issues

### BUG-014: Duplicate Logic

**Status**: ⚠️ REVIEW NEEDED  
**Priority**: Low  
**Module**: Code Quality  

**Description**:  
Some helper functions may be duplicated.

**Impact**:  
Maintenance overhead.

**Recommendation**:  
Refactor and consolidate.

---

### BUG-015: Inconsistent Naming

**Status**: ⚠️ REVIEW NEEDED  
**Priority**: Low  
**Module**: Code Quality  

**Description**:  
Mixed naming conventions.

**Impact**:  
Code readability.

**Recommendation**:  
Standardize naming conventions.

---

## Infrastructure Issues

### BUG-016: No Automated Testing

**Status**: ⚠️ PENDING  
**Priority**: Medium  
**Module**: Testing  

**Description**:  
Limited test coverage.

**Impact**:  
Regression risk.

**Recommendation**:  
Add unit and feature tests.

---

### BUG-017: No CI/CD Pipeline

**Status**: ⚠️ PENDING  
**Priority**: Medium  
**Module**: DevOps  

**Description**:  
No automated deployment.

**Impact**:  
Manual deployment errors.

**Recommendation**:  
Set up CI/CD pipeline.

---

### BUG-018: No Error Monitoring

**Status**: ⚠️ PENDING  
**Priority**: Medium  
**Module**: Monitoring  

**Description**:  
No external error monitoring.

**Impact**:  
Delayed issue detection.

**Recommendation**:  
Integrate Sentry or similar.

---

## Documentation Issues

### BUG-019: Missing API Documentation

**Status**: ⚠️ PENDING  
**Priority**: Low  
**Module**: Documentation  

**Description**:  
No API documentation.

**Impact**:  
Developer onboarding difficulty.

**Recommendation**:  
Create Swagger/OpenAPI docs.

---

### BUG-020: Missing Deployment Guide

**Status**: ⚠️ PENDING  
**Priority**: Low  
**Module**: Documentation  

**Description**:  
No deployment documentation.

**Impact**:  
Deployment errors.

**Recommendation**:  
Create deployment guide.

---

## Deep Analysis Findings (2026-08-07)

### BUG-021: No Git Repository

**Status**: ⚠️ PENDING  
**Priority**: 🔴 HIGH  
**Module**: DevOps/Security  

**Description**:  
Project has no `.git/` — zero version control.

**Root Cause**:  
Repository never initialized.

**Impact**:  
No rollback, no history, no protection against accidental loss or exposure of .env.

**Recommendation**:  
Run `git init`, commit baseline, and push to a private remote.

---

### BUG-022: Stored XSS in Ticket Views

**Status**: ✅ FIXED (2026-08-16)  
**Priority**: 🔴 HIGH  
**Module**: Support Tickets / Security  

**Description**:  
`{!! $ticket->problem !!}` rendered user-supplied ticket text without escaping in 3 views:
- `resources/views/tools/tickets.blade.php:394`
- `resources/views/admin/tools/tickets.blade.php:315`
- `resources/views/property/mytickets.blade.php:305`

**Root Cause**:  
Unescaped blade output of user-controlled content (plain-text textarea field).

**Impact**:  
Stored XSS — attacker can execute scripts in other admin/user sessions.

**Fix (2026-08-16)**:  
Replaced with `{{ nl2br(e($ticket->problem)) }}` in all 3 views — escapes content while preserving line breaks.  
**Verification**: grep (no leftovers) + `php artisan view:cache` (all 549 views compile) + `php artisan test` (27 passed).

---

### BUG-023: Dynamic SQL Interpolation Needs Verification

**Status**: ✅ VERIFIED SAFE (2026-08-16)  
**Priority**: 🟠 MEDIUM  
**Module**: Reporting/Tools / Security  

**Description**:  
- `Reporting.php` / `CheckRegister.php`: `{$code}`, `{$alias}`, `{$effectiveSnoExpr}` embedded in `DB::raw()` (aliases sanitized via preg_replace; codes derive from propertyid/revmast)
- `Tools/ToolsController.php:2428,2431,2508,2942,3080`: `DB::select("SHOW TABLES LIKE '{$tableName}'")` and `whereRaw($sqlWhere)` at 2534/2958/2997

**Verification result (2026-08-16)**:  
- `$allowedTables` is a **hardcoded whitelist**; `fetchtabledata`/`bulkupdaterecords`/etc. validate table names by exact match against `SHOW TABLES` and column names against `SHOW COLUMNS`.
- `whereRaw($sqlWhere)` is a **by-design** superadmin/support DB tool; `ToolsController::__construct` middleware requires auth AND (superadmin role=1/propertyid=10 OR propertyid=20) — regular users get redirected.
- Reporting interpolation sources are server-side (propertyid/revmast).

**Verdict**: Not exploitable by non-privileged users. **Re-audit if the ToolsController constructor guard is ever removed.**

---

### BUG-024: Debug Mode Enabled (confirm)

**Status**: ⚠️ PENDING  
**Priority**: 🟠 MEDIUM  
**Module**: Security  

**Description**:  
`APP_DEBUG=true`, `APP_ENV=local` currently set.

**Impact**:  
Stack traces and env details exposed if app is reachable publicly.

**Recommendation**:  
Set `APP_DEBUG=false` + `APP_ENV=production` before any public deployment.

---

### BUG-025: God Controllers / Zero Eager Loading

**Status**: ⚠️ REVIEW NEEDED  
**Priority**: 🟠 MEDIUM  
**Module**: Architecture / Performance  

**Description**:  
- CompanyController 22,468 lines; PrintController 6,000; InventoryController 5,924; Reporting 5,441; Banquet 4,712; ToolsController 4,662
- No `->with()` / `->load()` eager loading anywhere

**Impact**:  
N+1 queries on list/report pages; files too large to safely refactor or test.

**Recommendation**:  
Add eager loading on hot paths first; then split CompanyController into domain services/controllers.

---

### BUG-027: formatCurrency Helper Missing (docs ≠ code)

**Status**: ✅ FIXED (2026-08-16)  
**Priority**: 🟠 MEDIUM  
**Module**: Helpers / Tests  

**Description**:  
`.ai` docs (2026-08-07) claimed `formatCurrency` was added to `app/Helpers/Helpers.php`; the function **never existed in the repo** (single commit `67e9744`). Result: 7 tests failed with `Call to undefined function formatCurrency`.

**Root Cause**:  
Documented work was never committed — `.ai` overstates repo state (see BUG-028).

**Fix (2026-08-16)**:  
Added `formatCurrency($amount, $currency='₹', $decimals=2)` (function_exists guard) → `number_format($amount, $decimals, '.', ',')` prefixed by currency.

**Verification**: `php artisan test` → **27 passed (33 assertions)**.

---

### BUG-026: Minimal Caching + Sync Queues

**Status**: ⚠️ MONITORING  
**Priority**: 🟡 LOW  
**Module**: Performance  

**Description**:  
Only 2 cache usages in the app; `QUEUE_CONNECTION=sync`, `CACHE_DRIVER=file`, `SESSION_DRIVER=file`.

**Impact**:  
Repeated expensive queries; no background jobs; file storage doesn't scale.

**Recommendation**:  
Cache master data (travel agents, revenue codes, room lists); move to database/Redis queues and Redis cache/sessions in production.

---

## Composer Audit Results (VERIFIED 2026-08-07)

`composer audit` → **29 advisories across 6 packages + 1 abandoned** (exit code 1).

| Package | Installed | Fixed In | Fixable on L10? | Advisories |
|---------|-----------|----------|-----------------|------------|
| dompdf/dompdf | 3.1.5 | **3.1.6** | ✅ Yes | 6 (4 med, 2 low): CVE-2026-59943/59942/59941/56722/55555/55554 |
| guzzlehttp/guzzle | 7.11.0 | **7.15.2** | ✅ Yes | 9 (1 HIGH CVE-2026-69246 + 8 med) |
| guzzlehttp/psr7 | 2.11.0 | **2.12.3** | ✅ Yes | 2 med: CVE-2026-59882, CVE-2026-55766 |
| league/commonmark | 2.8.2 | **2.9.0** | ✅ Yes | 6 (4 HIGH): CVE-2026-71488, CVE-2026-71478, ... |
| phpoffice/phpspreadsheet | 5.7.0 | **5.8.1+** | ✅ Yes | 3 HIGH: CVE-2026-59933/59932/59931 |
| laravel/framework | 10.50.2 | **12.60.0+/13.10.0+** (9-11.x unfixable — EOL) | ❌ No (needs L12 upgrade) | 3 (HIGH + med + CVE-2026-48019) |
| beyondcode/laravel-websockets | 1.14.1 | — (abandoned) | ⚠️ Migrate to Reverb | 0 CVEs, abandoned flag |

**Summary**: 5 of 6 vulnerable packages are fixable with `composer update` on Laravel 10. Only laravel/framework requires the full L12 upgrade (`.ai/UPGRADE_PLAN.md`).

---

## Bug Summary

| ID | Status | Priority | Module |
|----|--------|----------|--------|
| BUG-001 | Resolved | Critical | System |
| BUG-002 | Resolved | Critical | System |
| BUG-003 | Resolved | Critical | System |
| BUG-004 | Warning | Medium | WebSocket |
| BUG-005 | Resolved | Medium | Configuration |
| BUG-006 | Resolved | Medium | System |
| BUG-007 | Pending | Low | Session |
| BUG-008 | Pending | Low | Cache |
| BUG-009 | Pending | Low | Queue |
| BUG-010 | Pending | Medium | Security |
| BUG-011 | Pending | Medium | Security |
| BUG-012 | Monitoring | Low | Performance |
| BUG-013 | Monitoring | Low | Performance |
| BUG-014 | Review | Low | Code Quality |
| BUG-015 | Review | Low | Code Quality |
| BUG-016 | Pending | Medium | Testing |
| BUG-017 | Pending | Medium | DevOps |
| BUG-018 | Pending | Medium | Monitoring |
| BUG-019 | Pending | Low | Documentation |
| BUG-020 | Pending | Low | Documentation |
| BUG-021 | Verified (fixed) | HIGH | DevOps/Security |
| BUG-022 | **Fixed 2026-08-16** | HIGH | Security |
| BUG-023 | **Verified safe 2026-08-16** | Medium | Security |
| BUG-024 | Pending | Medium | Security |
| BUG-025 | Review | Medium | Architecture/Performance |
| BUG-026 | Monitoring | Low | Performance |
| BUG-027 | **Fixed 2026-08-16** | Medium | Helpers/Tests |
| BUG-028 | Open | Low | Documentation |
| BUG-029 | Open | Low | Code Quality |

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
