# Analysis HMS — NEXT TASK (WORK QUEUE)

> Auto-maintained queue. Always work the highest-priority **incomplete** task.
> Priority: P0 security/data/financial/core → P1 logic/reports → P2 perf/UI → P3 docs/refactor.

---

## Completed

### ✅ P1 — Purchase analysis + PO/Indent linkage fixes (BUG-040/041/042) — 2026-08-16
- **Traced**: Indent → PO → MR/bill lifecycle (porder/porder1, Indent.refdocId consumption, porder.mrcontradocId/mrsno consumption markers, pendingpo/pendingindentitems filters) vs legacy (FrmPurch, Indent ClearYN re-open).
- **BUG-040 FIXED**: `deletepurchaseorder` had zero permission guard (any user could delete POs) — now `revokeopen(161114)` edit-guarded.
- **BUG-041 FIXED**: PO delete now releases `Indent.refdocId` back to pending (was permanently locked); consumed-PO deletion blocked.
- **BUG-042 FIXED**: `deletepurchbill` + `mrentryupdate` release `porder.mrcontradocId/mrsno` (POs were stuck consumed forever after MR delete/deselect).
- **Live finding (held for approval)**: 6 orphaned POs on property 103 — dangling mrcontradocId to MRs existing nowhere; releasing markers is a 6-row financial-adjacent data repair.
- New doc: `.ai/PURCHASE_GAPS.md`. Suite: 33 passed (39 assertions).

### ✅ P1 — Inventory analysis + financial-deletion audit (BUG-039) — 2026-08-16
- **Traced** (Laravel vs legacy HMS): Item masters (FrmItemMast/Cat/Group, FrmConsumMast→itemmast.Type), Opening Stock (FrmOPStock), Stock Transfer (FrmStockTransfer), MR/Purchase (FrmPurch: Purch1/Purch2/Sale2/Suntran/Ledger), Requisition, Stock Issue, Indent, Kitchen Closing Stock, Recipe Master.
- **BUG-039 FIXED**: `deletepurchbill` (Ledger hard-delete unlogged), `purchasebillupdate` + `purchasebillsubmit` (Suntran+Ledger hard-delete unlogged) — now audited via `LedgerLogService::store()` + `Suntranlog` (project patterns). All other inventory deletes verified non-ledger.
- **Report parity**: stock register/movement/trade/valuation/purchase-amount/delay-delivery/receiver-pending-mr/pending-mr all present. INV-01..03 documented.
- New doc: `.ai/INVENTORY_GAPS.md`. Suite: 33 passed (39 assertions).

### ✅ P1 — Banquet analysis + audit fixes (BUG-038) + Outstanding report — 2026-08-16
- **Traced**: Enquiry → Booking → Hall → Function → Menu/Package → Advance → Discount/Tax/Round Off → Settlement → Bill vs legacy HMS. Hall availability ✅, duplicate-booking ✅ (venueavailability), advance (paychargeh AD), settlement (paychargeh IDC), performa invoice ✅.
- **BUG-038 FIXED**: `deleteAdvance` (newer flow) — orphaned ledger rows + no audit; `deletebanquetbill` — full bill wipe unlogged. Both now audit via `PaychargeLog::auditDeleted` + ledger cleanup.
- **Config note**: `roundoffac`/`discountac` configured but posting flows through Sundrytype revcode (legacy divergence documented, not changed).
- **NEW: Banquet Outstanding report** — Bill vs Advance vs Settled per hallbook, outstanding filter, Excel/Print. Live scan: ₹1.9M+ real outstanding across properties (prop 141 ₹252,525; prop 132 ₹305,030) + overpayment anomalies surfaced.
- New doc: `.ai/BANQUET_GAPS.md` (to be written next pass). Suite: 33 passed (39 assertions).

