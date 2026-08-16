# Analysis HMS — CHANGELOG (AI SESSION)

> Only **verified** changes are recorded here. Prior `.ai/CHANGELOG.md` entries (2026-08-07) describe uncommitted/aspirational work — this file is the authoritative record from 2026-08-16 onward.

---

## 2026-08-16

### feat: KOT token sequence (legacy parity) + KOT/NC/TOKEN analysis
- **MODULE**: KOT / POS
- **FILES**: `app/Http/Controllers/Kot.php` (`submitkotentry` — per-outlet token generation), `.ai/KOT_NC_TOKEN_GAPS.md` (new)
- **CHANGE**: New KOT now takes `tokenno = depart.cur_token_no_kot + 1` (per outlet) and persists the counter — mirrors legacy `Update Depart Set CurTokenNoKOT=...` on KOT insert. Schema already had both columns; transfer/merge paths already preserved tokenno (untouched).
- **WHY**: User asked to find missing token business logic. Live DB: 3,192 KOT rows, 0 with tokenno; `cur_token_no_kot` never read/incremented — the sequence was ported in schema but never implemented in logic.
- **TEST**: php -l clean; 33 tests pass. Non-financial (order-level), zero impact on vno/docid/stock/billing.
- **RESULT**: ✅ VERIFIED
- **RISK**: LOW (additive; token unused by any existing consumer until print/spooler follow-up)
- **REMAINING (business decisions)**: token display/print (printdelay schema + external spooler), daily auto-reset hook, meal-token master (`FrmPlanTokenMast`), `PlanMealTokens` report. NC fully covered — do not duplicate.

### fix: Audit all unlogged paycharge deletions — POS + re-posting flows (BUG-037)
- **MODULE**: POS / Financial safety
- **FILES**: `app/Models/PaychargeLog.php` (new static `auditDeleted`), `app/Http/Controllers/Pos.php` (deletebillxhr + possalebillsettleupdate + possalebillsettle), `app/Http/Controllers/Pointofsale.php` (salebillsettlesubmit), `app/Http/Controllers/CompanyController.php` (chargesposting + 2 ROFF deletes), `app/Http/Controllers/CronController.php` (night-audit repost), `app/Services/AccountPosting.php` (batch repost), `.ai/POS_GAPS.md` (new)
- **CHANGE**: Every paycharge deletion now writes a paychargelog audit row (user, time, reason, amtdr+amtcr, full docid/vno/folio/ref linkage) via the shared `PaychargeLog::auditDeleted` helper. Fixes missing amtcr in deletebillxhr. Imports added where missing.
- **WHY**: User requirement "Find all direct PayCharge deletes. Ensure every financial deletion is logged." 8 sites deleted financial rows with zero audit (settlement re-posts, daily POS→folio re-posts, round-off removes).
- **TEST**: php -l clean ×6; 33 tests pass; live paycharge row verified to carry all 28 logged columns; helper handles model/object/collection input.
- **RESULT**: ✅ VERIFIED
- **RISK**: LOW (insert-only audit; no behavior change to settlement/posting math)

### fix: Housekeeping status-change audit for OOO/release/damage (BUG-036) + analysis
- **MODULE**: Housekeeping
- **FILES**: `app/Http/Controllers/HouseKeeping.php` (`savehousecleaning` O/R branches, `storeoutofororder`), `.ai/HOUSEKEEPING_GAPS.md` (new)
- **CHANGE**: (1) `savehousecleaning` type='O' now writes a `roomclean` audit row (`OOO: <reasons> [block]`); (2) type='R' branch got a null-guard on `$rblkout` (previously unguarded → 500 when no active OOO block) + audit row (`Released from OOO: <remark>`); (3) `storeoutofororder` (damage-report OOO) writes audit row (`OOO via damage report: <desc>`); (4) all audit remarks truncated to `roomclean.remarks varchar(50)` via mb_substr.
- **WHY**: User asked for audit history for status changes. Live DB confirmed only C/D rows existed; all 24 OOO blockouts (2026) and releases had zero audit trail.
- **TEST**: php -l clean; 33 tests pass; `roomclean.type varchar(1)` accepts 'O'/'R'; non-strict sql_mode confirmed (truncation guard added anyway to preserve audit fidelity).
- **RESULT**: ✅ VERIFIED
- **RISK**: LOW (audit-only additions; FO availability verified independent of `room_stat` — housekeeping can never change sellable inventory)

