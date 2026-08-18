# Reports Module — Bug Findings (READ-ONLY audit)

Audit date: 2026-08-17
Scope: `app/Http/Controllers/Reporting.php` (6,398 lines), `app/Http/Controllers/Finance/FinanceController.php` (2,065 lines), `app/Http/Controllers/NightAudit/Reports/DailyReport.php`, report blades.
**NO code changes made** — this is a findings-only report per user instruction.

---

## 🔴 RPT-01 (P2) — Bulk-charge report default date range inverted + dead variable

**File**: `app/Http/Controllers/Reporting.php:207-211` + `resources/views/property/report_bulkcharge.blade.php:99`

```php
$fromdate = $this->ncurdate;                                   // today
$todate = date('Y-m-d', strtotime('-1 month', strtotime($this->ncurdate)));  // one month AGO
```

- `$todate` is computed (one month ago) but **never passed to the view** (view receives `fromdate`, `statename`, `company`, ... — no `todate`).
- The "To Date" input at blade line 99 uses `value="{{ $fromdate }}"` → **defaults to today**, so the default range is today→today (empty report) instead of the intended last-30-days.
- The assignment itself is inverted (fromdate = today, todate = month-ago). If passed through, `whereBetween` would silently return nothing.

**Impact**: default bulk-charge report renders empty; user must manually set both dates. Data is correct once dates are set.

---

## 🟠 RPT-02 (P1) — `billreprintsubmit` writes financial data with NO permission guard

**File**: `app/Http/Controllers/Reporting.php:1080` → route `routes/reporting.php:23` `POST billreprintsubmit`

- The page itself (`CompanyController::openbillreprint`, line 15706) is guarded with `revokeopen(141115)`.
- But `billreprintsubmit` (which **updates `paycharge.amtdr/onamt/billamount`** — financial records!) has only the constructor login check. **No `revokeopen` guard.**
- Any authenticated user (any menu permission) can POST to `/billreprintsubmit` and alter folio charge amounts.
- Same bug class as the previously-fixed BUG-040 (write path with zero permission guard).

**Impact**: unauthorized financial write — any logged-in user can change bill amounts.

---

## 🟠 RPT-03 (P2) — `updatemenuitems` / `updateitemrates` unguarded + always-insert duplicates

**File**: `app/Http/Controllers/Reporting.php:5982` (updatemenuitems), `:6025` (updateitemrates)
**Routes**: `routes/reporting.php:163,165` `POST updatemenuitems` / `POST updateitemrates`

- **No `revokeopen` guard** — only constructor login check.
- `updateitemrates`: the "update existing rate" branch is **commented out** — the endpoint **always `INSERT`s a new `itemrate` row**, so every save call creates a duplicate rate row for the same (RestCode, ItemCode, AppDate). No existence check.
- `$item['itemcode']` / `$item['rate']` / `$item['app_date']` are used without validation — a missing key throws (caught → 500), and arbitrary values are written (no item-master membership check).
- `updatemenuitems` writes `itemmast` prices from a raw `items[]` array — no per-item validation or price bounds.

**Impact**: duplicate itemrate rows accumulate (data bloat), prices changeable by any authenticated user, malformed payloads 500.

---

## 🟠 RPT-04 (P2) — Finance report fetch/query/print/export endpoints unguarded

**File**: `app/Http/Controllers/Finance/FinanceController.php` — route-bound methods without `revokeopen`:

- `trialmainquery`, `monthwisetrialfetch`, `monthrowfetch`, `trialgroupmainquery`, `fetchsubgroupdetails`, `fetchsubgroupdetails2`
- `profitlossmainquery`, `profitlosssecondqueryhf`, `balancesheetmainquery`
- `tdsreportdata`, `detailedTrialLedgerQuery`, `printDetailedTrialLedger`, `exportDetailedTrialLedger`
- `generalLedgerAccounts`, `generalLedgerQuery`, `printGeneralLedger`, `exportGeneralLedger`
- `dayBookVtypes`, `dayBookQuery`, `printDayBook`, `exportDayBook`
- `journalBookVtypes`, `journalBookQuery`, `printJournalBook`, `exportJournalBook`
- `cashBankBookAccounts`, `cashBankBookQuery`, `printCashBankBook`, `exportCashBankBook`

