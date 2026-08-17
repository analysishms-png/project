# Analysis HMS — Standards Compliance Audit

Audit date: 2026-08-17
Reference: `.ai/DEVELOPMENT_STANDARDS.md` (official HMS development standards)

This document records the compliance audit of the existing codebase against the official
standards, what was fixed, and what is intentionally documented as a legacy deviation
(not refactored) to avoid risk to working financial logic.

---

## Audit summary

| Rule | Finding | Status |
|---|---|---|
| 2.x Database (collation, naming, `id` first, `TINYINT(1)` booleans) | Existing schema predates the standard; no new tables added in this pass | ⚠️ Legacy — documented, not migrated |
| 3.1 Eloquent-first for Insert/Update/Delete | 192+ `DB::table(...)->insert()` call sites; whole app built on Query Builder for writes | ⚠️ Legacy convention — see below |
| 3.2 Reports may use Query Builder | ✅ Used correctly for reports | ✅ |
| 4.2 Constructor with `$this->propertyid` | 34/119 controllers lack a constructor, but only 2 use `Auth::user()->propertyid`; the other 32 are API/Auth/Frontend/Cron public endpoints where the auth-guard constructor would break routing | ⚠️ Documented — see below |
| 9.1/9.2/9.5 Laravel Excel + DomPDF exports | ✅ Export classes + DomPDF print views used for new reports | ✅ |
| 10.6 Log writes via service classes | ❌ 8 inline `DB::table('paychargelog')->insert()` in 3 controllers | ✅ **FIXED** (PayChargeLogService) |

---

## FIXED — Rule 10.6: paychargelog audit writes centralized

**Files changed:**
- `app/Services/PayChargeLogService.php` (**new**) — `store(array $row)` + `storeMany(array $rows)`
  wrapper around `DB::table('paychargelog')->insert()`. Pure insert passthrough; column
  payloads unchanged; mirrors the existing `LedgerLogService` precedent.
- `app/Http/Controllers/Banquet.php` — 5 inline inserts → `PayChargeLogService::store([...])`
  / `storeMany($log)` (+ `use` import)
- `app/Http/Controllers/CompanyController.php` — 2 inline inserts → `PayChargeLogService::store(...)`
  (+ `use` import)
- `app/Http/Controllers/Reporting.php` — 1 inline insert → `PayChargeLogService::store([...])`
  (+ `use` import)

**Why safe:** the service is a thin wrapper — same table, same data, same transaction
scope, zero behavior change. All 8 sites sit inside existing `DB::beginTransaction()`
blocks, and the `if (!empty($log))` batch guard was preserved.

**Verification:** `php -l` clean on all 4 files; full suite **68 passed (165 assertions)**.

---

## Documented legacy deviations (NOT refactored — risk assessment)

### Rule 3.1 — Eloquent-first writes (192+ Query Builder inserts)

The entire application was built on `DB::table(...)->insert()` for financial writes
(paycharge, suntran, guestfolio, reservations, roomocc, stock, kot, …). Converting all
192 sites to Eloquent models would be a full rewrite of working financial logic with no
evidence of a defect — explicitly excluded by the project master prompt
("DO NOT randomly refactor working code", "DO NOT replace existing business logic without
evidence").

**Recommendation:** apply Eloquent-first to **new** code going forward (already the rule);
schedule a separate, test-covered migration of the financial write paths if desired.

### Rule 4.2 — Controller constructors

34/119 controllers have no `__construct()`. Of these:
- **2** use `Auth::user()->propertyid` inline (`GeneralController`, `Pos\OrderController`) —
  but both serve the **public QR-ordering flow** (`placeorder`, `outlet/{propertyid}/{outlet_code}/{comp_name}`),
  which is deliberately outside the auth-protected route group. Adding the house
  auth-guard constructor would redirect guest QR orders to login and **break functionality**.
- **32** are API/Auth/Frontend/Cron/Demo endpoints where propertyid is not used or auth
  is handled differently (token/API auth, public marketing pages).

**Recommendation:** leave as-is; constructors already exist on all authenticated
property-panel controllers that use `$this->propertyid`.

### Rule 2.x — Database schema

Existing tables predate the standard (legacy singular/plural mix, some uppercase columns,
`Y/N` flags). No schema changes made — the master prompt forbids changing database
structure without a verified bug. Applies to new tables only.

---

## Standing recommendations for new code

- New tables: `utf8mb4_0900_ai_ci`, plural names, `id` first, `u_name` + `created_at`/`updated_at`, `TINYINT(1)` booleans
- New writes: Eloquent models; reports may use Query Builder
- New controllers (auth-protected): house constructor with `$this->propertyid`
- All log-table writes: dedicated service classes (this audit fixed the paychargelog family; `kotlog`, `activity_logs`, `support_ticket_queue`, `menuhelp`/`userpermission` remain inline — same treatment recommended)
- Exports: Laravel Excel classes for large datasets, DomPDF for PDF/print
- UI: Bootstrap utilities first, minimal custom CSS (already the direction of the UI redesign passes)
