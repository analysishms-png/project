# AI QA — BUGS (Master QA Orchestrator)

Status codes: 🐛 OPEN | ✅ FIXED | 🔁 REGRESSION-TESTED | ⛔ BLOCKED

---

## BUG-QA-001 — Banquet `performaInvoiceSubmit`: transaction commented out, orphan commit active
- **MODULE**: Banquet (performa invoice billing)
- **SEVERITY**: P1 (financial atomicity)
- **TITLE**: `DB::beginTransaction()` was commented out while `DB::commit()` stayed active — a mid-run failure (e.g. voucher-prefix error after partial inserts) would leave a performa invoice's postings half-written with no rollback.
- **ROOT CAUSE**: Legacy rewrite left the begin commented (`// DB::beginTransaction();`) with the commit live.
- **AFFECTED FILE**: `app/Http/Controllers/Banquet.php`
- **AFFECTED METHOD**: `performaInvoiceSubmit`
- **PROPOSED FIX**: Re-enable begin; add `DB::rollBack()` on the 2 early returns (no voucher-prefix / no hallbook) and in the catch.
- **STATUS**: ✅ FIXED + 🔁 regression test `testPerformaInvoiceSubmitTransactionReenabled`

## BUG-QA-002 — Banquet `deletebanquetbill`: 6-table financial delete without transaction
- **MODULE**: Banquet
- **SEVERITY**: P1 (financial atomicity)
- **TITLE**: Deletes HallSale1/HallSale2/HallStock/Suntran/SuntranH/Ledger (with paychargelog audit) with no transaction — a mid-sequence failure leaves the bill half-deleted while its audit says "deleted".
- **AFFECTED FILE**: `app/Http/Controllers/Banquet.php` → `deletebanquetbill`
- **STATUS**: ✅ FIXED + 🔁 `testDeleteBanquetBillIsTransactional`

## BUG-QA-003 — Banquet `deletePerformaInvoice`: 5-table delete without transaction; catch MASKS errors as success
- **MODULE**: Banquet
- **SEVERITY**: P1 (financial atomicity + error masking)
- **TITLE**: Deletes HallSale1Est/2Est/HallStockEst/SuntranEst/SuntranhEst with no transaction; the catch returned `'success' => true` on exception — a failed delete was reported to the UI as success.
- **AFFECTED FILE**: `app/Http/Controllers/Banquet.php` → `deletePerformaInvoice`
- **STATUS**: ✅ FIXED (transaction + `'success' => false`) + 🔁 `testDeletePerformaInvoiceTransactionalAndErrorNotMasked`

## BUG-QA-004 — Banquet `deleteadvancebanquet`: paychargelog audit + PaychargeH/Ledger deletes without transaction
- **MODULE**: Banquet (advance deletion)
- **SEVERITY**: P1 (financial atomicity)
- **AFFECTED FILE**: `app/Http/Controllers/Banquet.php` → `deleteadvancebanquet`
- **STATUS**: ✅ FIXED + 🔁 `testDeleteAdvanceBanquetTransactional`

## BUG-QA-005 — Banquet `deletebanquet`: booking-inquiry update + HallBook/VenueOcc deletes without transaction
- **MODULE**: Banquet (booking deletion)
- **SEVERITY**: P2 (multi-table delete atomicity; low data volume)
- **AFFECTED FILE**: `app/Http/Controllers/Banquet.php` → `deletebanquet`
- **STATUS**: ✅ FIXED + 🔁 `testDeleteBanquetTransactional`

## BUG-QA-006 — Banquet `banquetbillsubmit`: settlement delete+reinsert without transaction
- **MODULE**: Banquet (settlement)
- **SEVERITY**: P1 (financial atomicity — deletes old paycharge + Ledger rows then re-inserts settlement postings)
- **AFFECTED FILE**: `app/Http/Controllers/Banquet.php` → `banquetbillsubmit`
- **STATUS**: ✅ FIXED + 🔁 `testBanquetBillSubmitTransactional`