### ✅ P1 — KOT / NC / TOKEN analysis + KOT token sequence (legacy parity) — 2026-08-16
- **Checked**: KOT creation/print (`sendprintdata`→printdelay, printer routing), transfer (preserves tokenno), cancellation (KotModal+Stock only), NC KOT (flag+type+reason), NC type master, previous-NC fetch, NC KOT report (with reason). **NC fully covered — no gaps.**
- **Implemented**: KOT token sequence — `kot.tokenno = depart.cur_token_no_kot + 1` per outlet in `submitkotentry` (live DB had 3,192 KOT rows, 0 with tokenno; counter never read). Non-financial, additive.
- **Documented (need business decision)**: token print/display (printdelay schema + external spooler), daily auto-reset hook (`auto_reset_token` saved but unused), meal-token master (`FrmPlanTokenMast`), `PlanMealTokens` report.
- New doc: `.ai/KOT_NC_TOKEN_GAPS.md`. Suite: 33 passed (39 assertions).

### ✅ P1 — POS analysis + audit of all paycharge deletions (BUG-037) — 2026-08-16
- **Traced** (Laravel vs legacy HMS): Menu → KOT → KOT Transfer → Bill → Tax/Discount → NC → Payment/Room Charge (PPOS/IPOS) → Settlement (TOUT/REC) → Stock → Accounting re-posting. Checked duplicate bill/payment (idempotent delete+repost, UI TOUT guard), KOT cancel (KotModal+Stock only, not ledger), merge (mergedwith), split (partial), complimentary, discount, tax, payment modes, stock deduction.
- **BUG-037 FIXED**: 8 paycharge-delete sites unlogged (settlement re-posts ×2, alt settle, chargesposting, night-audit cron, AccountPosting batch, 2 ROFF deletes) + deletebillxhr missing amtcr. New `PaychargeLog::auditDeleted()` helper applied everywhere — every financial deletion now audited (user/time/reason/amtdr/amtcr/linkage).
- **Legacy comparison**: UnSettledBillsInfo ✅, POSBillDeletion ✅ (audit superior), RecycleData ✅ REPLACED by paychargelog+restore, FrMultiBill ⚠️ split UI partial, FrmPOSBillModification* ⚠️ MISSING (needs business decision), FrmPOSSaleDataTransfer ⚠️ likely obsolete.
- New doc: `.ai/POS_GAPS.md`. Suite: 33 passed (39 assertions).

### ✅ P1 — Housekeeping analysis + status-change audit fix (BUG-036) — 2026-08-16
- **Traced** (Laravel vs legacy HMS): room cleaning entry (amenities stepper), `hkcleaninghdr` completion audit, room status board (FrmHouseStatus legacy — Laravel superset), assignment/inspection, damage report → `storeoutofororder` OOO, Lost & Found, Laundry.
- **Verified safety requirement**: NO FO availability path uses `room_stat` — housekeeping can never change sellable inventory (availability derives from roomocc + grpbookingdetails + roomblockout only).
- **BUG-036 FIXED**: `roomclean` audit now written for OOO ('O'), release ('R'), and damage-report OOO (`storeoutofororder`); 'R' branch null-guard added; audit remarks truncated to varchar(50). Live DB had 0 audit rows for the 24 OOO blockouts.
- **Legacy gap noted (NOT HK scope)**: `FrmItemIssuedOnCleaning`/`DepartWiseItemIssueList` is a store/inventory issue flow → belongs to INVENTORY module.
- New doc: `.ai/HOUSEKEEPING_GAPS.md`. Suite: 33 passed (39 assertions).

### ✅ P1 — Room Management analysis + reconciliation report — 2026-08-16
- **Traced** (Laravel vs legacy HMS): Room Master, Room Category, Room Features, Room Status board (TR/OO/OD/OR/OC/VR/VD/VC), RoomOcc/RoomOccDet (legacy view), Room Change (BUG-034 already fixed), Room Block (roomblockout), Room Availability, Room Settlement.
- **Verified COMPLETE**: roomstatus board counts (blocks subtracted), housekeeping statuses (D/C/O), OOO flow (damage-report → storeoutofororder), room change, settlement, room category/master CRUD.
- **Verified gaps**: BUG-035 — `getAvailability` ignores roomblockout (legacy subtracted blocks); extrabed column exists but unused in 2026 data; roomblockout type values differ (legacy O/M, data shows O/R).
- **New read-only reconciliation report** `roomrecon` (8 tabs: orphan occupancy, room w/o master, folio w/o room, occupied w/o charges, occupied status, blocked+occupied, stale blocks, extra bed) — verified on live DB (property 158: 1 room-missing-master, 3 stale blocks; all properties: 1 orphan active RoomOcc, 111 RoomOcc w/o room_mast, 37 folios w/o room in 2026).