### feat: Front Office mismatch diagnostics (read-only)
- **MODULE**: Front Office / Reporting
- **FILES**: `app/Http/Controllers/Reporting.php` (`fodiagnostics`, `fodiagnosticsfetch`), `routes/reporting.php` (2 routes), `resources/views/property/fodiagnostics.blade.php` (tabbed Tabulator), `.ai/FRONT_OFFICE_GAPS.md`/`FRONT_OFFICE_REPORTS.md`/`FRONT_OFFICE_TESTS.md` updated with verified findings
- **CHANGE**: Read-only diagnostic page — tabs: No-Show candidates (past arrival, not cancelled, no folio), Orphan Rooms (RoomOcc w/o folio), Folios w/o Room, Folio on Cancelled Booking, Reservation-vs-Folio (departure/room/category/rate/plan/company-agent carry mismatches), Settlement Balance (checked-out folios with open balance). Bounded (LIMIT 500), no mutation.
- **WHY**: User asked to compare arrival/departure/room/category/rate/tax/plan/company/agent/source/cancellation/no-show/room-change/checkout/settlement between reservation and folio; these were previously unverified.
- **TEST**: syntax + view:cache + 27 tests pass; every tab query validated against live `analysis` DB (property 158: noshow 49, cancelledfolio 4, resvfolio ≥500, settlement 28 incl. folio 374 open ₹1,647).
- **RESULT**: ✅ VERIFIED
- **RISK**: LOW (read-only)

### feat: Safe Advance Restore / Re-post (mission §10) + Front Office analysis docs
- **MODULE**: Front Office / Financial safety
- **FILES**:
  - `app/Http/Controllers/Reporting.php` — new `advreconrestore` (guarded, transactional, audited)
  - `routes/reporting.php` — new `advreconrestore` route
  - `resources/views/property/advreconreport.blade.php` — restore button + confirmation in the detail modal
  - `.ai/FRONT_OFFICE_GAPS.md`, `.ai/FRONT_OFFICE_REPORTS.md`, `.ai/FRONT_OFFICE_TESTS.md` — new FO analysis docs
- **CHANGE**: Restore re-posts ONLY the missing difference (ResAdvance − FolioAdvance − Deleted) onto the existing folio. Guards (refuse with message): booking missing / cancelled / not checked-in / folio settled or guest checked-out / missing ≤ 0 (blocks duplicates) / no ADRES row to copy payment mode / no CHK voucher prefix / docid collision. Writes paychargelog audit inside one DB transaction. Never duplicates.
- **WHY**: User explicitly requested "Provide safe Restore/Re-post functionality. Never duplicate payment."
- **TEST**: syntax + view:cache + 27 tests pass; read-only live-DB validation: 60 sample advance-folios correctly refused (settled), exactly 1 currently in-house booking (Res 49, ₹1,300 missing) is restorable — NOT executed without user approval.
- **RESULT**: ✅ VERIFIED (feature; no live mutation performed)
- **RISK**: LOW-MEDIUM (financial operation, but guarded + audited + transaction + user-confirmed UI)

### feat+fix: Advance/Folio reconciliation report + audit-safe advance deletion (mission §9/§10)
- **MODULE**: Front Office / Financial safety / Reporting
- **FILES**:
  - `app/Http/Controllers/CompanyController.php` — `deleteadvancedeposit` now writes the deleted paycharge rows to `paychargelog` (reason, user, time, original amounts + refdocid/folionodocid linkage, original u_name/u_entdt in remarks) BEFORE deleting. Contract unchanged.
  - `app/Http/Controllers/Banquet.php` — `deleteadvancebanquet` now audits BOTH `paychargeh` rows and `ledger` postings to `paychargelog` before deleting. Contract unchanged.
  - `app/Http/Controllers/Reporting.php` — new `advreconreport`, `advreconreportfetch`, `advreconreportdetail` (read-only).
  - `routes/reporting.php` — 3 new routes (`advreconreport`, `advreconreportfetch`, `advreconreportdetail`).
  - `resources/views/property/advreconreport.blade.php` — new report view (Tabulator, date range, print/Excel, row-click trace detail modal).