## BUG-QA-007 — HolidayController: no auth guard at all
- **MODULE**: Holiday master
- **SEVERITY**: P1 (security — unauthenticated data exposure)
- **TITLE**: `GET /holiday/data` returned all holiday rows to unauthenticated visitors (verified 200 before fix); `/holidaymaster`, `/holiday/store`, `/holiday/{id}` had zero auth (no middleware, no constructor guard) while every sibling controller guards via constructor middleware.
- **AFFECTED FILE**: `app/Http/Controllers/HolidayController.php`
- **STATUS**: ✅ FIXED (constructor auth middleware) + 🔁 `testHolidayControllerHasAuthGuard`

## BUG-QA-008 — submitadvdeposit: post-check-in advances never reach the folio (folionodocid empty)
- **MODULE**: Reservation / Advance / Folio (Phase-6 critical workflow)
- **SEVERITY**: P1 (financial — guest money never lands on the folio)
- **TITLE**: `submitadvdeposit` always inserted ADRES rows with an EMPTY `folionodocid` — even when the reservation was already checked in (`ContraDocId` set). The advance therefore never appeared on the guest's folio; the Advance/Folio reconciliation report flagged a permanent MISMATCH (ResAdvance > FolioAdvance) and staff compensated with manual ACCOUNT-TRANSFER RECs (which the report cannot link either, since it matches `refdocid = B.DocId`).
- **EVIDENCE (live DB, read-only)**: June 2026 window using the report's own logic — prop 135: 2 MISMATCH, prop 102: 1, prop 158: 4 (e.g. res#196: 6150 advance taken after check-in, folio credit 0, no compensating REC anywhere). prop 135 res#190: auto-copy DID run at check-in (paychargelog shows the CHK row deleted 03-29), staff re-posted as REC "ACCOUNT TRANSFER(24/03/2026)" with empty refdocid → money on folio but report-invisible. Across 2026 checked-in bookings: 564/727 flagged MISMATCH by the report (mix of post-check-in advances, manual-transfer linkage gaps, and pre-fix data).
- **ROOT CAUSE**: The advance-copy in `submitwalkin` only runs at check-in time. Any advance taken AFTER check-in (advance-deposit screen, still reachable from the reservation list) was never folio-linked.
- **AFFECTED FILE**: `app/Http/Controllers/Reservation/Advance.php`
- **AFFECTED METHOD**: `submitadvdeposit`
- **PROPOSED FIX**: Resolve `ContraDocId` from grpbookingdetails; when set (in-house), write `folionodocid = ContraDocId` and `foliono = guestfolio.folio_no` on the main ADRES row AND each tax row — mirroring the check-in advance-copy fields in submitwalkin. Reservation-only behavior (pre-check-in) unchanged.
- **STATUS**: ✅ FIXED + 🔁 `tests/Feature/AdvanceFolioLinkageTest.php` (structural + live-DB invariants).
- **NOTE**: Historical mismatched rows predate the fix; the reconcile report remains the tool to list them for manual review (staff already re-post them via ACCOUNT TRANSFER — now unnecessary for new advances).

## BUG-QA-009 — deleteadvancedeposit: audit + delete not atomic
- **MODULE**: Reservation / Advance
- **SEVERITY**: P2 (financial atomicity — paychargelog audit insert + Paycharge delete could be split by a mid-run failure, orphaning the audit trail)
- **AFFECTED FILE**: `app/Http/Controllers/CompanyController.php`
- **AFFECTED METHOD**: `deleteadvancedeposit`
- **STATUS**: ✅ FIXED (wrapped in transaction; error no longer mid-flight) + 🔁 `test_deleteadvancedeposit_is_transactional`

---

## Phase-6 reconcile audit summary (2026-08-17)
**Method**: traced reservation→advance→check-in→folio→settlement write paths; ran the app's own authoritative `advreconreportfetch` logic read-only against live data (guestfolio join = checked-in definition, ResAdvance = ADRES/ARRES sum, FolioAdvance = folio-linked credit, DelAmount = paychargelog).

**Verdict**: the critical workflow is transactional end-to-end (submitadvdeposit ✅, submitwalkin check-in+advance-copy ✅ transactional, submitRoomSettle ✅) and reconciles correctly on the standard path (advance before check-in, check-in via prefilled walkin): prop 174 7/7 exact, prop 158 288 auto-copies in 2026. The residual MISMATCHes were (a) BUG-QA-008 (post-check-in advances) — now fixed, and (b) manual ACCOUNT-TRANSFER workarounds that break the report's refdocid linkage — staff process, documented in the report note above.

