# Analysis HMS - Decisions Log

## Decisions Overview

This document records all important architectural and technical decisions.

---

## Decision Log

### Decision 1: PHP Version Upgrade

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Project required PHP ^8.1 but was running PHP 8.0.30.

**Decision**:  
Upgrade to PHP 8.2.33.

**Alternatives Considered**:  
- Stay on PHP 8.0 (rejected - incompatible with Laravel 10)
- Upgrade to PHP 8.1 (considered - but 8.2 has better performance)
- Upgrade to PHP 8.3 (rejected - too new, potential compatibility issues)

**Rationale**:  
PHP 8.2 is stable, well-supported, and offers performance improvements.

**Consequences**:  
- All dependencies now compatible
- Performance improvements
- Access to newer PHP features

---

### Decision 2: Laravel Framework Version

**Date**: August 7, 2026  
**Status**: ⚠️ SUPERSEDED (Laravel 10 is EOL) — see Decision 12

**Context**:  
Need to choose Laravel version.

**Decision**:  
Use Laravel 10.x.

**Alternatives Considered**:  
- Laravel 9 (rejected - end of support)
- Laravel 11 (considered - but requires PHP 8.2 minimum)
- Laravel 12 (rejected - not yet stable)

**Rationale**:  
Laravel 10 is stable, well-documented, and has long-term support.

**Consequences**:  
- Access to latest features
- Good community support
- Regular security updates

**2026 Update**: Laravel 10 security fixes ended early 2025. Decision superseded — upgrade to Laravel 12 planned.

---

### Decision 3: Database Choice

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Need to choose database system.

**Decision**:  
Use MySQL.

**Alternatives Considered**:  
- PostgreSQL (considered - but team expertise in MySQL)
- SQLite (rejected - not suitable for production)
- MSSQL (considered - but licensing costs)

**Rationale**:  
MySQL is widely supported, free, and team has expertise.

**Consequences**:  
- Easy deployment on XAMPP
- Good performance for hotel operations
- Wide hosting support

---

### Decision 4: Authentication System

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Need secure authentication.

**Decision**:  
Use Laravel Sanctum for API + Session for web.

**Alternatives Considered**:  
- Passport (rejected - overkill for this use case)
- JWT (considered - but Sanctum is simpler)
- Custom authentication (rejected - security risk)

**Rationale**:  
Sanctum is lightweight, secure, and integrated with Laravel.

**Consequences**:  
- Simple API authentication
- Secure token management
- Easy to maintain

---

### Decision 5: Frontend Architecture

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Need to choose frontend approach.

**Decision**:  
Use Blade templates with jQuery.

**Alternatives Considered**:  
- Vue.js (considered - but adds complexity)
- React (considered - but adds complexity)
- Livewire (considered - but learning curve)

**Rationale**:  
Blade is simple, fast, and team is familiar with jQuery.

**Consequences**:  
- Fast page loads
- Simple to maintain
- Good performance

---

### Decision 6: Caching Strategy

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Need to optimize performance.

**Decision**:  
Use file-based caching for development, Redis for production.

**Alternatives Considered**:  
- Database caching (rejected - slow)
- Memcached (considered - but Redis is more feature-rich)
- No caching (rejected - poor performance)

**Rationale**:  
File caching is simple for dev, Redis is fast for production.

**Consequences**:  
- Fast development experience
- Scalable production caching
- Easy to switch between drivers

---

### Decision 7: Queue System

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Need background job processing.

**Decision**:  
Use sync queue for development, database/Redis for production.

**Alternatives Considered**:  
- RabbitMQ (rejected - overkill)
- Amazon SQS (rejected - cloud dependency)
- Beanstalkd (considered - but Redis is more common)

**Rationale**:  
Sync is simple for dev, database/Redis is reliable for production.

**Consequences**:  
- Simple development experience
- Scalable production processing
- Easy to switch between drivers

---

### Decision 8: WebSocket Implementation

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Need real-time features.

**Decision**:  
Use beyondcode/laravel-websockets.

**Alternatives Considered**:  
- Pusher (rejected - costs)
- Soketi (considered - but more complex)
- Native WebSocket (rejected - too much work)

**Rationale**:  
Laravel WebSockets is simple, free, and integrated with Laravel.

**Consequences**:  
- Real-time updates for POS
- Real-time printing
- Free to use

---

### Decision 9: PDF Generation

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Need to generate PDFs for bills, reports.

**Decision**:  
Use barryvdh/laravel-dompdf.

**Alternatives Considered**:  
- TCPDF (considered - but DomPDF is simpler)
- wkhtmltopdf (rejected - external dependency)
- Snappy (considered - but requires wkhtmltopdf)

**Rationale**:  
DomPDF is pure PHP, easy to use, and well-maintained.

**Consequences**:  
- Easy PDF generation
- No external dependencies
- Good performance

---

### Decision 10: Excel Export

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Need to export data to Excel.

**Decision**:  
Use phpoffice/phpspreadsheet.