- **CHANGE**: (1) Advance deletion is now auditable — never silently deleted (previously `deleteadvancedeposit` and `deleteadvancebanquet` hard-deleted financial rows with zero audit, no reason, no reconciliation check). (2) New read-only reconciliation report traces reservation advance (ADRES/ARRES via refdocid) → folio advance (transferred CHK rows, excluding REV round-offs) → deletion history (paychargelog) and flags MISMATCH / OVER-CREDIT / PENDING-TRANSFER / CANCELLED-CHECK.
- **WHY**: Mission §9 (no silent financial deletion) + §10 (advance→folio→settlement traceability + mismatch detection).
- **TEST**: `php -l` all files; `php artisan view:cache` (all blades compile); `php artisan test` → 27 passed; live-query smoke test on `analysis` DB — query runs, flags 11 real MISMATCHes in a 200-row sample (e.g., checked-in guest with ₹30,000 res advance, ₹0 folio).
- **RESULT**: ✅ VERIFIED
- **RISK**: LOW — deletion behavior unchanged (same rows deleted, same responses); report is read-only. New paychargelog rows are additive.
- **ROLLBACK**: revert the two delete methods + remove routes/methods/view.
- **NOTES**: Historical paychargelog rows for deleted ADRES advances have `amtcr=NULL` (old deletion code copied only amtdr) — deletion *amounts* unrecoverable historically, but the trail (user/time/reason) exists and the report shows it. `db_analysishms` does not exist; live DB is `analysis` (per .env).

### security: Fix stored XSS in ticket views (BUG-022)
- **MODULE**: Support Tickets / Security
- **FILES**:
  - `resources/views/tools/tickets.blade.php` — `{!! $ticket->problem !!}` → `{{ nl2br(e($ticket->problem)) }}`
  - `resources/views/admin/tools/tickets.blade.php` — same
  - `resources/views/property/mytickets.blade.php` — same
- **CHANGE**: Escape user-supplied ticket `problem` text; `nl2br` preserves line breaks from textarea input.
- **WHY**: Stored XSS — raw output of user content in 3 admin/support/user views.
- **TEST**: grep (no leftovers) + `php artisan view:cache` (549 views compile) + `php artisan test` (27 pass).
- **RESULT**: ✅ FIXED & VERIFIED
- **RISK**: LOW (display only; no business logic touched)
- **ROLLBACK**: revert the 3 blade lines.

### feat: Room Management reconciliation report (read-only)
- **MODULE**: Room Management / Reporting
- **FILES**: `app/Http/Controllers/Reporting.php` (`roomrecon` + `roomreconfetch`, 8 tabs), `routes/reporting.php` (2 routes), `resources/views/property/roomrecon.blade.php` (new)
- **CHANGE**: New read-only diagnostics page (existing design system — Tabulator, Excel, Print): orphan occupancy (RoomOcc w/o GuestFolio), Room w/o Master (RoomOcc w/o room_mast), Folio w/o Room, Occupied w/o Charges, Occupied Status (occupied room not Dirty), Blocked+Occupied (OOO w/ active occupancy), Stale Blocks (uncleared past todate), Extra Bed.
- **WHY**: Mission — verify RoomOcc ↔ GuestFolio ↔ PayCharge ↔ room_mast ↔ roomblockout consistency; legacy maintained these invariants.
- **TEST**: php -l clean; all 8 tab queries validated against live `analysis` DB (property 158: 1 room-missing-master, 3 stale blocks); `php artisan view:cache` OK; tests 33 pass.
- **RESULT**: ✅ ADDED & VERIFIED
- **RISK**: LOW (read-only)

