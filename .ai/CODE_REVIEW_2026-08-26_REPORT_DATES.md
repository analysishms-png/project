# Code Review: Date-Filter & Financial Year Logic
## 26 August 2026

---

## 1. Financial Year Architecture

### How FY is configured

| Source | Table | FY Field | Current Value (prop 103) |
|--------|-------|----------|--------------------------|
| **Report engine** | `DateHelper::calculateDateRanges()` | Hardcoded April 1 → March 31 | Computed dynamically from `ncur` |
| **Hotel record** | `company.start_dt` / `end_dt` | Manual entry | 2024-04-01 / 2025-03-31 (**stale**) |
| **Software date** | `enviro_general.ncur` | Current business date | 2026-08-05 |

**Key finding:** `DateHelper::calculateDateRanges()` (app/Helpers/DateHelper.php:32-36) hardcodes
the Indian hotel FY (April 1 → March 31) using Carbon, computed from `ncur`. It does NOT
use `company.start_dt/end_dt` at all. Reports using DateHelper are correct.

### Where `company.start_dt/end_dt` is used

- **commented-out code** in Reporting.php (line 1328) — FY enforcement was disabled
- **CompanyInfo API** (line 32-33) — passes to external consumers (informational only)
- **MainController** (lines 358-359, 501-502) — stores values when user edits company profile
- **Hidden fields in blades** (arrivallist, pos_saleregister, etc.) — passed as POST params but never consumed by controllers (dead code)

**Verdict:** `company.start_dt/end_dt` are informational/display-only. No queries use them.

---

## 2. Date-Filter Patterns (All Reports)

### Pattern A: Software-date-based (most reports)

All finance, front-office, and operational reports use `$this->ncurdate` set from
`enviro_general.ncur`. The date filter flows:
1. Controller constructor: `$this->ncurdate = enviro_general.ncur`
2. Blade default date: `value="{{ $fromdate }}"` (typically `ncurdate`)
3. AJAX fetch: `$('#fromdate').val()` → POST → controller

This is correct and consistent.

### Pattern B: DateHelper ranges (financial reports)

Finance reports use `DateHelper::calculateDateRanges($this->ncurdate)` which returns:
- `mtd` (month-to-date)
- `ftd` (financial year-to-date)
- `ytd` (calendar year-to-date)

All computed from ncurdate. Correct and consistent.

---

## 3. Specific Findings

### 3.1 CronController FY-end hardcode (BY DESIGN, NOT A BUG)

**File:** CronController.php:127
**Code:** `if (date('d-m', ncur) == '31-03')`

Skips night audit on March 31 → forces manual FY rollover via FinancialPush.
This is a deliberate business flow: the operator must run FinancialPush to create
next-year voucher prefixes before night audit can proceed.

### 3.2 Reporting Occupancy Reports — depdate filter (BY DESIGN)

**Files:** Reporting.php:12343, 12368

Per-room-type occupancy queries use:
```sql
LEFT JOIN roomocc ro ON ... AND ro.type NOT IN ('C','O')
  AND ro.chkindate <= $td AND (ro.depdate IS NULL OR ro.depdate >= $fd)
```

Uses expected departure (`depdate`) as the reporting cutoff. Hotels treat past
expected departures as "checked out" for reporting purposes (even if physically
present). This is intentional for historical per-period reports and differs from
the dashboard's current-state view (which correctly uses `chkoutdate` + `type`).

### 3.3 Date Boundary (NOT A BUG)

All `whereBetween` date filters are safe: `vdate` columns in `paycharge`, `sale1`,
`hallsale1`, `kot`, `guestfolio` are all DATE type (not DATETIME), so boundary
comparisons include the full day.

### 3.4 Cross-Property Data Bleed (NOT FOUND)

All sampled queries in Reporting.php include `->where('propertyid', $this->propertyid)`
within the first 3-5 lines of the chain. No cross-property bleed found.

### 3.5 Dashboard — Already Fixed (26 Aug 2026)

- ADR/RevPAR now use room revenue only (paycharge.amtdr), matching RealtimeController
- Occupancy trend uses chkoutdate + type, not depdate (current-state display context)

---

## 4. Recommendation

No code changes required. The date-filter logic is sound:

- FY computation is consistent (all via DateHelper with hardcoded April 1)
- Software date (ncur) is the single source of truth for "today"
- company.start_dt/end_dt stale data does not affect any query
- Occupancy report depdate usage is by-design for historical reporting

If non-April-March FY support is ever needed, modify DateHelper::calculateDateRanges()
to accept a configurable FY start month.
