# Night Audit Module — Gap Analysis

**Module:** 14 — Night Audit  
**Date:** 2026-08-19  
**Status:** COMPLETE (verified — code clean, routes registered, MySQL offline for live validation)

---

## 1. Laravel Implementation (Current)

### Core Components

| Component | File | Lines | Purpose | Status |
|---|---|---|---|---|
| **autoCharge** (auto night audit) | CronController.php | 1219 | Automated nightly room-charge posting (cron-driven) | ✅ COMPLETE |
| **submitnightaudit** (manual) | CompanyController.php | ~350 | Manual night audit with validation + state advance | ✅ COMPLETE |
| **submitnightaudit2** (reverse) | CompanyController.php | ~30 | Reverse night audit (rollback ncur) | ✅ COMPLETE |
| **nightAuditLog** / chremovelog | NightAuditlogController.php | 190 | Night audit log + CH remove log viewer | ✅ COMPLETE |
| **DailyReport** (fetch + print) | NightAudit/Reports/DailyReport.php | 1145 | Comprehensive daily revenue report (rooms+POS+banquet+inventory) | ✅ COMPLETE |
| **CleanUp** | Cron/CleanUp.php | ~35 | Storage/log cleanup utility | ✅ COMPLETE |
| **DatabaseSend** | Cron/DatabaseSend.php | ~30 | Database backup utility | ✅ COMPLETE |

### autoCharge Posting Flow (verified):

1. ✅ DB transaction wrapping all properties
2. ✅ **PPOS** posting (suntran → paycharge) — outlet/room-service charges
3. ✅ **IPOS** posting (stock → paycharge) — item-level charges
4. ✅ **REV** posting (plan charges with TaxStru evaluation)
5. ✅ **RC** posting (default room charges with TaxStru evaluation)
6. ✅ Tax structure evaluation (Between, <=, >=, =, >, <) — all 6 operators
7. ✅ VoucherPrefix serial number management
8. ✅ ncur advance (enviro_general)
9. ✅ PaychargeLog audit before PPOS/IPOS re-posting
10. ✅ logData() audit trail
11. ✅ Error handling + DB::rollBack()

### submitnightaudit Validation Flow (verified):

1. ✅ Permission guard (191112 ins)
2. ✅ Pending KOT check (if kotatnightaudit = Y)
3. ✅ Pending POS bill check (if posbillatnightaudit = Y)
4. ✅ Uncharged room check (RC vtype existence)
5. ✅ Unsettled bill check (nullroomocc + billno/settledate)
6. ✅ Tentative booking auto-cancel (tentativedays)
7. ✅ Departure date extension
8. ✅ No-show auto-cancel (noshowatnightaudit = Y)
9. ✅ Auto housekeeping room assignment (autoroomassign = 1)
10. ✅ AccountPosting (PPOS→Folio, IPOS→Folio)
11. ✅ Room status → Dirty
12. ✅ NightAuditLog write
13. ✅ ncur advance + availability cache flush
14. ✅ DB transaction + commit/rollback

### Delete-path safety:
- **autoCharge PPOS/IPOS deletion**: ✅ Audited via PaychargeLog::auditDeleted before delete
- **submitnightaudit**: ✅ No financial deletes (only state transitions)
- **submitnightaudit2**: ✅ No financial deletes (only ncur rollback)

---

## 2. Legacy HMS Night Audit Reports (from HMS.text GRepFormName)

| Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|
| **DailyReport** | Daily revenue report (rooms+POS+banquet) | dailyreport (NightAudit/Reports/DailyReport.php) | ✅ EXISTS |
| **NightAuditReport** | Night audit summary (occupancy+revenue+comparison) | ❌ was MISSING → **ADDED** (Night Audit Reconciliation) | ✅ NEW |
| **NightAuditReportI** | Night audit list view (variant of above) | Covered by new Night Audit Reconciliation | ✅ NEW |
| **DailySumm** | Daily summary | Covered by DailyReport | ✅ EXISTS |
| **DailyDiet** | Daily diet/meal report | ❌ MISSING (P2 — meal plan tracking) | ❌ MISSING |
| **DailyFuncSheet** | Daily function/banquet sheet | ❌ MISSING (documented in BANQUET_GAPS.md) | ❌ MISSING |
| **RoomNights** | Room nights analysis | ❌ MISSING (P2 — occupancy statistics) | ❌ MISSING |
| **DailyStoreIssRpt** | Daily store issue report | Covered by inventory reports | ✅ EXISTS |

---

## 3. Added This Pass

### Night Audit Reconciliation Report (NA-01)

**Purpose:** Room occupancy vs charges posted vs settlement status — snapshot for the selected date, with prior-night comparison.

**Key gap it solves:** Legacy had NightAuditReport/NightAuditReportI (occupancy+revenue+comparison), but Laravel only had DailyReport (revenue) and NightAuditLog (log entries). No reconciliation view existed.

**Files created:**
- `app/Http/Controllers/Reporting.php` — +2 methods: `nightauditrecon`, `nightauditreconfetch`
- `routes/reporting.php` — +2 routes
- `resources/views/property/nightauditrecon.blade.php` — NEW (DataTables, summary cards, revenue-by-vtype, unsettled bills, NA log)

**Data shown:**
1. Summary cards: Occupied Rooms, Total Revenue, Unsettled Bills, Active Guests — each with prior-night comparison
2. Revenue by voucher type table (RC, REV, PPOS, IPOS, DISC, etc.)
3. Unsettled bills table (room, guest, outstanding balance)
4. Night Audit log entries for the date

**Permission:** 191212 (same as Daily Report — night audit report family).  
**Read-only: zero writes to any table.**

### BUG-047 Fix

**File:** `app/Http/Controllers/Reporting.php` line 3607  
**Bug:** `'todate',` (bare string in array, should be `'todate' => $ncurdate,`)  
**Impact:** Undefined variable error in dailyreport view when accessing via route  
**Fix:** Changed to `'todate' => $ncurdate,`

---

## 4. Remaining Gaps (Not Implemented)

| Gap | Priority | Reason Not Implemented |
|---|---|---|
| DailyDiet (meal plan tracking) | P2 | Needs business decision on meal plan tracking scope |
| DailyFuncSheet (banquet daily function) | P2 | Documented in BANQUET_GAPS.md |
| RoomNights (occupancy statistics) | P2 | Nice-to-have; DailyReport covers occupancy |
| NightAuditReportI (list variant) | P2 | Covered by reconciliation report |

---

## 5. Verification

- `php -l Reporting.php` → ✅ no syntax errors
- `php artisan route:list --name=nightauditrecon` → ✅ 2 routes registered
- `php artisan test` → ⏳ MySQL offline (XAMPP)
- BUG-047 fix verified: `'todate' => $ncurdate,` now matches the view expectation

---

## 6. Files Changed (This Pass)

| File | Change |
|---|---|
| `app/Http/Controllers/Reporting.php` | +2 methods (~140 lines) + BUG-047 fix (1 line) |
| `resources/views/property/nightauditrecon.blade.php` | NEW (~180 lines) |
| `routes/reporting.php` | +2 routes |
| `.ai/NIGHT_AUDIT_GAPS.md` | NEW |