**Alternatives Considered**:  
- Laravel Excel (considered - but PhpSpreadsheet is more flexible)
- CsvExport (rejected - limited features)
- Custom export (rejected - too much work)

**Rationale**:  
PhpSpreadsheet is powerful, flexible, and well-maintained.

**Consequences**:  
- Rich Excel features
- Good performance
- Easy to use

---

### Decision 11: Currency Formatting Helper

**Date**: August 7, 2026  
**Status**: Implemented

**Context**:  
Need consistent currency formatting across the application.

**Decision**:  
Create `formatCurrency()` helper function.

**Alternatives Considered**:  
- Use PHP's number_format directly (rejected - not reusable)
- Create a service class (rejected - overkill for simple formatting)
- Use a blade directive (rejected - limited to views)

**Rationale**:  
Helper function is simple, reusable, and consistent with existing patterns.

**Consequences**:  
- Consistent currency formatting
- Easy to use in any context
- Can be extended for different currencies

---

### Decision 12: Upgrade Laravel 10 → 12 (2026 research-driven)

**Date**: August 7, 2026  
**Status**: 📋 PLANNED

**Context**:  
Web research (`.ai/RESEARCH.md`) confirmed Laravel 10 security support ended early 2025; Laravel 12 is the current stable target with support to ~Feb 2027.

**Decision**:  
Plan phased upgrade `Laravel 10.50.2 → 11 → 12`, together with `yajra/laravel-datatables-oracle` upgrade to v12.6.0+/13 (fixes AIKIDO-2025-10705 RCE affecting current ^10.11).

**Execution plan**: `.ai/UPGRADE_PLAN.md` (Phases 0–4, ~4–6 dev-days)

**Alternatives Considered**:  
- Stay on Laravel 10 (rejected - EOL, no security patches)
- Jump straight to Laravel 13 (rejected - research shows 12 as stable target for this stack)

**Rationale**:  
Security patches + Symfony 7 routing + continued package ecosystem support.

**Consequences**:  
- Requires PHP 8.2+ (already satisfied)
- Deprecation review needed (upgrade guides at laravel.com/docs/12.x/upgrade)
- Websocket package must be migrated (Decision 13)

---

### Decision 13: Replace beyondcode/laravel-websockets

**Date**: August 7, 2026  
**Status**: 📋 PLANNED

**Context**:  
`beyondcode/laravel-websockets` is abandoned on Packagist, incompatible with Laravel 11/12.

**Decision**:  
Migrate to **Laravel Reverb** (official first-party WebSocket server) or **Soketi** during the Laravel 12 upgrade.

**Alternatives Considered**:  
- Pusher cloud (rejected - ongoing cost)
- Soketi (considered - valid fallback, Node.js based)

**Rationale**:  
Reverb is the modern Laravel-native standard, free, and horizontally scalable.

**Consequences**:  
- Update `broadcasting.php`, Echo client config, and ws-server startup
- Verify POS/print realtime events (SaleBillPrintEvent, PrintEvent) after migration

---

### Decision 14: PHP 8.2 → 8.4 Upgrade Window

**Date**: August 7, 2026  
**Status**: 📋 PLANNED (before Dec 31, 2026)

**Context**:  
PHP 8.2 enters EOL on Dec 31, 2026 (security-only since Jan 2025).

**Decision**:  
After the Laravel 12 upgrade, move PHP to **8.3 or 8.4** (8.4 active until Dec 2028).

**Alternatives Considered**:  
- Stay on 8.2.33 (rejected - EOL in 5 months)
- PHP 8.5 (considered - too new for production stability at this time)

**Rationale**:  
Continuous security coverage; 8.4 is the recommended production pairing for Laravel 12.

**Consequences**:  
- Re-verify extensions (gd, intl, bcmath, etc.) on the new PHP build
- Run full test suite after switch

---

## Decision Summary

| ID | Decision | Date | Status |
|----|----------|------|--------|
| 1 | PHP Version Upgrade | Aug 7, 2026 | Implemented (8.2.33) |
| 2 | Laravel Version | Aug 7, 2026 | ⚠️ Superseded (EOL) |
| 3 | Database Choice | Aug 7, 2026 | Implemented |
| 4 | Authentication System | Aug 7, 2026 | Implemented |
| 5 | Frontend Architecture | Aug 7, 2026 | Implemented |
| 6 | Caching Strategy | Aug 7, 2026 | Implemented |
| 7 | Queue System | Aug 7, 2026 | Implemented |
| 8 | WebSocket Implementation | Aug 7, 2026 | Implemented (to be replaced) |
| 9 | PDF Generation | Aug 7, 2026 | Implemented |
| 10 | Excel Export | Aug 7, 2026 | Implemented |
| 11 | Currency Formatting Helper | Aug 7, 2026 | Implemented |
| 12 | Upgrade Laravel 10 → 12 | Aug 7, 2026 | 📋 Planned |
| 13 | Replace websockets pkg | Aug 7, 2026 | 📋 Planned |
| 14 | PHP 8.2 → 8.4 | Aug 7, 2026 | 📋 Planned |

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