### ✅ P1 — Check-in/Check-out deep analysis + room-change bug fix + regression tests — 2026-08-16
- **Traced** (Laravel vs legacy HMS): Reservation → prefilled check-in (`openprefilledwalkin`) → Guest Profile → RoomOcc → GuestFolio → PayCharge → CHK advance transfer → settlement/checkout (`submitRoomSettle`) → bill → room release. Legacy confirms same schema (RoomOccDet view, ModeSet/SettleDate, PayCharge.RefDocId).
- **Check-in** (`submitwalkin`): already transactional — RoomOcc + GuestFolio + GuestFolioProfDetail + GuestProf + PlanDetail + CHK advance rows, with leader/full-advance handling and room-repeat validation.
- **Checkout** (`submitRoomSettle`): already transactional — REC rows, roomocc type='O', room release (room_stat D), grpbookingdetails chkoutyn='Y', fombilldetails settleamt.
- **BUG-034 FIXED**: `submitroomchange` `if ($olddata->leaderyn = 'Y')` (assignment → always true) clobbered msno1 on all folio CHK rows on every room change; live evidence: 109CHK|2026|152 (msno1=2, leader=6). Also wrapped room change in DB transaction + null-guard.
- **Regression suite added**: `tests/Feature/CheckInOutRegressionTest.php` — 6 read-only invariants (ADRES collision, msno1/leader, orphan folio, booking link, advance duplication, checkout-date). Full suite now **33 passed (39 assertions)**.
- **Edge cases verified live**: reservation w/o advance, multiple rooms, room change, early checkout, unpaid balance (see fodiagnostics). Historical anomalies documented: 2 roomocc type=O w/o chkoutdate (115CHK|2026|166, 157CHK|2026|360).

### ✅ P1 — Reservation module analysis + Web-prepay vno collision fix (BUG-033) — 2026-08-16
- Traced New Reservation → Guest → Room → Rate → Plan → Company/TA → Advance → Confirmation → Modification → Cancellation → Check-in in Laravel vs legacy HMS (legacy confirms BookNo per-year numbering, PayCharge refdocid = booking DocId, VType ARRES/ADRES/AWRES).
- **Fixed**: Web-prepay ADRES vno off-by-one (`Api/Reservation.php`, `ChannelPublic.php` — `start_srl_no` → `start_srl_no + 1`) causing duplicate docids. Live DB: 5 collided ADRES docids / 10 rows (Web CRED vs counter UPI/CASH). CHK multi-row docids + BookNo year-restart verified NOT bugs.
- Reservation module otherwise COMPLETE: letters (resletter/rescard/cancelletter), email, proforma invoice, cancellation, advance deposit (audited + reconcile-reportable).

### ✅ P0 — Stored XSS in ticket views (BUG-022) — 2026-08-16
`{!! $ticket->problem !!}` → `{{ nl2br(e($ticket->problem)) }}` in 3 views. Verified: tests 27 pass, views compile.

### ✅ P1 — Front Office mismatch diagnostics (2026-08-16)
- `fodiagnostics` read-only page (6 tabs) — verified live: 49 no-shows, 4 folios on cancelled bookings, 500+ res-vs-folio mismatches, 28 settled-with-open-balance folios (property 158).