## BUG-QA-010 — DayBook/JournalBook/CashBankBook/GeneralLedger: INNER JOIN silently drops orphan-subcode postings
- **MODULE**: 12 Accounts / 22 Reporting (Day Book, Journal Book, Cash/Bank Book, General Ledger)
- **SEVERITY**: P1 (report accuracy — totals did not reconcile to the ledger)
- **TITLE**: All ledger-composition queries joined `subgroup` with an **INNER JOIN**, but the legacy query was `VIEWLEDGER LEFT JOIN SUBGROUP`. Ledger postings whose `subcode` is empty or missing from `subgroup` were silently dropped — Day/Journal Book totals understated the raw ledger and showed `dr != cr`.
- **ROOT CAUSE**: HPOST advance legs are written with an empty `subcode` when the property's `roomchrgdueac` account is unconfigured (`AccountPosting` writes `'subcode' => $envirofom->roomchrgdueac`, which is `''` on many properties). `sub_code` is globally unique in `subgroup` (verified: 10,735 rows, 0 duplicates; `group_code` also unique per property) so the join can't multiply — it can only drop.
- **LIVE EVIDENCE (read-only, 2026)**: 683 orphan rows / **₹7,024,242.83 dr** across **41/72 properties** dropped before the fix (worst: prop 149 — 210 rows / ₹4.11M; prop 115 — 87 rows / ₹477K; prop 160 — 36 rows / ₹492K; prop 175 — 95 rows / ₹257K). After the fix: **0/72 properties** have report-vs-raw mismatch (row count + dr + cr all reconcile). Cash/Bank per-account identity (opening + period = closing) verified on live data.
- **AFFECTED FILES**: `app/Http/Controllers/Finance/FinanceController.php` (`dayBookRows`, `cashBankBookRows`, `generalLedgerQuery`, `printGeneralLedger`), `app/Exports/DayBookExport.php`, `JournalBookExport.php`, `CashBankBookExport.php`, `GeneralLedgerExport.php`
- **STATUS**: ✅ FIXED (all `join('subgroup')` → `leftJoin('subgroup')` = legacy VIEWLEDGER parity) + 🔁 `tests/Feature/ReportReconcileTest.php` (3 tests / 20 assertions: structural LEFT-join assertions + live report-vs-raw reconcile + JV double-entry balance).
- **NOTE**: PBPC/PBPB (purchase-bill) vouchers on some properties (e.g. prop 149: +₹5.26M all-time) are dr-only in `ledger` — the payable half is posted elsewhere. That is a **source-data pattern, not a report defect**; reports now faithfully mirror the ledger. Flagged for accounts review. TDS report's `subgroup` join is a party-name lookup (different semantics) — intentionally left as-is.

## Cross-controller sweep summary (2026-08-17)
Static scan of all 27 write-bearing controllers: methods containing insert/update/delete chains checked for `beginTransaction`+`commit` and permission guards.

- **Already safe** (verified, no change): CompanyController, InventoryController, Pointofsale/Pos/SaleBill, HouseKeeping, Kot, CronController, AccountPosting, VoucherEntry, PrintController (single-table writes), UserParam (guarded), GatePassController store (guarded), MaintenanceController/HappyhourController single writes.
- **MainController admin routes** (`disablepropertyadmin`, `enablepropertyadmin`, `disableusermaster2`, `enableusermaster2`, `update_usermaster2`, `update_user_ap2`): no route-level middleware, but the controller constructor redirects unauthenticated AND non-property-10 users (verified 302 empirically). Defense-in-depth note: could add `->middleware('superadmin')` at route level — LOW priority, not exploitable today.
- **Orphaned legacy `holiday` table** (vdate/repeatflag/activeyn): no reads anywhere in `app/`; the Laravel module writes the new `holidays` table (holiday_date/is_repeat/is_active). Not a defect — dead legacy table; flagging for DB-cleanup consideration only.
- **Code smell (P4)**: emoji-named variable `$jaldiwahasehato📢` present in CompanyController (48×), MainController (10×), Pos (2×), PrintController (11×) — cosmetic; not fixing to avoid churn.