### fix: submitroomchange leader comparison `=` → `==` + transaction safety (BUG-034)
- **MODULE**: Front Office / Room Change
- **FILES**: `app/Http/Controllers/CompanyController.php` (`submitroomchange`)
- **CHANGE**: (1) `if ($olddata->leaderyn = 'Y')` → `if ($olddata->leaderyn == 'Y')` — the assignment made the msno1 clobber unconditional on every room change; (2) wrapped the multi-table write (RoomOcc, guestfolio, roommast, Kot, PlanDetail, Paycharge) in `DB::beginTransaction`/`commit`/`rollBack`; (3) added null-guard on `$olddata` with rollback.
- **WHY**: Live `analysis` DB shows 1 corrupted folio (109CHK|2026|152: msno1=2 vs leader sno1=6) — settlement groups by msno1 for leader balances. Room change was also a non-atomic multi-table financial write (mission §18).
- **TEST**: `php -l` clean; `php artisan test` → **33 passed (39 assertions)** incl. new `tests/Feature/CheckInOutRegressionTest.php` (6 read-only invariant tests).
- **RESULT**: ✅ FIXED & VERIFIED (code path). Historical corrupt row (109CHK|2026|152) needs approval to repair.
- **RISK**: LOW — behavior identical for leader rooms; non-leader room changes no longer clobber msno1.
- **ROLLBACK**: revert `==` to `=` and remove transaction wrapper.

### tests: Check-in/Check-out regression suite
- **MODULE**: Front Office / Tests
- **FILES (new)**: `tests/Feature/CheckInOutRegressionTest.php` — 6 read-only DB-invariant tests (auto-skip when DB unavailable):
  - INV-1 BUG-033 regression: no new ADRES docid/vno collisions since fix date
  - INV-2 BUG-034 regression: CHK msno1 must match leader roomocc sno1
  - INV-3 no orphan CHK folio charges (folionodocid without roomocc)
  - INV-4 every CHK charge links to a booking row
  - INV-5 folio advance (CHK) never exceeds reservation advance (ADRES/ARRES) — no duplicate payments
  - INV-6 checked-out roomocc (type=O) must carry chkoutdate
- **WHY**: Mission §17 + user request — regression coverage for check-in/check-out; all assertions validated to pass on live data with known historical exceptions documented inline.
- **TEST**: `php artisan test --filter=CheckInOutRegressionTest` → 6 passed; full suite 33 passed (39 assertions).
- **RESULT**: ✅ ADDED & VERIFIED

### fix: Web-prepay ADRES vno off-by-one → duplicate docid (BUG-033)
- **MODULE**: Reservation / Advance Deposit / Channel
- **FILES**: `app/Http/Controllers/Api/Reservation.php` (line ~407), `app/Http/Controllers/ChannelPublic.php` (line ~433)
- **CHANGE**: `$vnop = $chkvpfp->start_srl_no;` → `$vnop = $chkvpfp->start_srl_no + 1;` in both Web/channel prepay advance paths (ADRES voucher).
- **WHY**: Confirmed on live `analysis` DB — 5 ADRES docid/vno collisions (10 rows), each pairing a Web-prepay (CRED, `PrePaidPartially`) receipt with a counter (UPI/CASH) receipt for DIFFERENT folios sharing one docid (e.g. `135ADRES…134` = folio 273 ₹5,000 + folio 277 ₹27,140). Root cause: counter flows (`Advance.php`) use `start_srl_no + 1` then `increment()`, but both Web paths used `start_srl_no` (no +1) then `increment()` → reused the counter's last vno. Verified: CHK/BookNo numbering clean (0 same-year dups; CHK multi-row docids are legitimate main+tax/multi-room lines).
- **TEST**: `php -l` clean ×2; `php artisan test` → 27 passed (33 assertions).
- **RESULT**: ✅ FIXED & VERIFIED (code path). Existing collided rows in DB are historical data — remediation requires approval (do NOT auto-rewrite financial docids).
- **RISK**: LOW — fixes future Web-prepay voucher numbering only; no existing rows/contracts touched.
- **ROLLBACK**: revert the two `+ 1` additions.

