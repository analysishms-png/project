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
| BUG-014 | Duplicate helper logic | Low | OPEN | Code quality |
| BUG-015 | Inconsistent naming | Low | OPEN | Code quality |
| BUG-016 | Limited test coverage | Medium | OPEN (partially fixed) | Testing |
| BUG-017 | No CI/CD | Medium | OPEN | DevOps |
| BUG-018 | No error monitoring | Medium | OPEN | Monitoring |
| BUG-019 | No API docs | Low | OPEN | Documentation |
| BUG-020 | No deployment guide | Low | OPEN | Documentation |
| BUG-021 | No git repo | HIGH | VERIFIED (fixed — 1 baseline commit) | DevOps |
| BUG-022 | **Stored XSS in ticket views** | **HIGH** | **FIXED 2026-08-16** | Security |
| BUG-023 | Dynamic SQL interpolation (Tools) | Medium | VERIFIED SAFE 2026-08-16 | Security |
| BUG-024 | Debug mode enabled (dup of BUG-010) | Medium | OPEN | Security |
| BUG-025 | God controllers / zero eager loading | Medium | OPEN | Architecture/Perf |
| BUG-026 | Minimal caching + sync queues | Low | OPEN | Performance |
| BUG-027 | **formatCurrency helper missing (docs ≠ code)** | **Medium** | **FIXED 2026-08-16** | Helpers/Tests |
| BUG-028 | `.ai` docs overstate repo state (uncommitted work) | Low | OPEN (docs reconciliation) | Documentation |
| BUG-029 | `e = statename();.blade.php` junk file in views | Low | OPEN (cleanup) | Code quality |
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