## BUG-QA-011 — openupdatereservation/openupdatewalkin: availableRooms closure 500s ("Undefined variable $row")
- **MODULE**: 7 Reservation / check-in edit pages
- **SEVERITY**: P1 (page completely broken — 500 on every cache miss)
- **TITLE**: `MasterDataCache::availableRooms(...)` was called with a closure whose body references the foreach variable `$row->RoomCat`/`$row->roomcat`, but the `use (...)` clause captured only `$checkindate, $previousdate`. The closure runs inside `Cache::remember`, so on cache miss PHP raised `Undefined variable $row` — the reservation-edit page (which hosts the advance-delete button) and the in-house edit-walkin page 500'd.
- **FOUND VIA**: Phase-4 browser walkthrough on the QA instance (POST /updatereservation → 500, laravel.log: `Undefined variable $row at CompanyController.php:2027`).
- **AFFECTED FILE**: `app/Http/Controllers/CompanyController.php` → `openupdatereservation` (~line 2008) and `openupdatewalkin` (~line 1790)
- **PROPOSED FIX**: capture the room category as a plain scalar (`$roomCat = $row->RoomCat;`) and `use ($checkindate, $previousdate, $roomCat)`; reference `$roomCat` inside the closure.
- **STATUS**: ✅ FIXED + 🔁 `tests/Feature/AdvanceDeleteAuditTest.php` (structural closure-capture assertions on both methods). Verified via browser: /updatereservation renders, delete button works.

## BUG-QA-012 — deleteguestledger: audit row omits refdocid/amtcr → deletions invisible to reconciliation report
- **MODULE**: 10 Folio / Ledger (guest-ledger delete)
- **SEVERITY**: P2 (audit-trail completeness + report linkage)
- **TITLE**: The `paychargelog` audit row written by `deleteguestledger` copied only `amtdr` — **not `refdocid`, not `amtcr`, not `paytype`**. The Advance/Folio Reconciliation report links deletions via `PL.refdocid = B.DocId` (or the docid of a still-existing ADRES/ARRES row) and sums `amtcr - amtdr`; without `refdocid`/`amtcr` a deleted advance is invisible to `DelAmount` (and its amount is lost from the audit trail). Also: the log fetch was scoped by `vno` only (could log a DIFFERENT vtype sharing the vno), and the log-insert + delete were not atomic.
- **LIVE EVIDENCE (read-only)**: **466 ADRES/ARRES deletion rows on live data** (2026: 429) have `refdocid IS NULL` **and** `amtcr IS NULL` — all invisible to the report's DelAmount (0 rows linkable).
- **AFFECTED FILE**: `app/Http/Controllers/CompanyController.php` → `deleteguestledger`
- **PROPOSED FIX**: copy `refdocid` + `amtcr` + `paytype` into the log row (matching the audited `deleteadvancedeposit` pattern), scope the log fetch by `vno` **and** `vtype` (same scope as the delete), and wrap log-insert + delete in one transaction.
- **STATUS**: ✅ FIXED + 🔁 `tests/Feature/AdvanceDeleteAuditTest.php` (structural: refdocid/amtcr/paytype present, vtype-scoped fetch, transactional). Note: the 466 historical rows cannot be retroactively linked (amount never stored) — they remain invisible; only new deletions are covered. Consider a data-repair pass via `ToolsController` if the business wants history reconstructed.

## Delete-path audit summary (2026-08-17, QA session 4)
Chain audited: `deleteadvancedeposit` → `paychargelog` → `advreconreportfetch` DelAmount linkage.
- **Audited path (deleteadvancedeposit) is SOUND**: writes a complete audit row (refdocid, amtcr, folionodocid, u_ae='e', full remarks) inside a transaction. Verified end-to-end on the QA instance: deleted ₹2000 → paychargelog row with refdocid=RES → report DelAmount=2000, Recon=0. **Zero double counting.**
- **Legacy path (deleteguestledger) had the gap above (BUG-QA-012)** — now fixed for new deletions.
- The reconciliation report itself (`advreconreportfetch`/`detail`/`restore`) uses identical linkage semantics on both sides (delete and restore), so the fix restores symmetry.