### security: Verify dynamic SQL in ToolsController (BUG-023)
- **MODULE**: Tools / Security
- **FILES**: none (verification only)
- **CHANGE**: None — confirmed `$allowedTables` whitelist, DB-introspection table/column validation, and constructor auth gate (superadmin/property-20). `whereRaw($sqlWhere)` is a by-design trusted tool.
- **RESULT**: ✅ VERIFIED SAFE
- **RISK**: If ToolsController constructor guard is removed in future, re-audit.

### fix: Add missing formatCurrency helper (BUG-027)
- **MODULE**: Helpers / Tests
- **FILES**: `app/Helpers/Helpers.php` — added `formatCurrency($amount, $currency='₹', $decimals=2)` (function_exists guard).
- **WHY**: 7 tests failed — helper documented in `.ai` but never present in code; repo state ≠ docs state.
- **TEST**: `php artisan test` → **27 passed (33 assertions)** (was 20 passed / 7 failed).
- **RESULT**: ✅ FIXED & VERIFIED — test baseline restored.
- **RISK**: LOW (pure addition; no existing call sites referenced it).
- **ROLLBACK**: remove the function block.

### P1 — Banquet analysis + audit fixes (BUG-038) + Outstanding report — 2026-08-16
- **Traced** (Laravel vs legacy HMS): Enquiry → Booking → Hall → Function → Menu/Package → Advance → Hall Charges → Discount → Tax → Round Off → Settlement → Bill. Checked hall availability (venueavailability/availablitybanquet), duplicate booking, advance (paychargeh AD), discount/round-off accounts, package/menu, billing (hallsale1), settlement (paychargeh IDC), cancellation.
- **BUG-038 FIXED**: `deleteAdvance` (newer banquet advance flow) deleted `paychargeh` rows with **zero audit and no ledger cleanup** — live banquet advances carry 2 ledger rows each (verify: earlier fix on `deleteadvancebanquet` had audited paychargeh+ledger, but the newer `deleteAdvance` path did not). Now: ledger rows removed + `paychargeh` delete audited before deletion. Same audit discipline applied to `deletebanquetbill` (hallsale1 + hallstock + hallsale2 + suntranh + ledger wiped with no log) — all deleted rows now logged to `paychargelog` (validated against live sample bill: 1 hallsale1 + 6 suntranh + 5 ledger rows).
- **Verified config**: `EnviroBanquet.roundoffac`/`discountac` are configured in `submitbanquetparameter` but only used for account-lookup filters — actual posting uses `Sundrytype.revcode`. Legacy enforced HallDiscAC/HallRoundOff; documented divergence, NOT changed (accounting rule).
- **Report comparison**: Banquet Sales Register ✅, Settlement Summary ✅, Venue Availability (day-wise) ✅, Performa Invoice ✅, Party/Company outstanding ⚠️ MISSING, Daily Function Sheet ⚠️ MISSING.
- **NEW: Banquet Outstanding report** (`banqoutstanding` + `banqoutstandingfetch` + view) — Bill vs Advance (AD sno=1) vs Settled (IDC) per hallbook, outstanding = netamt − paid, Only-Outstanding filter, Excel/Print. Model validated: property 162 all 16 bills reconcile to 0; cross-property scan surfaces ₹1.9M+ real outstanding (e.g. prop 141 ₹252,525 unpaid; prop 132 ₹305,030) + overpayment anomalies (prop 108 vno=14 paid > bill by ₹2,00,000).
- **FILES**: `app/Http/Controllers/Banquet.php`, `routes/company.php`, `resources/views/property/banqoutstanding.blade.php` (new).
- **Suite**: 33 passed (39 assertions); view:cache OK; live-query validated.