### ✅ P0 — Advance/Folio reconciliation report + audit-safe advance deletion + safe restore (2026-08-16)
- Read-only `advreconreport` (routes + Reporting@advreconreport/fetch/detail + blade view) — traces ADRES/ARRES → CHK transfer → paychargelog deletions; flags MISMATCH/OVER-CREDIT/PENDING-TRANSFER/CANCELLED-CHECK. Verified on live `analysis` DB (11 real mismatches in sample).
- `deleteadvancedeposit` + `deleteadvancebanquet` now write paychargelog audit rows before deleting (BUG-030).
- `advreconrestore` — guarded, transactional, audited restore of missing folio advance (never duplicates; refuses settled/cancelled/no-folio/balanced). **Feature verified on live data (1 currently in-house booking, Res 49, ₹1,300, is restorable). Restore NOT executed — needs user approval per booking.**

---

## Queue (next candidates, highest first)

### P0
1. **Execute restores on the flagged mismatches** — starting with Res 49 (₹1,300, folio 47, room 12). **STOP for explicit user approval per booking — mutates financial data.**
2. **ADRES docid collision data remediation (BUG-033 data side)** — read-only report listing the 5 collided docids / 10 rows (docid → both folios, refdocids, amounts, payment modes, dates). Business decision per pair: re-number one receipt vs. leave + reconciliation note. **STOP for approval before any re-numbering.**
3. **Repair 1 historical msno1 corruption (BUG-034 data side)** — docid `109CHK|2026|152`: msno1=2 should be 6 (leader sno1). **STOP for approval — financial data touch.** Also 2 roomocc type=O w/o chkoutdate (115CHK|2026|166, 157CHK|2026|360).
4. **Audit remaining silent deletes** (ML-08): `ToolsController::deletedate`, `delete_table_record`, `deletemultiplerecords`, POS/KOT deletion paths — add paychargelog audit like BUG-030.
5. **Verify legacy-only modules exist in Laravel** (MODULE_STATUS ⚠️ list): Lost & Found, Denomination, ForEx, MeterReading, WakeUp, PaxDetails, UnSettledBillsInfo, HotKey. Scan routes+views first; classify MISSING/REPLACED/OBSOLETE.
6. **Remaining transaction-safety audit** — check-in/check-out are transactional (verified); audit folio merge/reverse-merge, POS billing, stock issue/transfer, voucher posting (ML-02..07). Read first, then propose.

### P1
5. **GUEST MANAGEMENT gaps** (GUEST_MANAGEMENT_GAPS.md) — GM-01 Wake-up module, GM-02 House guest messages (both legacy-proven, additive) or GM-04 C-Form/foreign-guest report (compliance — confirm need with hotel first).
5b. **KOT token follow-ups** (KOT_NC_TOKEN_GAPS.md) — display token on KOT screen + print (printdelay schema + spooler), daily auto-reset in night audit, meal-token master + PlanMealTokens report (business decisions).
6. **Eager loading on top hot list/report pages** (BUG-025/PERF-02) — add `->with()` to highest-traffic queries; measure before/after.
7. **Missing report inventory** — complete MISSING_REPORTS.md by diffing legacy report forms vs Laravel Reporting routes; implement highest-value missing report.
8. **GST/E-Invoice verification** — confirm taxcalc/e-invoice flows match TaxStru legacy rules.

### P2
8. **Cache master data** (travel agents, revenue codes, room lists) via `Cache::remember` (PERF-03).
9. **Housekeeping COLLATE workaround cleanup** (PERF-06) — align table collations (schema change → requires approval).
10. **Stray file cleanup** — confirm + remove `resources/views/e = statename();.blade.php`.

### P3
11. **Reconcile `.ai` docs with repo reality** (BUG-028) — verify composer audit state, DB backup status (live DB is `analysis`, NOT `db_analysishms` — BUG-032), Reverb keys.
12. **Deployment guide + API docs** (BUG-019/020).
13. **CI/CD baseline** (BUG-017): composer audit + php artisan test in GitHub Actions.

---

## Rules
- Stop for human approval before: destructive migrations, data deletion, production schema changes, accounting/tax/payment rule changes, Laravel major upgrade, auth model changes, removing features, replacing legacy business rules.
- Safe code/UI/test/docs changes: proceed automatically, then verify + document.