## BUG-QA-013 — DataTables plugin never loaded; 100+ pages silently lack table features (FIXED)
- **MODULE**: All modules (property/tools/admin layouts — 101 views call DataTables)
- **SEVERITY**: P1 (functional — tables had no sorting/search/pagination; some pages threw JS errors)
- **TITLE**: The shared layouts never loaded the DataTables plugin, yet views call it — 49 use the DataTables **2.x-only** `new DataTable(...)` syntax (e.g. roommaster), dozens use `$().DataTable()`. The bundled asset was DataTables 1.10.18 and was never included (verified: absent from the footer in the initial commit). Every such page threw `$(...).DataTable is not a function` / `Cannot read properties of undefined (reading 'isDataTable')`.
- **ROOT CAUSE**: Asset missing from the layout include chain; code targets DataTables 2.x.
- **AFFECTED**: `resources/views/property/layouts/header.blade.php`, `tools/layouts/header.blade.php`, `admin/layouts/header.blade.php` (includes added).
- **FIX**: Vendored DataTables **2.3.2** core + Buttons 3.2.0 + Responsive 3.0.3 + JSZip + pdfmake into `public/admin/plugins/datatables2/`; loaded CSS in `<head>` and JS after jQuery in all three layouts. Also null-guarded roommaster's `#name`/`#namelist` listeners (`addEventListener` pageerror on a non-existent element).
- **REGRESSION**: Playwright on QA — roommaster/voucherentry/advancelist/checkinlist/bookingsource all show full 2.x features (search/info/paging); export buttons render (voucherentry 3, bookingsource 4); **0 DataTables/page errors**; mobile OK; admin-layout page loads globals. Full suite **68 passed (165 assertions)**.
- **STATUS**: ✅ FIXED + 🔁 verified via browser automation

## Pre-existing, still OPEN (noted during this pass)
- `Datamap is not defined` on `superadmin/backups` — page references a Google-Charts `Datamap` global that isn't loaded on that page. Unrelated to DataTables. Candidate BUG for a future pass.

## BUG-QA-014 — Housekeeping Command Center dropped ALL rooms when no floors configured (FIXED)
- **MODULE**: Housekeeping (roomstatusboard + assignment reports)
- **SEVERITY**: P1 (core HK screen showed an empty board; assignment reports silently missing rooms)
- **TITLE**: `getRoomsWithStatus` and 5 assignment-report queries **INNER-joined `hkfloors`** on `RM.floor = FL.code` — any room without a matching `hkfloors` row was silently dropped. Live proof: property 102 has **0 `hkfloors` rows and all 15 rooms have empty `floor`** → the Command Center rendered 0 rooms and 0% occupancy **in production**.
- **ROOT CAUSE**: INNER join on a lookup table the property may not have configured (same class as BUG-QA-010). 4 other sites in the same controller already used `leftJoin('hkfloors')`, confirming the anomaly.
- **AFFECTED**: `app/Http/Controllers/HouseKeeping.php` — 6 sites (2196/2246/2303/2385/2485 in assignment reports, 2868 in `getRoomsWithStatus`).
- **FIX**: All 6 → `leftJoin('hkfloors')`; view groups empty floors under "Unassigned Floor".
- **REGRESSION**: Playwright on QA — 15 rooms render with real statuses (Occupied Dirty 2 / Vacant Dirty 13 / 13.33% occupancy); assignments/view/housekeepingscreen/roomstatus all clean; suite 68 passed.
- **STATUS**: ✅ FIXED + 🔁 browser-verified

## BUG-QA-015 — HK Command Center fabricated data + broken modal (FIXED)
- **MODULE**: Housekeeping roomstatusboard view
- **SEVERITY**: P2 (false data shown + broken UI)
- **TITLE**: The Command Center view (a) **fabricated** Pending-Inspections rows ("304 / Rakesh Kumar / High") that don't exist in the DB — violating the no-invented-values rule; (b) workload "Done" column was a "-"/0% placeholder; (c) the room modal posted to `/property/rooms/{roomno}/status` — **a route that does not exist** (404 on Save) — and used BS5 attributes (`data-bs-toggle`/`data-bs-close`) on the BS4 runtime so it never even opened.
- **FIX**: Pending Inspections now lists real `INSPECT`-status rooms (empty state otherwise); workload shows real Assigned/Done/Efficiency from `hkroomassigns.status`; modal is read-only Room Details (no non-existent endpoint), opened via jQuery `modal('show')`, closed with BS4 `data-dismiss`.
- **REGRESSION**: Playwright — modal opens with real room/status, closes; 0 errors; suite 68 passed.
- **STATUS**: ✅ FIXED + 🔁 browser-verified