### P1 — Inventory analysis + financial-deletion audit (BUG-039) — 2026-08-16
- **Traced** (Laravel vs legacy HMS): Item/ItemCat/ItemGroup/Unit/Recipe masters, Opening Stock (FrmOPStock), Stock Transfer (FrmStockTransfer), MR entry + Purchase bills (FrmPurch: Purch1/Purch2/Sale2/Suntran/Ledger posting), Requisition slip, Stock Issue vs Requisition, Indent, Kitchen Closing Stock. Legacy FrmConsumMast covered by itemmast.Type/ItemType; FrmDeliveredItemDetail ("Not Delivered Order") partially covered by pendingkotreport.
- **BUG-039 FIXED** (HIGH, financial safety): `deletepurchbill` hard-deleted **Ledger** rows with zero audit; `purchasebillupdate` + `purchasebillsubmit` hard-deleted **Suntran + Ledger** rows unlogged before re-post. Now audited via project-convention patterns — `LedgerLogService::store()` (VoucherEntry pattern, 26 cols verified on live ledger + ledger_logs) and `Suntranlog` fill+save (SaleBill/Pointofsale pattern).
- **Verified non-financial deletes** (Stock/Indent/Gin/config — no ledger impact): mrentryupdate, deletemrentry (soft delflag), deletestocktransfer, deleteopeningstock, requisition stock issue delete, deleteindent, deleteinv, kitchen closing.
- **Reports**: stock register/movement/trade, actual/LPR valuation, purchase amount, delay delivery, receiver pending material, pending MR — all present. INV-01..03 documented (business decisions).
- **FILES**: `app/Http/Controllers/InventoryController.php` (+23 lines, 2 imports). New doc: `.ai/INVENTORY_GAPS.md`. Suite: 33 passed (39 assertions).

### PURCHASE module analysis + PO linkage fixes (BUG-040/041/042) — 2026-08-16
- **TASK**: Module 11 Purchase — PO flow (Indent → PO → MR → Bill), linkage integrity, authorization.
- **MODULE**: Purchase / Purchase Order / Inventory (MR).
- **FILES**: `app/Http/Controllers/purchaseorder/PurchaseOrderController.php` (delete guard + indent release + consumed-PO block, transaction-wrapped); `app/Http/Controllers/InventoryController.php` (deletepurchbill releases POs; mrentryupdate release-then-relink). New doc: `.ai/PURCHASE_GAPS.md`.
- **CHANGE**: (1) BUG-040 — `deletepurchaseorder` now guarded by `revokeopen(161114)` edit permission (was fully unguarded). (2) BUG-041 — PO delete releases `Indent.refdocId=''` so indents return to pending (legacy ClearYN re-open parity); consumed-PO delete blocked. (3) BUG-042 — `deletepurchbill` + `mrentryupdate` release `porder.mrcontradocId/mrsno` so POs return to pending after MR delete/deselect.
- **WHY**: Authorization gap (P0 class) + two orphan-linkage defects that permanently lock indents/POs out of the pending flow.
- **TEST**: php -l ×2 clean; view:cache compiles; 33 passed (39 assertions); live schema check (mrcontradocId/mrsno nullable) + read-only orphan scan (6 POs prop 103 dangling → flagged for approval, not mutated).
- **RESULT**: VERIFIED.
- **RISK**: LOW — additive guards + linkage releases on delete/edit only; submit/post math untouched.
- **ROLLBACK**: revert the two controller edits; re-apply if needed.

### docs: Create knowledge-base documents
- **FILES (new)**: `.ai/MASTER_PROJECT_MAP.md`, `.ai/MODULE_STATUS.md`, `.ai/BUG_REGISTER.md`, `.ai/MISSING_FEATURES.md`, `.ai/MISSING_REPORTS.md`, `.ai/MISSING_LOGIC.md`, `.ai/SECURITY_AUDIT.md`, `.ai/PERFORMANCE_AUDIT.md`, `.ai/DATABASE_MAP.md`, `.ai/ROUTE_MAP.md`, `.ai/UI_MAP.md`, `.ai/LEGACY_TO_LARAVEL_MAP.md`, `.ai/CHANGELOG_AI.md`, `.ai/NEXT_TASK.md`, `.ai/COMPLETED_TASKS.md`
- **WHY**: Mission §3/§30 — permanent engineering knowledge base, built from verified source inspection.
- **NOTES**: Prior `.ai` docs (2026-08-07) overstate repo state → BUG-028; KNOWN_BUGS.md preserved as history, BUG_REGISTER.md is canonical.