The **page entry points ARE guarded** (`revokeopen(111211)` on trailbalance/generalledger/daybook/detailedtrialledger/journalbook/cashbankbook). But the AJAX fetch + print + export endpoints they call are **directly routable and unguarded** — any authenticated user can hit `/generalledger/export` or `/daybook/fetch` directly without the 111211 (Trail Balance) permission.

**Impact**: read-only financial data exposure to any authenticated user; report-menu permission bypass. Not a write, so severity P2.

---

## 🟠 RPT-05 (P2) — `tdsreport` page has its permission check commented out

**File**: `app/Http/Controllers/Finance/FinanceController.php` (tdsreport method)

```php
// $permission = revokeopen(111214);
return view('property.finance.tdsreport', [...]);
```

- The page itself is **not permission-guarded** (comment left in place). Any authenticated user can open the TDS report page.

---

## 🟡 RPT-06 (P3) — No fromdate ≤ todate validation anywhere

- `validateFinancialYear()` (custom.min.js:2364) validates only the financial-year window, **not** the from/to order.
- All `whereBetween([$fromdate, $todate])` queries (fetchpaydata:602/637, fetchcheckinregdata:1361, fetchcancelbilldata, etc.) silently return **empty results** when from > to.
- The bulk-charge default (RPT-01) makes this easy to hit accidentally.

**Impact**: confusing empty reports; no error message.

---

## 🟡 RPT-07 (P3) — `revokeopen` double call

**File**: `app/Http/Controllers/Reporting.php:199-203` — `revokeopen(141212)` called twice in sequence (the `$permission` result from the first check is not used; the second call re-queries). Wasteful duplicate DB query; first check is dead code.

---

## 🟡 RPT-08 (P3) — `{!! $page->content !!}` raw CMS output

**File**: `resources/views/frontend/page.blade.php:13`

- Raw HTML output of CMS page content. If the CMS editor allows raw HTML this is intentional; if the content can be set by non-trusted users, it's stored XSS. (The ticket views were previously fixed for this — BUG-022 — but the CMS page output was not reviewed in that fix.)

---

## ✅ Verified OK (not bugs)

- Report queries property-scoped (`paycharge.propertyid = $this->propertyid` on fetchpaydata mainQuery + cgstQuery).
- Division-by-zero sites guarded (`> 0` checks at 3254, 3279, 6277-6308).
- `DailyReport.php` — all methods guarded or constructor-level.
- `fetchcancelbilldata` / `fetchbussource` / `fetchcompname` — read-only, property-scoped.
- `guestsign` `{!! !!}` — stored upload filename, not free text (low risk).
- `whereNull('roomocc.type')` + groupBy + `->get()->count()` — counts groups correctly (collection count).
- Guest-trail / arrival-list / occupancy-forecast / reward-point fetches — property-scoped.

---

## Severity summary

| ID | Severity | Type | File |
|----|----------|------|------|
| RPT-01 | P2 | Wrong default date range (empty report) | Reporting.php:207-211 + view:99 |
| RPT-02 | **P1** | Financial write without permission guard | Reporting.php:1080 |
| RPT-03 | P2 | Unguarded writes + duplicate itemrate inserts | Reporting.php:5982,6025 |
| RPT-04 | P2 | Finance report fetch/export endpoints unguarded | FinanceController (21 methods) |
| RPT-05 | P2 | tdsreport permission commented out | FinanceController |
| RPT-06 | P3 | No from≤to date validation | validateFinancialYear + all reports |
| RPT-07 | P3 | Duplicate revokeopen call | Reporting.php:199 |
| RPT-08 | P3 | Raw CMS content output (stored-XSS review) | frontend/page.blade.php:13 |

**Recommended fix order**: RPT-02 (P1 financial) → RPT-03 → RPT-04/RPT-05 → RPT-01 → RPT-06/07/08.
