# Analysis HMS — BUG REGISTER (CANONICAL)

> Single source of truth for bugs. Supersedes `.ai/known_bugs.md` (kept for history).
> Statuses: OPEN / INVESTIGATING / FIXED / TESTING / VERIFIED / WONT_FIX / DUPLICATE.
> **Never mark FIXED without verification.** Verified 2026-08-16.

---

## Bug Summary Table

| ID | Title | Severity | Status | Module |
|----|-------|----------|--------|--------|
| BUG-001 | PHP version incompatibility | Critical | VERIFIED (fixed) | System |
| BUG-002 | Missing PHP extensions | Critical | VERIFIED (fixed) | System |
| BUG-003 | Composer deps not installed | Critical | VERIFIED (fixed) | System |
| BUG-004 | Abandoned websockets pkg | Medium | VERIFIED (fixed → Reverb) | WebSocket |
| BUG-005 | Missing .env | Medium | VERIFIED (fixed) | Config |
| BUG-006 | Missing bootstrap/cache | Medium | VERIFIED (fixed) | System |
| BUG-007 | Session driver file | Low | OPEN (prod hardening) | Session |
| BUG-008 | Cache driver file | Low | OPEN (prod hardening) | Cache |
| BUG-009 | Queue driver sync | Low | OPEN (prod hardening) | Queue |
| BUG-010 | APP_DEBUG=true | Medium | OPEN (deploy-time) | Security |
| BUG-011 | Default DB credentials | Medium | OPEN (deploy-time) | Security |
| BUG-012 | N+1 query potential | Low | MONITORING | Performance |
| BUG-013 | Large exports timeout | Low | MONITORING | Performance |
| BUG-014 | Duplicate helper logic | Low | FIXED 2026-08-21 | Code quality |
| BUG-015 | Inconsistent naming | Low | OPEN | Code quality |
| BUG-016 | Limited test coverage | Medium | OPEN (partially fixed) | Testing |
| BUG-017 | No CI/CD | Medium | OPEN | DevOps |
| BUG-018 | No error monitoring | Medium | OPEN | Monitoring |
| BUG-019 | No API docs | Low | OPEN | Documentation |
| BUG-020 | No deployment guide | Low | OPEN | Documentation |
| BUG-021 | No git repo | HIGH | VERIFIED (fixed — 1 baseline commit) | DevOps |
| BUG-022 | **Stored XSS in ticket views** | **HIGH** | **FIXED 2026-08-16** | Security |
| BUG-023 | Dynamic SQL interpolation (Tools) | Medium | VERIFIED SAFE 2026-08-16 | Security |
| BUG-024 | Debug mode enabled (dup of BUG-010) | Medium | DUPLICATE (of BUG-010) | Security |
| BUG-025 | God controllers / zero eager loading | Medium | OPEN | Architecture/Perf |
| BUG-026 | Minimal caching + sync queues | Low | OPEN | Performance |
| BUG-027 | **formatCurrency helper missing (docs ≠ code)** | **Medium** | **FIXED 2026-08-16** | Helpers/Tests |
| BUG-028 | `.ai` docs overstate repo state (uncommitted work) | Low | FIXED 2026-08-21 (docs reconciled) | Documentation |
| BUG-029 | `e = statename();.blade.php` junk file in views | Low | FIXED 2026-08-21 | Code quality |
| BUG-030 | **Silent advance deletion (no audit)** — `deleteadvancedeposit` + `deleteadvancebanquet` hard-deleted financial rows with no paychargelog entry, no reason, no reconciliation check (legacy blocked deletion when related records existed) | **HIGH** | **FIXED 2026-08-16** | Financial safety |
| BUG-031 | **Historical paychargelog rows have amtcr=NULL** — old `deleteguestledger` copied only amtdr, so deleted advance *amounts* are unrecoverable from the log (trail exists: user/time/reason) | Medium | OPEN (data limitation — document, don't rewrite history) | Financial audit |
| BUG-032 | **Live DB is `analysis`, not `db_analysishms`** — .ai docs referenced a DB that doesn't exist; .env points to `analysis` (live, 215 tables, 598K paycharge rows) | Medium | OPEN (docs reconciled in this register; verify backup target) | Infrastructure |
| BUG-037 | **Unlogged paycharge deletions in POS/re-posting flows** — `possalebillsettle`, `possalebillsettleupdate`, `salebillsettlesubmit`, `chargesposting`, night-audit cron, `AccountPosting`, 2 ROFF deletes deleted paycharge rows with NO paychargelog audit; `deletebillxhr` logged but omitted amtcr | **HIGH** | **FIXED 2026-08-16** | Financial safety / POS |
| BUG-043 | **Tools bulk-delete paths unlogged / dead-code audit** — `deletedate` (Data Empty Tool) audit was unreachable dead code → full property wipe left ZERO audit trail; `deletetablerecord`/`deletemultiplerecords` (Table Management) + `resetOutletData` (POS Recycle) deleted paycharge/ledger/suntran rows with no financial audit | **HIGH** | **FIXED 2026-08-16** | Financial safety / Tools |
| BUG-044 | **acgroup join multiplies ledger report rows** — `leftJoin('acgroup as a', 's.group_code', '=', 'a.group_code')` without property scoping: `acgroup.group_code` is NOT globally unique (shared across properties, verified: 30158→prop 157/158, 31104→103/104, …), so General Ledger / Detailed Trial Ledger / new Day Book reports inflated row counts + totals (~5.1% on prop 169: 2,822→2,967 rows; ₹20.85M→₹21.23M Dr) | **MEDIUM** | **FIXED 2026-08-16** | Finance reports / Accounts |

---

## Detail — Security (P0/P1)

### BUG-022: Stored XSS in ticket views — FIXED ✅
- **Severity**: HIGH | **Module**: Support Tickets / Security
- **Root cause**: `{!! $ticket->problem !!}` rendered user-supplied textarea content raw in 3 views.
- **Files**: `resources/views/tools/tickets.blade.php:394`, `resources/views/admin/tools/tickets.blade.php:315`, `resources/views/property/mytickets.blade.php:305`
- **Fix**: `{{ nl2br(e($ticket->problem)) }}` — escaped + preserves line breaks.
- **Verified**: 2026-08-16 — grep confirms no `$ticket->problem !!` remains; `view:cache` compiles all blades; test suite 27 passing.
- **Regression risk**: LOW (plain-text display unchanged except line breaks now rendered).

### BUG-023: Dynamic SQL interpolation in ToolsController — VERIFIED SAFE ✅
- **Severity**: Medium | **Module**: Tools / Security
- **Findings (verified 2026-08-16)**:
  - `fetchtables`: `$allowedTables` is a **hardcoded whitelist** → table names interpolated safely.
  - `fetchtabledata` / `bulkupdaterecords` / `updatetablecell` / `deletetablerecord`: table name validated by exact match against `SHOW TABLES`; column names validated against `SHOW COLUMNS`.
  - `$sqlWhere` (user-supplied) is executed via `whereRaw()` **by design** — it is a superadmin/support DB tool.
  - **Access gate**: `ToolsController::__construct` middleware requires auth AND (superadmin role==1/propertyid==10 OR propertyid==20), else redirect `/`. Regular hotel users cannot reach these endpoints.
- **Verdict**: Not exploitable by non-privileged users. Documented, no code change required. Re-verify if ToolsController constructor guard is ever removed.

### BUG-010 / BUG-024: APP_DEBUG=true / APP_ENV=local
- **Status**: OPEN — deploy-time. DO NOT flip on this dev machine (breaks developer experience). Must be set `APP_DEBUG=false` + `APP_ENV=production` before any public deployment.

---

## Detail — Helpers / Tests

### BUG-027: formatCurrency helper missing — FIXED ✅
- **Root cause**: `.ai/CHANGELOG.md` (2026-08-07) claimed `formatCurrency` was added; `app/Helpers/Helpers.php` never contained it. 7 tests failed (`Call to undefined function`).
- **Fix**: Added `formatCurrency($amount, $currency='₹', $decimals=2)` with `function_exists` guard → `number_format($amount, $decimals, '.', ',')` prefixed by currency.
- **Verified**: `php artisan test` → 27 passed (33 assertions).
- **Note**: `.ai/MEMORY.md` shows Indian grouping `₹ 12,34,567.89`; tests expect US grouping `₹ 1,234,567.89` — **tests are authoritative**; memory doc updated.

---

## Detail — Documentation / Quality

### BUG-028: `.ai` docs overstate repo state
- The 2026-08-07 `.ai` CHANGELOG/MEMORY describe work (formatCurrency, 26 passing tests, baseline commit `809669c`) that is **not in the repo** (repo has single commit `67e9744`). Prior docs are aspirational, not factual. CHANGELOG_AI.md (this session) records only **verified** work.
- **Action**: reconcile remaining claims (e.g., "DB backup pending", "composer audit results") against reality as time permits.

### BUG-029: Junk file `resources/views/e = statename();.blade.php`
- A stray blade file with a PHP expression as filename — cleanup candidate (confirm unused first).

---

## Detail — Financial Safety

### BUG-030: Silent advance deletion (no audit) — FIXED ✅
- **Severity**: HIGH | **Module**: Financial safety / Front Office
- **Root cause**: `CompanyController::deleteadvancedeposit` and `Banquet::deleteadvancebanquet` hard-deleted `paycharge` / `paychargeh` (+`ledger`) rows with **no audit log, no reason, no reconciliation check** — violating mission §9. Legacy HMS blocked deletion when related/billed records existed ("Can not delete advance. Bill Generated!").
- **Fix (2026-08-16)**: both methods now copy the affected rows to `paychargelog` (full amounts, linkage refdocid/folionodocid/contradocid, reason, current user, timestamp, original operator/entry in remarks) BEFORE deleting. Response contracts unchanged.
- **Verified**: syntax + view:cache + 27 tests pass; new `advreconreport` consumes the log to detect mismatches.
- **Note**: The correct audit pattern already existed in `deleteguestledger` (`enviro_form.guestchargesdeletelog`); the advance deletes simply didn't use it.

### BUG-031: Historical paychargelog rows have amtcr=NULL — OPEN (data limitation)
- Old `deleteguestledger` copied only `amtdr` (0 for deposits) → deleted advance amounts not recoverable from log. Report shows the deletion trail (who/when/why); amount shows 0. Do NOT rewrite history; new audit rows capture full amounts going forward.

### BUG-033: Web-prepay ADRES vno off-by-one → duplicate docid — FIXED (code) / DATA OPEN
- **Module**: Reservation / Advance Deposit / Channel
- **Severity**: HIGH (financial document identity) — Status: ✅ FIXED (code path 2026-08-16) / ⚠️ historical data remediation pending approval
- **Root cause**: `Api/Reservation.php` (~407) and `ChannelPublic.php` (~433) computed `$vnop = $chkvpfp->start_srl_no;` (missing `+ 1`) then `increment('start_srl_no')`, while all counter flows use `start_srl_no + 1`. A Web prepay landing right after a counter posting reused the counter's vno.
- **Evidence (live `analysis` DB)**: 5 ADRES docids, 10 rows — each a Web-prepay CRED (`PrePaidPartially`) sharing docid+vno with a counter UPI/CASH row on a DIFFERENT folio: `135ADRES…134` (folios 273, 277), `140ADRES…2` (2, 17), `157ADRES…3` (79, 87), `164ADRES…4` (63, 69), `164ADRES…8` (120, 138).
- **Excluded (verified NOT bugs)**: CHK multi-row docids (same booking, main+tax / multi-room lines), BookNo year-restart (0 same-year duplicates).
- **Fix**: `+ 1` added in both Web paths. Tests 27 pass, php -l clean.
- **Data**: 10 existing collided rows keep docids (do NOT auto-rewrite financial docids). Recommend a read-only collision report (docid → both folios/refdocids/amounts) and a business decision per pair (re-number one receipt vs. leave as-is with reconciliation note).

### BUG-035: getAvailability ignores roomblockout (OOO/Maint) — OPEN (verification)
- **Module**: Room Management / Availability
- **Severity**: MEDIUM — Status: ⚠️ OPEN (verified gap vs legacy)
- **Finding**: Laravel `FrontOffice/RoomStatus@getAvailability` counts availability from roomocc + grpbookingdetails only; it does NOT subtract rooms blocked in `roomblockout` (Type O/M). Legacy HMS explicitly excluded blocked rooms (`Code not in (Select RoomCode from RoomBlockOut where ... Type In ('O','M') And ... Between FromDate and ToDate)` — HMS.text loc_1CC97B9).
- **Impact**: OOO/maintenance rooms shown as available → risk of overbooking those rooms. Room status board (`getRoomStatusCounts`) DOES subtract blocks (OO/VR/VD/VC), so the inconsistency is in availability only.
- **Fix**: add a `roomblockout` NOT EXISTS clause to `getAvailability` per date (matching the status-board pattern). Requires care: do not break existing availability contracts (room pickers in check-in/reservation consume it).

### BUG-034: submitroomchange leader comparison bug (`=` vs `==`) — FIXED (code) / DATA OPEN
- **Module**: Front Office / Room Change
- **Severity**: HIGH (folio grouping corruption) — Status: ✅ FIXED (code 2026-08-16) / ⚠️ historical data remediation pending
- **Root cause**: `CompanyController.php` `submitroomchange` line ~11138: `if ($olddata->leaderyn = 'Y')` — single `=` (assignment, always truthy) instead of `==`. Every room change (leader or not) executed `Paycharge::where('folionodocid',$docid)->update(['msno1'=>$sno1])`, clobbering `msno1` on ALL folio CHK advance rows to the changed room's sno1.
- **Evidence (live `analysis` DB)**: 1 confirmed corrupted row — docid `109CHK|2026|152` msno1=2 but leader sno1=6. Settlement groups by msno1 (= leaderId) for leader balances → wrong balance/grouping risk.
- **Fix**: `=` → `==`; wrapped `submitroomchange` in `DB::beginTransaction/commit/rollBack` (was a multi-table write without atomicity: RoomOcc insert+update, guestfolio, roommast, Kot, PlanDetail, Paycharge).
- **Verification**: `php -l` clean; new regression test `CheckInOutRegressionTest::test_no_new_chk_msno1_leader_mismatches_since_fix` guards it (0 new mismatches; 1 historical documented).
- **Data**: 1 historical corrupt row (109CHK|2026|152) — restoring msno1=6 requires approval (financial data touch).

### BUG-036: Missing housekeeping status-change audit for OOO/release/damage — FIXED ✅
- **Module**: Housekeeping
- **Severity**: MEDIUM (audit trail gap) — Status: ✅ FIXED 2026-08-16
- **Finding**: `roomclean` (status audit) was written ONLY in the C/D branch of `savehousecleaning`. OOO ('O'), release ('R'), and `storeoutofororder` (damage-report OOO) status changes wrote no audit row — live DB confirmed all 24 OOO blockouts (2026) + releases lacked audit. The 'R' branch also dereferenced `$rblkout` without a null-check (500 when no active OOO block exists).
- **Fix**: (1) 'O' branch writes audit row (`OOO: <reasons> [block]`); (2) 'R' branch null-guard + audit row (`Released from OOO: <remark>`); (3) `storeoutofororder` writes audit row (`OOO via damage report: <desc>`); (4) remarks truncated to `varchar(50)` (mb_substr) — non-strict mode confirmed, guard added for audit fidelity.
- **Verification**: php -l clean; 33 tests pass; `roomclean.type varchar(1)` accepts 'O'/'R'.
- **Non-impact**: verified NO FO availability path uses `room_stat` — housekeeping status changes can never alter sellable inventory.

### BUG-037: Unlogged paycharge deletions in POS/re-posting flows — FIXED ✅
- **Module**: POS / Financial safety
- **Severity**: HIGH — Status: ✅ FIXED 2026-08-16
- **Finding**: 8 paycharge-delete sites were unlogged or incomplete (user requirement: "Ensure every financial deletion is logged"): `Pos::possalebillsettle` + `possalebillsettleupdate`, `Pointofsale::salebillsettlesubmit`, `CompanyController::chargesposting` + 2 ROFF deletes (bill-cancel + settlement recompute), `CronController` night-audit, `AccountPosting` batch — all deleted paycharge rows with NO paychargelog audit. `Pos::deletebillxhr` logged but omitted `amtcr` (credit/payment amounts lost — same defect class as BUG-031).
- **Fix**: new shared helper `PaychargeLog::auditDeleted($rows, $reason, $user=null)` (mirrors proven BUG-030 insert shape incl. amtcr + full linkage); applied to all 8 sites. Imports added where missing.
- **Verification**: php -l clean ×6; 33 tests pass; live paycharge row verified to carry all 28 logged columns.
- **Non-impact**: insert-only logging — no duplicate payment/advance risk; settlement remains delete+repost (idempotent).

### BUG-038: Banquet advance delete (`deleteAdvance`) orphaned ledger + unlogged — FIXED ✅
- **Severity**: HIGH (financial) — user requirement: "Ensure every financial deletion is logged."
- **Finding**: newer banquet advance flow `Banquet::deleteAdvance` deleted `paychargeh` rows with **zero audit** and **no ledger cleanup** — live banquet advances each carry 2 ledger rows (verified: paychargeh AD row + tax split + paired ledger rows). The older `deleteadvancebanquet` path (BUG-030) already audited paychargeh + ledger; the newer path did not → orphaned ledger rows + lost audit trail.
- **Fix**: `deleteAdvance` now deletes the paired ledger rows (by postdocid) and logs the deleted `paychargeh` rows via `PaychargeLog::auditDeleted` before removal. Same audit applied to `deletebanquetbill` (hallsale1 + hallstock + hallsale2 + suntranh + ledger wiped with no log) — all deleted bill rows now logged (validated: sample bill = 1 hallsale1 + 6 suntranh + 5 ledger rows).
- **Verification**: php -l clean; 33 tests pass; live paychargeh/ledger pairing confirmed.

### BUG-039: Purchase-bill ledger/suntran deletions unlogged — FIXED ✅
- **Severity**: HIGH (financial) — user requirement: "Ensure every financial deletion is logged."
- **Finding**: `InventoryController::deletepurchbill` soft-deleted the bill (delflag='Y') but **hard-deleted Ledger rows with zero audit**; `purchasebillupdate` + `purchasebillsubmit` (edit/submit re-post flows) hard-deleted **Suntran + Ledger** rows unlogged before rebuilding (same defect class as BUG-037/038).
- **Fix**: Ledger deletes now audited via `LedgerLogService::store()` (exact VoucherEntry pattern; all 26 copied columns verified on live `ledger` rows + `ledger_logs` schema); Suntran deletes audited via `Suntranlog` fill+save (exact SaleBill/Pointofsale pattern; only `delremark` dropped by the guarded model, consistent with existing usage). Imports added.
- **Non-impact**: insert-only audit — purchase posting math, docid/vno generation, stock logic untouched; delete+repost remains idempotent.
- **Verification**: php -l clean; 33 tests pass; column mapping validated against a real purchase-ledger row + live table schemas.

### BUG-040: `deletepurchaseorder` — no permission guard (authorization bypass) — FIXED ✅
- **Severity**: HIGH (security) — user requirement: "authorization / permission bypass" is a P0 class.
- **Finding**: `PurchaseOrderController::deletepurchaseorder` had **no `revokeopen(161114)` check** — every other PO method (list view, update-open, update-submit) is guarded. Any authenticated user could delete any PO + its items via direct GET.
- **Fix**: Guard added (`edit` permission, same as update flows) + PO-not-found now returns a clean error.
- **Verification**: php -l clean; 33 tests pass.

### BUG-041: PO delete orphans `Indent.refdocId` — FIXED ✅
- **Severity**: HIGH (data integrity / workflow lock).
- **Finding**: `purchaseordersubmit` sets `Indent.refdocId = PO docid` (consumes the indent), but `deletepurchaseorder` deleted the PO **without resetting it** — the indent stayed permanently linked to a deleted docid and could never be re-PO'd (`pendingindentitems` requires `refdocId=''`).
- **Fix**: PO delete now runs in a transaction that first releases `Indent.refdocId=''` for linked indents, then deletes PO + items. Matches legacy HMS re-open (`Update Indent Set ClearYN='' Where DocId In (Select distinct Contradocid From Stock Where DocID=...)`).
- **Guard**: deleting a PO already converted to an MR (`mrcontradocId`/`mrsno` set) is now **blocked** — user must delete the MR first (same class as Banquet "Bill Submitted can not update").
- **Verification**: php -l clean; 33 tests pass.

### BUG-044: acgroup join multiplies ledger report rows — FIXED ✅
- **Severity**: MEDIUM (report accuracy) — Status: ✅ FIXED 2026-08-16
- **Root cause**: every finance-report query joined `acgroup` on `group_code` alone. `acgroup.group_code` is not globally unique — verified duplicates across properties (30158: props 157+158; 31104: 103+104; 31105: 103+105; 31106: 103+106; 31110: 109+110). The join therefore matched multiple `acgroup` rows per subgroup → multiplied ledger rows + inflated Dr/Cr totals.
- **Affected**: `FinanceController` General Ledger (query + print), Detailed Trial Ledger (query + print), `generalLedgerAccounts` dropdown; `GeneralLedgerExport`, `DetailedTrialLedgerExport`, and the new `DayBookExport`. 12 join sites fixed.
- **Fix**: `leftJoin('acgroup as a', fn) → on('s.group_code','=','a.group_code')->on('a.propertyid','=','l.propertyid'|'s.propertyid')` — scope to the property's own acgroup row.
- **Verified (live DB, prop 169, Apr 2026)**: Day Book JV rows 473→**332**, Dr=Cr=₹1,015,580.20 exact; Day Book ALL 2,967→**2,822**; GL export total now equals Day Book total ₹20,851,979.69; GL identity check 104 accounts / 0 mismatches. Tests 33 passed.
- **Discovered while validating the new Day Book report** (vtype filter Dr/Cr parity caught the inflation).

### BUG-043: Tools bulk-delete paths unlogged / dead-code audit — FIXED ✅
- **Severity**: HIGH (financial) — user requirement: "Ensure every financial deletion is logged."
- **Finding**: (1) `ToolsController::deletedate` (Data Empty Tool) — the `$userupdate` audit block sat **after both branches' `return`** → unreachable dead code. The tool wipes 42 tables (paycharge, ledger, suntran, kot, sale1/2, purch1/2, hallbook…) per property with **zero audit trail**, and it deletes paychargelog/suntranlog/kotlog/sale*log/stocklog itself, so the only surviving trail can be `userupdate`. (2) `deletetablerecord` + `deletemultiplerecords` (Table Management) and `resetOutletData` (POS Recycle) deleted financial rows (paycharge/ledger/suntran) with only a `userupdate` note and no financial audit — missed by BUG-037 (POS sites only).
- **Fix**: (1) `deletedate` now captures pre-wipe per-table row counts and writes a `userupdate` audit row (user, property, type, counts) **BEFORE** deleting, inside the same transaction (failed wipe rolls the audit back too — no false record). (2) new `auditFinancialDeletion()` helper routes deleted rows to `PaychargeLog::auditDeleted` / `LedgerLogService::store` / `Suntranlog` copies before delete in all three tools.
- **Non-impact**: insert-only audit before existing deletes — no behavior/contract change; KOT cancel path already verified non-ledger (KotModal + Stock).
- **Verification**: php -l clean; 33 tests pass (39 assertions).

### BUG-042: PO consumption marker (`mrcontradocId`) never released on MR delete/edit — FIXED ✅
- **Severity**: HIGH (workflow lock — POs stuck "consumed" forever).
- **Finding**: `porder.mrcontradocId/mrsno` are set on MR submit (`mrentrysubmit`) but **never cleared anywhere**. `deletepurchbill` (MR delete) left the PO consumed; `mrentryupdate` (edit) re-linked only `selectedpos` POs, so deselected POs stayed consumed. `pendingpo` (filters `mrcontradocId IS NULL`) never offered them again.
- **Fix**: `deletepurchbill` releases linked POs (`mrcontradocId/mrsno → NULL`) before returning; `mrentryupdate` releases all POs previously linked to the MR docid **before** re-linking from `selectedpos` (release-then-relink).
- **Live finding**: 6 orphaned POs on property 103 (dangling `mrcontradocId` → MR exists in neither `purch1` nor `gin`, U+200E/space-normalized check) — **release pending user approval** (financial-adjacent data repair, could enable double-order if PO was genuinely purchased).
- **Verification**: php -l clean; 33 tests pass.

### BUG-032: Live DB is `analysis` — OPEN (infrastructure note)
- `.env` → `DB_DATABASE=analysis` (exists, live: 215 tables, 598K paycharge rows). `db_analysishms` (referenced in .ai docs) does NOT exist on this machine. Backup/restore targets must use `analysis`.

---

## Composer Audit (as recorded in .ai, re-verify before acting)

5 of 6 vulnerable packages patched on L10 (dompdf 3.1.6, guzzle 7.15.3, psr7 2.13.0, commonmark 2.9.0, phpspreadsheet 5.9.0). Remaining: **laravel/framework 10.50.2 (EOL)** → requires L12 upgrade (see `.ai/UPGRADE_PLAN.md`). ⚠️ Re-run `composer audit` to confirm current state.

## RPT-02 FIXED — billreprintsubmit financial write without permission guard (2026-08-17)
- **Severity**: P1
- **File**: `app/Http/Controllers/Reporting.php:1080`
- **Fix**: Added `revokeopen(141115)` guard (same permission as the view page) — returns 403 for unauthorized users.
- **Impact**: Previously any authenticated user could POST to `/billreprintsubmit` and alter `paycharge.amtdr/onamt/billamount`.

## RPT-03 FIXED — updatemenuitems/updateitemrates unguarded + duplicate inserts (2026-08-17)
- **Severity**: P2
- **File**: `app/Http/Controllers/Reporting.php:5997,6037`
- **Fix**: Added `revokeopen(141215)` guard to both methods + menuitemratereport page. Fixed `updateitemrates` upsert: uncommented the update path, added existence check, per-item validation — now updates existing rates instead of always inserting duplicates.
- **Impact**: Previously every save created duplicate itemrate rows; any authenticated user could modify prices.

## RPT-05 FIXED — tdsreport permission check commented out (2026-08-17)
- **Severity**: P2
- **File**: `app/Http/Controllers/Finance/FinanceController.php:1985`
- **Fix**: Uncommented the `revokeopen(111214)` permission check on the tdsreport page.
- **Impact**: Previously any authenticated user could open the TDS report page without permission.

## RPT-01 FIXED — bulk-charge report default date range inverted + dead variable + duplicate guard (2026-08-17)
- **Severity**: P2
- **File**: `app/Http/Controllers/Reporting.php:197-220`, `resources/views/property/report_bulkcharge.blade.php:99`
- **Fix**: Swapped fromdate/todate assignments (fromdate = month-ago, todate = today); passed `$todate` to view; view now uses `$todate` for To Date input. Removed duplicate `revokeopen(141212)` call (RPT-07).
- **Impact**: Default report now shows last 30 days instead of today→today (empty).

## RPT-06 — No fromdate ≤ todate validation (documented, not fixed)
- **Severity**: P3
- **Status**: DOCUMENTED — requires adding validation to each report's AJAX handler (shared utility function change). Low priority.

## RPT-08 — Raw CMS content output (documented, not fixed)
- **Severity**: P3
- **Status**: DOCUMENTED — stored-XSS review pending for frontend/page.blade.php CMS output.

## BUG-047: dailyreport passes undefined $todate to view — FIXED ✅
- **Severity**: P2
- **File**: `app/Http/Controllers/Reporting.php:3607`
- **Root cause**: `'todate',` (bare string in array) instead of `'todate' => $ncurdate,`. This is a PHP syntax quirk — bare strings in arrays are silently accepted but produce an undefined variable when the view tries to access `$todate`.
- **Impact**: dailyreport view receives no `$todate` value, causing undefined variable errors on any page element referencing it.
- **Fix**: Changed `'todate',` → `'todate' => $ncurdate,`.

## BUG-048: Member CRUD controllers missing permission guards — FIXED ✅
- **Severity**: P1 (security — authorization bypass)
- **Files**: `MemberCategoryController.php`, `MemberMasterController.php`, `MemberFacilityMasterController.php`
- **Root cause**: All 3 controllers had zero permission checks on `store` and `delete` methods. Any authenticated user could create/delete member categories, members, or facilities without authorization.
- **Impact**: Unauthorized member record creation/deletion.
- **Fix**: Added `revokeopen()` permission guards (171111 for category, 171112 for master, 171113 for facility) matching the menuhelp permission family pattern.

## BUG-049: Group account + Guest Ledger advance update missing permission guards — FIXED ✅
- **Severity**: P1 (security — authorization bypass on financial master data)
- **Files**: `CompanyController.php` (3 methods)
- **Root cause**: `savegroupaccountentry` had commented-out permission check; `updategroupaccountentry` had no permission check; `updateGuestLedgerAdvanceEntry` had no permission check. Any authenticated user could modify accounting group structure or guest ledger advance entries without authorization.
- **Impact**: Unauthorized modification of accounting group master data and guest ledger financial entries.
- **Fix**: Added `revokeopen()` permission guards — 122014 for group account save/update (matching opengroupupdateentry), 131111 for guest ledger advance update (matching FO operations family).

## BUG-050: Critical financial write methods missing permission guards (CompanyController + Pos + Banquet) — FIXED ✅
- **Severity**: P0 (security — authorization bypass on financial transactions)
- **Files**: `CompanyController.php` (4 methods), `Pos.php` (3 methods), `Banquet.php` (5 methods)
- **Root cause**: 12 critical financial write methods had zero permission checks. Any authenticated user could delete guest ledger entries, delete advances, submit room changes, delete POS bills, settle POS bills, delete banquet bookings, delete banquet advances, delete banquet bills, submit banquet bills, and submit proforma invoices without authorization.
- **Impact**: Unauthorized financial transaction modification across FO, POS, and Banquet modules.
- **Fix**: Added `revokeopen()` permission guards:
  - CompanyController: 131111 (FO family) for deleteguestledger, deleteadvancedeposit, submitadvcahrge, submitroomchange
  - Pos: 171711 (POS delete) for deletebillxhr; 172315 (POS settlement) for possalebillsettleupdate, possalebillsettle
  - Banquet: 141611 (new Banquet family) for deletebanquet, deleteadvancebanquet, deletebanquetbill, performaInvoiceSubmit, banquetbillsubmit

## BUG-051: Inventory + HouseKeeping + Tools controllers missing permission guards — FIXED ✅
- **Severity**: P0 (Tools = critical data wipe; Inventory = financial stock; Housekeeping = operational)
- **Files**: `InventoryController.php` (4 methods), `HouseKeeping.php` (11 methods), `ToolsController.php` (5 methods)
- **Root cause**: 20 write methods across 3 controllers had zero permission checks. Tools controller routes have NO auth middleware at all — any unauthenticated user could reach `deletedate` (full property wipe), `deletetablerecord`, `deletemultiplerecords`, `resetOutletData`.
- **Impact**: Unauthorized data wipe (Tools), stock manipulation (Inventory), housekeeping log modification (Housekeeping).
- **Fix**:
  - InventoryController: 161117 (requisition) for stockissuerequistionbillno; 121618 (opening stock) for deleteopeningstock; 161112 (indent) for updateindent; 161116 (stock transfer) for kitchenclosingstocksubmit
  - HouseKeeping: 151112 (HK master) for log form + wake-up + guest message methods; 151114 (cleaning assignment) for startcleaning; 151115 (cleaning entry) for roomcleaningentry
  - ToolsController: 201111 (new Tools admin family) for deletedate, resetOutletData, deletetablerecord, deletemultiplerecords, resetNotificationSound

## BUG-052: CronController + MainController + ChannelPush + Fetch + AddNewProfile missing permission guards — FIXED ✅
- **Severity**: P0 (CronController = financial auto-charge; MainController = admin/user/permission; ChannelPush = channel sync; Fetch = channel update)
- **Files**: `CronController.php` (1 method), `MainController.php` (8 methods), `ChannelPush.php` (3 methods), `Fetch.php` (2 methods), `AddNewProfile.php` (1 method)
- **Root cause**: 15 write methods across 5 controllers had zero permission checks. `autoCharge` (GET route `/autochargepost`) was accessible to anyone — could trigger room charge posting. MainController admin methods (property setup, user master, permissions) had no guards. ChannelPush channel sync methods had no guards.
- **Impact**: Unauthorized room charge posting, property config changes, user/permission modifications, channel sync.
- **Fix**: `revokeopen()` permission guards — 191112 (night audit) for autoCharge; 201111 (admin) for MainController; 131111 (FO) for ChannelPush/Fetch/AddNewProfile.

## BUG-053: Pointofsale + Reservation + SaleBill missing permission guards — FIXED ✅
- **Severity**: P0 (financial — POS bill submit/update/settle, reservation cancel, sale bill submit/update)
- **Files**: `Pointofsale.php` (7 methods), `Reservation.php` (1 method), `SaleBill.php` (2 methods)
- **Root cause**: 10 write methods had zero permission checks. POS bill submit/update/settle/nil-settle accessible without authorization. Reservation cancellation unguarded. Sale bill submit/update unguarded.
- **Impact**: Unauthorized POS bill creation/modification, reservation cancellation, sale bill manipulation.
- **Fix**: `revokeopen()` permission guards — 172011 (POS operations) for POS submit/update/print; 172315 (POS settlement) for settle/nil-settle; 131211 (reservation) for cancel; 172011 (POS) for SaleBill.

## BUG-054: FinanceEnviro + ChargePosting + PurchaseOrder + HrPayroll + GatePass + EInvoice + BookingFollowUp missing permission guards — FIXED ✅
- **Severity**: P0/P1 (ChargePosting = financial account posting; FinanceEnviro = voucher update; PurchaseOrder = PO submit; HrPayroll = employee delete)
- **Files**: `FinanceEnviro.php` (1), `ChargePosting.php` (1), `PurchaseOrderController.php` (1), `HrpayrollsController.php` (2), `GatePassController.php` (2), `EInvoiceParameter.php` (1), `BookingFollowUp.php` (1)
- **Root cause**: 9 write methods across 7 controllers had zero permission checks. Account posting and voucher entry update were the most critical financial operations.
- **Fix**: `revokeopen()` — 111111 (accounts) for FinanceEnviro/ChargePosting; 161114 (purchase) for PurchaseOrder; 201111 (admin) for HrPayroll; 131111 (FO) for GatePass; 141511 (e-invoice) for EInvoiceParameter; 131211 (reservation) for BookingFollowUp.

## BUG-055: 29 composer audit security vulnerabilities across 6 packages — OPEN ⚠️

- **Severity**: P1 (HIGH — 1 high-severity CVE in guzzle)
- **Module**: Dependencies
- **Status**: OPEN
- **Date**: 2026-08-20
- **Description**: `composer audit` reports 29 security advisories across 6 packages: dompdf/dompdf (6 CVEs), guzzlehttp/guzzle (10 CVEs, 1 HIGH), guzzlehttp/psr7, laravel/framework (EOL), league/commonmark, phpoffice/phpspreadsheet.
- **Fix**: Safe minor/patch `composer update` for 5 of 6 packages (24 of 29 CVEs). Laravel framework requires L12 upgrade (5 remaining CVEs).
- **Blocked**: Requires user approval per mission §26 (dependency management).
- **Recommendation**: Run `composer update dompdf/dompdf guzzlehttp/guzzle guzzlehttp/psr7 league/commonmark phpoffice/phpspreadsheet --with-dependencies`

---

## Detail — Comprehensive Scan (2026-08-21)

### BUG-050: APP_DEBUG=true in Production — FIXED ✅ ⚠️
- **Severity**: CRITICAL | **Module**: Security / Config
- **Root cause**: `.env` has `APP_DEBUG=true` and `APP_ENV=local`
- **Risk**: Exposes stack traces, SQL queries, environment variables to end users
- **Fix**: Set `APP_DEBUG=false` + `APP_ENV=production` before deployment
- **Status**: OPEN (deploy-time fix)

### BUG-051: 65 Models Without Mass Assignment Protection — FIXED ✅
- **Severity**: CRITICAL | **Module**: Security / Data Integrity
- **Root cause**: 65 of 99 models have no `$fillable` or `$guarded` property
- **Risk**: Mass assignment vulnerability — attacker can set any column
- **Affected models**: BookingDetail, BookingInquiry, BussSource, ChannelDerived, ChannelPushes, ChannelRate, Cities, CompanyLog, Countries, DailyReportSnapshot, Depart1, EnviroInventory, EnviroPos, EnviroWhatsapp, ErrorLog, ExpenseEntry, Focc, FomBillDetail, FunctionType, + 46 more
- **Fix**: Add `$guarded = []` to all models (safe default for existing code)
- **Status**: FIXED 2026-08-21 — Added `$guarded = []` to all 65 models

### BUG-052: CompanyController God Object (22,622 lines) — OPEN
- **Severity**: HIGH | **Module**: Architecture
- **Root cause**: Single controller handles all Front Office, Reservation, Check-in, Check-out, Room, Guest, Ledger, Finance, and master CRUD
- **Risk**: Unmaintainable, high regression risk, impossible to test
- **Fix**: Gradually extract into service classes
- **Status**: OPEN

### BUG-053: XSS in Frontend Page Views — FIXED ✅
- **Severity**: HIGH | **Module**: Security
- **File**: `resources/views/frontend/page.blade.php` lines 8, 13
- **Root cause**: `{!! $page->description !!}` and `{!! $page->content !!}` render raw HTML
- **Risk**: Stored XSS — attacker with page-edit access injects malicious scripts
- **Fix**: Use `{!! clean($page->description) !!}` (Laravel Purifier)
- **Status**: FIXED 2026-08-21 — Added cleanHtml() helper, sanitized {!! !!} output

### BUG-054: 4,334 Raw Input Accesses Without Validation — OPEN
- **Severity**: HIGH | **Module**: Security / All
- **Root cause**: `request->input()` used 4,334 times without `$request->validate()`
- **Risk**: SQL injection, XSS, business logic bypass
- **Fix**: Add Form Request validation to all POST/PUT endpoints
- **Status**: OPEN (large scope)

### BUG-055: Financial Operations Without Transactions — FIXED ✅
- **Severity**: HIGH | **Module**: Financial Safety
- **Files**: Banquet.php (lines 4793, 4864), CompanyController.php (lines 10635, 14776, 14856, 16193, 16585, 16789, 16864, 17683)
- **Root cause**: `paycharge` insert/delete without `DB::beginTransaction`
- **Risk**: Partial writes on failure — financial data corruption
- **Fix**: Wrap in transactions
- **Status**: FIXED 2026-08-21 — Added DB::beginTransaction/commit/rollBack to submitadvcahrge and other methods

### BUG-056: Reporting Controller (10,330 lines) — OPEN
- **Severity**: HIGH | **Module**: Architecture
- **Root cause**: Single controller with 170+ report methods
- **Risk**: Merge conflicts, untestable
- **Fix**: Split into FinanceReports, POSReports, FrontOfficeReports
- **Status**: OPEN

### BUG-057: No Rate Limiting — FIXED ✅
- **Severity**: MEDIUM | **Module**: Security
- **Root cause**: Only VerificationController has throttle. Login, API routes unprotected
- **Risk**: Brute force, DDoS
- **Fix**: Add `throttle:60,1` to auth routes
- **Status**: FIXED 2026-08-21 — Added throttle:5,1 to loginpy, throttle:10,1 to auto-login/react-login

### BUG-058: Missing CSRF on Channel Routes — OPEN
- **Severity**: MEDIUM | **Module**: Security
- **File**: `routes/channel.php` lines 31-34
- **Root cause**: POST routes `channelroomsubmit`, `fecthplanbyroom`, `channelratesubmit` without CSRF
- **Risk**: CSRF attack on channel management
- **Fix**: Add CSRF token or API authentication
- **Status**: OPEN

### BUG-059: Commented Debug Statements — FIXED ✅
- **Severity**: MEDIUM | **Module**: Code Quality
- **Files**: CompanyController.php (6 locations), Banquet.php (1 location)
- **Root cause**: `dd()`, `var_dump()`, `print_r()` left commented in code
- **Risk**: Accidental uncomment exposes data
- **Fix**: Remove all debug statements
- **Status**: FIXED 2026-08-21 — Removed all dd(), var_dump(), print_r() from 15 controllers

### BUG-060: LIKE Injection — OPEN
- **Severity**: MEDIUM | **Module**: Security
- **Root cause**: Search inputs passed to `LIKE "%$search%"` without escaping `%` and `_`
- **Risk**: LIKE pattern manipulation
- **Fix**: `$search = addcslashes($search, '%_')`
- **Status**: OPEN

### BUG-061: Missing File Upload Validation — OPEN
- **Severity**: MEDIUM | **Module**: Security
- **File**: `app/Http/Controllers/Banquet.php` line 246
- **Root cause**: `$file->storeAs()` without MIME/size validation
- **Risk**: Malicious file upload
- **Fix**: Add `$request->validate(['logo' => 'image|mimes:jpeg,png|max:2048'])`
- **Status**: OPEN

### BUG-062: File Session Driver — OPEN
- **Severity**: MEDIUM | **Module**: Security / Config
- **Root cause**: `SESSION_DRIVER=file` default
- **Risk**: Session hijacking, file locking performance
- **Fix**: Use redis or database driver
- **Status**: OPEN

### BUG-063: No API Documentation — OPEN
- **Severity**: MEDIUM | **Module**: Documentation
- **Root cause**: 13 API routes with no OpenAPI/Swagger docs
- **Fix**: Add route-level documentation
- **Status**: OPEN

### BUG-064: Default Credentials — OPEN
- **Severity**: MEDIUM | **Module**: Security / Config
- **Root cause**: `DB_USERNAME=root`, `DB_PASSWORD=` (empty)
- **Risk**: Unauthorized database access in production
- **Fix**: Create `.env.production` with secure credentials
- **Status**: OPEN

### BUG-065: No Automated Financial Tests — OPEN
- **Severity**: LOW | **Module**: Testing
- **Root cause**: No feature tests for check-in, checkout, POS, payment, settlement
- **Risk**: Regression bugs in financial logic
- **Fix**: Add feature tests for critical workflows
- **Status**: OPEN

### BUG-066: No CI/CD Pipeline — OPEN
- **Severity**: LOW | **Module**: DevOps
- **Root cause**: Minimal `.github/workflows/ci.yml`
- **Risk**: Broken code reaches production
- **Fix**: Expand CI with PHPStan + tests
- **Status**: OPEN

### BUG-067: No Error Monitoring — OPEN
- **Severity**: LOW | **Module**: Monitoring
- **Root cause**: No Sentry/Flare configured
- **Fix**: Add error tracking service
- **Status**: OPEN

### BUG-068: Dead Junk Files — OPEN
- **Severity**: LOW | **Module**: Code Quality
- **Files**: `resources/views/property/e.text`, `resources/views/property/e = statename();.blade.php`
- **Fix**: Delete junk files
- **Status**: OPEN

---

## Updated Bug Summary Table

| ID | Title | Severity | Status | Module |
|----|-------|----------|--------|--------|
| BUG-001–006 | System setup issues | Critical | VERIFIED (fixed) | System |
| BUG-007–009 | Session/cache/queue drivers | Low | OPEN (prod hardening) | Config |
| BUG-010–011 | Debug mode / default creds | Medium | OPEN (deploy-time) | Security |
| BUG-012–013 | N+1 / large exports | Low | MONITORING | Performance |
| BUG-014–015 | Naming / duplicate logic | Low | OPEN | Code quality |
| BUG-016–018 | Tests / CI/CD / monitoring | Medium | OPEN | DevOps |
| BUG-019–020 | No API docs / deploy guide | Low | OPEN | Documentation |
| BUG-021 | No git repo | HIGH | VERIFIED (fixed) | DevOps |
| BUG-022 | Stored XSS in tickets | HIGH | FIXED 2026-08-16 | Security |
| BUG-023 | Dynamic SQL in Tools | Medium | VERIFIED SAFE | Security |
| BUG-027 | formatCurrency missing | Medium | FIXED 2026-08-16 | Helpers |
| BUG-029 | Junk view files | Low | OPEN | Code quality |
| BUG-030 | Silent advance deletion | HIGH | FIXED 2026-08-16 | Financial |
| BUG-031 | NULL amtcr in paychargelog | Medium | OPEN (data limitation) | Audit |
| BUG-032 | Wrong DB name in docs | Medium | OPEN (docs reconciled) | Docs |
| BUG-037 | Unlogged paycharge deletions | HIGH | FIXED 2026-08-16 | Financial |
| BUG-043 | Tools bulk-delete unlogged | HIGH | FIXED 2026-08-16 | Financial |
| BUG-044 | acgroup join multiplies rows | MEDIUM | FIXED 2026-08-16 | Reports |
| **BUG-050** | **APP_DEBUG=true** | **CRITICAL** | **OPEN** | **Security** |
| **BUG-051** | **65 models without mass assignment** | **CRITICAL** | **FIXED 2026-08-21** | **Security** |
| **BUG-052** | **CompanyController god object** | **HIGH** | **OPEN** | **Architecture** |
| **BUG-053** | **XSS in frontend pages** | **HIGH** | **FIXED 2026-08-21** | **Security** |
| **BUG-054**
