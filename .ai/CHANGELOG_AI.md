# Analysis HMS — CHANGELOG (AI SESSION)

> Only **verified** changes are recorded here. Prior `.ai/CHANGELOG.md` entries (2026-08-07) describe uncommitted/aspirational work — this file is the authoritative record from 2026-08-16 onward.

---

## 2026-08-17
### UI Pass 2b — Housekeeping Command Center (UI + 2 real bug fixes)
- **FILES**: `app/Http/Controllers/HouseKeeping.php` (6 INNER→LEFT joins + real workload done_count), `resources/views/property/housekeeping/roomstatusboard.blade.php` (inspections, workload, modal, empty-floor).
- **CHANGE**: Completed the Command Center with **real data**: fixed `getRoomsWithStatus` + 5 assignment-report queries that INNER-joined `hkfloors` (live prop 102: 0 floors → all 15 rooms were dropped, board showed 0 rooms / 0% occupancy — **BUG-QA-014**, P1); Pending Inspections now lists real INSPECT rooms (removed fabricated rows — **BUG-QA-015**, P2); workload shows real Assigned/Done/Efficiency from `hkroomassigns.status='cleaned'`; the room modal was posted to a **non-existent PATCH route** with BS5 attributes on a BS4 runtime — now a read-only Room Details modal that opens/closes correctly.
- **TEST**: Playwright on QA — 15 rooms with real statuses (OD 2 / VD 13 / 13.33% occupancy), modal opens with real data + closes, 0 page errors, all HK screens clean. Suite **68 passed (165 assertions)**; `php -l` + `view:cache` clean.
- **RISK**: LOW — read-only query join correction + view presentation; no route/field/contract changes.
- **ROLLBACK**: revert the 6 joins + the view edits.

### UI Pass 3 — Main Setup master-screen standardization (UI-only)
- **FILES**: `resources/views/property/layouts/pageheader.blade.php` (new partial), 21 master screens (additive `@include`), `public/admin/css/hms.css` (Pass-3 section), small JS fixes in `roomcategory`/`tablemaster`/`paymaster`.
- **CHANGE**: Standard master-screen anatomy — Page Header (title + subtitle) → Form/Filter → Data Table (DataTables 2.x toolbar styled) + Actions. Applied to all Main Setup screens. Zero functional change: only an additive include after the `container-fluid` opener; nothing removed/moved.
- **ALSO FIXED** (pre-existing, surfaced by the pass): `#name`/`#namelist` autocomplete null-guards in roomcategory/tablemaster (`addEventListener` pageerrors); `new Datatable(` → `new DataTable(` typo in paymaster (the revenue-code table now actually initializes).
- **TEST**: Playwright on QA — 19/19 reachable master screens render the header with correct titles, 0 page errors; suite **68 passed (165 assertions)**; `view:cache` clean. (`usermaster` verified structurally — QA clone lacks the live `storage/app/public/menu.json` artifact.)
- **RISK**: LOW — additive partial include; no route/query/logic changes.
- **ROLLBACK**: remove the pageheader `@include` lines + delete the partial (keep the JS fixes).

### BUG-QA-013 FIX — DataTables 2.x loaded globally (functional bug fix, all modules)
- **BUG**: The shared property/tools/admin layouts never loaded the DataTables plugin while 101 views call it — 49 with the DataTables **2.x-only** `new DataTable(...)` syntax. Tables silently lacked sort/search/pagination; pages threw `$(...).DataTable is not a function` / `isDataTable` errors. The bundled asset was DataTables 1.10.18 and was never included.
- **FIX**: Vendored DataTables **2.3.2** + Buttons **3.2.0** + Responsive **3.0.3** (+ JSZip + pdfmake) into `public/admin/plugins/datatables2/` (official cdn.datatables.net sources) and loaded them in all three layout headers — CSS in `<head>`, JS after jQuery (before page scripts). Null-guarded roommaster's `#name`/`#namelist` listeners (pre-existing `addEventListener` pageerror on a non-existent element).
- **TEST**: Playwright on QA instance — roommaster/voucherentry/advancelist/checkinlist/bookingsource show full 2.x features (search/info/paging); export buttons render (voucherentry 3, bookingsource 4); **0 DataTables/page errors**; mobile OK; admin layout loads globals. Full suite **68 passed (165 assertions)**; `view:cache` clean.
- **RISK**: LOW — additive asset + include; no query/route/logic changes. The unused 1.10.18 bundle was left untouched.
- **ROLLBACK**: remove the datatables2 includes from the three layout headers + delete `public/admin/plugins/datatables2/`.

### UI Pass 2a — Login page redesign (navy/teal brand, UI-only)
- **FILES**: `resources/views/auth/login.blade.php` (redesigned), `resources/views/frontend/layouts/header.blade.php` (additive body-class hook)
- **CHANGE**: Replaced the plain unstyled Bootstrap card with a branded full-screen navy gradient login: white card, navy brand band (logo + title + tagline), icon input groups, navy login button, remember/forgot row, demo-request panel, copyright. Marketing topbar/navbar/footer hidden on the login gateway only (scoped by a new `body-class` yield; empty class elsewhere).
- **WHY**: Login is the brand entry point; reference package login.png shows a clean branded screen. Zero functionality change — field names/ids (`u_name`, `password`, `propertyid`, `remember`), `@csrf`, action `route('login')`, error blocks, digit sanitizer, localStorage persistence all byte-for-byte.
- **TEST**: Playwright on QA instance — brand styles applied, chrome hidden on login only, 5/5 fields unchanged, login ADMIN/qa123/102 → `/company` works (desktop + mobile 390), homepage unaffected, 0 console errors. Suite 68 passed (165 assertions); `php -l` + `view:cache` clean.
- **RISK**: LOW — two views, additive hook, scoped CSS.
- **ROLLBACK**: revert the two view files.

### UI Pass 1 — Bootstrap-5-style global design system (UI-only)
- **MODULE**: All property screens (presentation only — zero functional change)
- **FILES**: `public/admin/css/hms.css` (new, ~380 lines), `resources/views/property/layouts/header.blade.php` (1 link added), docs: `UI_REFERENCE_SCREEN_MAP.md`, `UI_REFERENCE_LARAVEL_MAP.md`, `UI_DESIGN_SYSTEM.md`, `UI_PROGRESS.md`, `UI_CHANGED_FILES.md`, `UI_REGRESSION.md`, `UI_NEXT_TASK.md` (all new)
- **CHANGE**: Studied the reference package (AnalysisHMS_Manual — 135 screenshots/15 modules) and mapped every reference screen to its Laravel route/blade. Built a Bootstrap-5-style design-system layer over the Ekka BS4.1.3 theme: navy/teal tokens, navy sidebar with light menu + accent active states, white topbar, borderless rounded cards, modern tables (uppercase sticky-ready headers, hover), navy primary buttons, refined forms/modals/dropdowns, responsive + print rules.
- **WHY**: Modernize the UI to Bootstrap-5 visual language with **zero functionality change** (framework swap is blocked: BS4-era plugins + `data-toggle` in hundreds of views would break).
- **TEST**: Playwright before/after on dedicated QA instance (prop 102, :8001, zero production writes): sidebar white→navy, buttons purple→navy, cards/tables modernized; mobile off-canvas + dynamic sidebar menu + topbar nav verified working. Full suite **68 passed (165 assertions)**; `php -l` + `view:cache` clean.
- **RISK**: LOW — one CSS link + one stylesheet; no route/query/JS/field changes.
- **ROLLBACK**: remove the hms.css `<link>` from header + delete the file.


### fix: advance-delete audit — availableRooms closure 500 (BUG-QA-011) + deleteguestledger audit completeness (BUG-QA-012)
- **MODULE**: 7 Reservation / 10 Folio-Ledger / 22 Reporting
- **FILES**: `app/Http/Controllers/CompanyController.php` (`openupdatereservation`, `openupdatewalkin`, `deleteguestledger`), `tests/Feature/AdvanceDeleteAuditTest.php` (new, 8 tests / 19 assertions)
- **BUG-QA-011 (P1, page broken)**: `MasterDataCache::availableRooms` closures in `openupdatereservation`/`openupdatewalkin` referenced the foreach variable `$row` without capturing it — on cache miss the reservation-edit and in-house-edit pages 500'd with `Undefined variable $row`. Fix: capture `$roomCat` scalar into the closure. Found via Phase-4 browser walkthrough on a dedicated QA DB; re-verified the page renders + advance-delete button works.
- **BUG-QA-012 (P2, audit completeness)**: `deleteguestledger` wrote its `paychargelog` audit row without `refdocid`/`amtcr`/`paytype`, so deleted advances were invisible to the Advance/Folio Reconciliation report's DelAmount (466 live ADRES deletion rows unlinkable); log fetch was vno-only (could log a different vtype) and not atomic. Fix: copy full linkage, scope fetch by vno+vtype, wrap in a transaction.
- **Advance-delete chain verified SOUND end-to-end** (deleteadvancedeposit → paychargelog → `advreconreportfetch` DelAmount): QA fixture deleted ₹2000 → DelAmount=2000, Recon=0, no double counting.
- **QA infra**: `analysis_qa` DB (schema + property-102) + second instance on :8001 with explicit env overrides (shell-exported `DB_DATABASE=analysis` + immutable Env repo made `.env.qa` alone ineffective — first boot hit live DB, killed).
- Suite: **68 passed (165 assertions)**.


### fix: Phase-12 report reconcile — DayBook/JournalBook/CashBankBook/GeneralLedger no longer drop orphan-subcode postings (BUG-QA-010)
- **MODULE**: 12 Accounts / 22 Reporting
- **FILES**: `app/Http/Controllers/Finance/FinanceController.php` (`dayBookRows`, `cashBankBookRows`, `generalLedgerQuery`, `printGeneralLedger`), `app/Exports/DayBookExport.php`, `JournalBookExport.php`, `CashBankBookExport.php`, `GeneralLedgerExport.php`, `tests/Feature/ReportReconcileTest.php` (new, 3 tests / 20 assertions)
- **BUG-QA-010 (P1, report accuracy)**: the four ledger-composition queries INNER-joined `subgroup`, but the legacy query was `VIEWLEDGER LEFT JOIN SUBGROUP`. Ledger postings with an empty/missing subcode (HPOST advance legs when the property's `roomchrgdueac` account is unconfigured) were silently dropped — **683 rows / ₹7,024,242.83 dr across 41/72 properties in 2026** (prop 149: ₹4.11M). Day/Journal Book totals understated the ledger and showed `dr != cr`. All sites now `leftJoin('subgroup')` — verified **0/72 report-vs-raw mismatches** after the fix; Cash/Bank per-account identity holds.
- **Data notes (not defects)**: PBPC/PBPB purchase vouchers are dr-only in `ledger` on some properties (payable half elsewhere) — flagged for accounts review; TDS report's subgroup join is a party-name lookup, intentionally unchanged.
- **TEST**: suite **61 passed (146 assertions)** — up from 58/126. Docs: AI_QA_BUGS (QA-010), AI_QA_PROGRESS (session 3).

### fix: Phase-6 critical workflow — post-check-in advances now folio-linked (BUG-QA-008/009)
- **MODULE**: 07 Reservation / 08 Advance / 10 Folio
- **FILES**: `app/Http/Controllers/Reservation/Advance.php` (`submitadvdeposit`), `app/Http/Controllers/CompanyController.php` (`deleteadvancedeposit`), `tests/Feature/AdvanceFolioLinkageTest.php` (new, 4 tests / 11 assertions), `.ai/AI_QA_BUGS.md` + `.ai/AI_QA_PROGRESS.md` (session 2)
- **BUG-QA-008 (P1, financial)**: `submitadvdeposit` inserted ADRES advances with an empty `folionodocid` even when the reservation was already checked in (`ContraDocId` set) — the money never reached the guest's folio; the Advance/Folio reconcile report flagged permanent MISMATCH (564/727 checked-in 2026 bookings) and staff compensated with manual ACCOUNT-TRANSFER RECs the report cannot link. Now: in-house bookings get `folionodocid = ContraDocId` + `foliono = guestfolio.folio_no` on the main AND tax rows (mirrors submitwalkin's check-in copy). Pre-check-in behavior unchanged.
- **BUG-QA-009 (P2)**: `deleteadvancedeposit` (paychargelog audit + Paycharge delete) now transactional.
- **Phase-6 audit method**: traced reservation→advance→check-in→folio→settlement (all transactional ✓); replicated the app's own `advreconreportfetch` logic read-only on live data — standard path reconciles (prop 174 7/7, prop 158 288 copies), residual flags root-caused.
- **TEST**: suite **58 passed (126 assertions)** — up from 54/115. Docs: AI_QA_BUGS (QA-008/009 + audit summary), AI_QA_PROGRESS (session 2).

### fix: Master QA pass — 6 Banquet financial atomicity defects + HolidayController auth gap (BUG-QA-001..007)
- **MODULE**: 14 Banquet, 23 Admin/Holiday
- **FILES**: `app/Http/Controllers/Banquet.php` (6 methods), `app/Http/Controllers/HolidayController.php` (constructor guard), `tests/Feature/BanquetHolidayQATest.php` (new, 7 tests / 25 assertions), `.ai/AI_QA_BUGS.md` + `.ai/AI_QA_PROGRESS.md` (new)
- **Banquet (P1, financial atomicity)**: `performaInvoiceSubmit` had `DB::beginTransaction()` **commented out** with an orphan active `DB::commit()` (re-enabled + rollbacks on 2 early returns + catch); `deletebanquetbill` (6-table delete), `deletePerformaInvoice` (5-table delete; catch also **masked failures as `'success' => true`** → now false), `deleteadvancebanquet` (paychargelog + PaychargeH/Ledger deletes), `deletebanquet` (inquiry + HallBook/VenueOcc), `banquetbillsubmit` (settlement delete+reinsert) — all wrapped in transactions with commit-on-success/rollback-on-error.
- **HolidayController (P1, security)**: zero auth guard (no middleware, no constructor) — `GET /holiday/data` returned all rows unauthenticated (verified). Added the sibling-pattern constructor auth middleware; `/holiday/data` and `/holidaymaster` now 302→login.
- **Swept all 27 write-bearing controllers** (brace-aware parser): CompanyController/Inventory/POS/HK/Kot/Cron/AccountPosting/VoucherEntry already transactional; MainController admin routes verified guarded at controller level (302 empirical; defense-in-depth route middleware noted, LOW); orphaned legacy `holiday` table documented (no reads).
- **TEST**: 7 new QA tests pass; full suite **54 passed (115 assertions)** — up from 47/90.
- **RISK**: LOW — additive atomicity + one auth middleware matching every sibling controller; no business-rule change.

### fix: Housekeeping module testing pass — BUG-045 (housemaster blocked) + 17 unguarded write paths + validation-catch crash
- **MODULE**: 13 Housekeeping
- **FILES**: `app/Http/Controllers/HouseKeeping.php`, `tests/Feature/HouseKeepingModuleTest.php` (new)
- **BUG-045 (HIGH)**: `housemaster`/`submithousemaster`/`updatehousemaster`/`deletehousekeepingmaster` guarded with `revokeopen(121512)` — a legacy duplicate code present on only 21 props (0 rows on prop 135); the canonical code is **151112** (41 props). Every prop-135 user was blocked from Housekeeper Master CRUD. Fixed: `revokeopen(151112) ?? revokeopen(121512)` (4 user-pairs have only the legacy code — fallback preserves them). Verified: 0 props have 121512 without 151112.
- **Permission guards added (17 write paths)**: `savehousecleaning` (151111), lostfound store/update (151117), laundry send/receive store/update (151414/151415), cleaning-type CRUD (121513), hk-supervisor CRUD (121514), floor-master CRUD (121511), `saveAssignmentReport`/`unassignRooms` (151113 ?? 151114), `submitstartcleaning` (151114 ?? 151115), `submitcleaningentry` (151115 ?? 151112), `storedamagereport`/`storeoutofororder` (151118), `updatedamagereport` (151216), `submitinspection` (151116). Dual-code `??` fallbacks where menuhelp maps one route to different codes across properties (startcleaning 151114/151115, roomcleaningentry 151112/151115, assignments 151113/151114) so no property's users are over-blocked. All codes verified against live menuhelp route→code map.
- **Transactions added (5 methods)**: `updatehousemaster` + `deletehousekeepingmaster` (housekeeparmast + employee sync), `updatehksupervisor` + `deletehksupervisor` (hksupervisor + employee sync), `storeoutofororder` (close old blockout + insert new + room_mast + roomclean audit).
- **Validation-catch crash (BUG-046)**: `storedamagereport`/`updatedamagereport`/`storeoutofororder` did `implode(' ', $ve->errors())` where `errors()` is array-of-arrays → **"Array to string conversion" fatal** on any validation failure. Fixed with `Arr::flatten` (matching `submitinspection`). Caught by the new authorized-user regression test.
- **Cleanup**: renamed emoji-named variable `$jaldiwahasehato📢` → `$deleted` in `deletehousekeepingmaster`; removed duplicated `$scode` query in `submithousemaster`.
- **TEST**: new `HouseKeepingModuleTest` (6 tests, 9 assertions): BUG-045 regression (151112-granted user without 121512 must pass), savehousecleaning/storelostfound deny without permission, housekeepingscreen loads for authorized user, storeoutofororder allows authorized user, damage validation failure returns 422. Suite: 47 passed (90 assertions).
- **RISK**: LOW — guards mirror menuhelp visibility (menu is driven by the same codes); dual-code fallbacks prevent over-blocking; read-only tests.
- **ROLLBACK**: revert guard/transaction edits in HouseKeeping.php; delete the test file.

### fix: Transaction-safety audit — re-enabled commented-out AccountPosting transaction (P0) + 8 untransactioned write paths (ML-04/06/07)
- **MODULE**: 8 POS / 21 Purchase / 23 Accounts / 12 Finance
- **FILES**: `app/Services/AccountPosting.php`, `app/Http/Controllers/Pos.php`, `app/Http/Controllers/Pointofsale.php`, `app/Http/Controllers/InventoryController.php`
- **CHANGE**: Audited ML-02..07 transactional integrity. **P0**: `AccountPosting::accountpoststore` (Daily POS→Folio re-posting: deletes PPOS/IPOS paycharge + HPOST ledger per date, then re-posts) had `DB::beginTransaction()`/`commit()` **commented out** — re-enabled; rollback added on early env-check return. **ML-04**: `Pointofsale::salebillupdate` (rewrites Sale2/Stock/Suntran/Sale1), `Pos::possalebillsettle` + `Pointofsale::salebillsettlesubmit` (delete+reinsert settlement paycharge — tx lost in an earlier rewrite), `nillsettle` (paycharge+roomocc) — all now transaction-wrapped with rollback+Log::error on failure. **ML-06**: `mrentrysubmit` (Gin+Stock+PO-consumption+vno), `openingstocksubmit`, `requisitionstocksubmit`/`requisitionstockupsubmit` (2 stock sets+Indent clear+2 vnos), `requisitionstockisuedelete` — all transaction-wrapped. ML-05 verified (mergeroompost/reverse transactional, no deletes); VoucherEntry Dr=Cr balance checks verified.
- **WHY**: A mid-run failure in any of these leaves financial/stock rows half-written (e.g. settlement paycharge deleted but not re-inserted; a day of PPOS/IPOS/HPOST postings partially deleted).
- **TEST**: php -l clean on all 4 files; structural check (1 begin + 1 commit + ≥1 rollback, no bare returns inside tx) on all 9 methods; suite 41 passed (81 assertions).
- **RISK**: LOW — adds atomicity only; commit points identical to the success returns; no business-rule change.
- **ROLLBACK**: remove the try/beginTransaction blocks (restore `// DB::beginTransaction()` comment in AccountPosting).

### test: walkin + reservation + FOM-charge master-data cache regression (PERF-03)
- **MODULE**: 14 Front Office / 12 Accounts
- **FILES**: `tests/Feature/PerformanceEagerLoadTest.php` (+`test_walkin_page_master_data_stays_cached`, +`test_reservation_page_master_data_stays_cached`, +`test_fom_charge_list_stays_cached`)
- **CHANGE**: Three regression tests asserting the hot booking/master pages serve master data from `MasterDataCache` across repeated loads: (1) walkin (`openwalkin`, perm 141112) — cold must query `subgroup` ≥1, warm must issue **0** subgroup queries, flush → re-query; (2) reservation (`openreservations`, perm 131111) — asserts **both** `subgroup` (agents+corporates) AND `room_mast` (rooms) go to 0 on warm, covering the `rooms()` key; (3) FOM charge list (`openplanaster`, perm 121215) — asserts **`revmast`** (FOM charges via `fomCharges()` key) goes to 0 on warm. Fixtures are dynamic (property with permission row + master-data rows + matching user for `revokeopen()`/`Auth`); read-only, skips when DB unreachable.
- **TEST**: suite 41 passed (81 assertions); real-controller cold/warm profiles measured first: walkin subgroup 2→0, reservation subgroup 2→0 + room_mast 1→0, plan-master revmast 1→0.
- **RISK**: LOW — test-only change.

### perf: remaining loop batching — Banquet/POS/focc (PERF-02 tail)
- **MODULE**: 19 Banquet / 17 POS / 14 Reporting
- **FILES**: `app/Http/Controllers/Pos.php` (possalebillsettle mergedBills), `app/Http/Controllers/Banquet.php` (advancebanquetsubmit + editAdvanceSubmit tax-name lookups), `app/Http/Controllers/Reporting.php` (focc_reportfetch depart lookups)
- **CHANGE**: The Banquet & POS hot LIST pages were audited and are already join-based single queries (displaytable, saleregfetch, advancelistData, banqoutstandingfetch, etc.) — the remaining per-row loops were in write/print paths. Batched: (1) `possalebillsettle` merged-bill collection — 2 queries per merged bill (Sale1 + first non-zero Paycharge) → one grouped `whereIn` fetch per table; (2) Banquet tax posting — 1 revmast name lookup per tax row → one batched fetch with **first-row-wins** map ordered by Desk_code (rev_code is NOT unique per property — MT10310 = 'BA - NOTAX' vs 'STR - Hall Rent'; reproduces original `value()` exactly); (3) `focc_reportfetch` — 1 Depart lookup per non-FOM payment row → one batched `pluck('name','dcode')`.
- **PARITY**: live-DB verified — Pos 2 merged bills / 0 mismatches (ordered by sno to match paycharge PK (propertyid, docid, sno, sno1) un-ordered `first()`); Banquet 2 tax codes / 0 mismatches; focc 8 depart codes / 0 mismatches (dcode unique per property — verified).
- **TEST**: php -l clean ×3; suite 38 passed (58 assertions).
- **RISK**: LOW — pure query restructuring in write/print paths, output-identical; no schema/route/contract changes.
- **ROLLBACK**: revert the three controller edits (git checkout).

### perf: per-date room availability caching (PERF-03 follow-up)
- **MODULE**: 14 Front Office / Room pickers
- **FILES**: `app/Helpers/MasterDataCache.php` (+`availableRooms`, `flushAvailability`, version key), `app/Http/Controllers/RoomController.php` (getRoomswalkin, getRooms), `app/Http/Controllers/CompanyController.php` (openupdatewalkin/openupdatereservation per-row room pickers, flush on walkin submit/update/delete + reservation delete), `app/Http/Controllers/Reservation/UpdateReservation.php`, `Api/Reservation.php`, `Frontend/HotelBookingController.php`, `ChannelPublic.php` (booking writes + flush), `HouseKeeping.php` (OOO blockout create/clear + flush), `Pointofsale.php` (checkout + flush), `tests/Feature/PerformanceEagerLoadTest.php` (+cache regression test)
- **CHANGE**: The walkin/reservation pages fire per-date room-availability queries on every load (walkin page posts `/getroomswalkin` per room row; each runs a 3-subquery NOT-IN check over roomocc + grpbookingdetails + roomblockout). Cached via `MasterDataCache::availableRooms(property, variant, roomcat, checkin, checkout, closure)` — **version-keyed**: `flushAvailability()` bumps a per-property version counter, making every previously-cached availability key unreachable in one cheap cache write (no key enumeration). TTL 300s is a safety net only; correctness comes from flush on writes.
- **INVALIDATION (17 write paths)**: walkin submit/update/room-change/delete, reservation update (UpdateReservation), reservation delete, API booking (Api/Reservation), frontend self-booking (HotelBookingController), channel-manager booking (ChannelPublic), HouseKeeping OOO blockout create + clear + damage-report flow, Pointofsale checkout, **night audit (`submitnightaudit` — updates roomocc.depdate + cancels no-show grpbookingdetails), room-move settlement (`submitRoomSettle` + `Frontend/RoomSettlement`), ToolsController `deletedate` bulk purge (both CM/without-CM branches), RoomController merge/reverse-merge (`mergeroompost`/`mergereverseroompost`)**. Left uncached: reservation-submit auto-fill/empty-room validation (must see fresh availability) and `mergefolio`/checkout-time `leaderyn` toggles with no room/date change (no availability impact). Automated brace-aware sweep of every controller confirms **0 write methods lack flush**.
- **MEASURED** (live prop 135): getRoomswalkin **1 query cold → 0 warm**, HTML byte-identical; 15-room category parity IDENTICAL vs raw query; flush → 1 query again. Suite: 38 passed (58 assertions).
- **RISK**: LOW-MED — cached availability is bounded by 300s TTL even if a write path is missed; flush covers **all** identified booking/blockout paths — a brace-aware sweep across `app/Http/Controllers` (incl. CronController, which has no availability-table writes — night audit only posts paycharge) shows **0 un-flushed write methods**.
- **ROLLBACK**: revert helper + RoomController/CompanyController/Reservation/Channel/HouseKeeping/Pointofsale edits (git checkout).

### perf: master-data caching (PERF-03)
- **MODULE**: 14 Front Office / 12 Accounts
- **FILES**: `app/Helpers/MasterDataCache.php` (new), `app/Http/Controllers/CompanyController.php` (17+ read sites wired, 23 write paths + flush), `app/Http/Controllers/Reporting.php` (2 read sites)
- **CHANGE**: Added `MasterDataCache` — `Cache::remember` wrappers for the 5 hottest master lists (travelAgents, corporates, companiesAndAgents, rooms, fomCharges; 24h TTL safety net). Wired into walkin / walkinprefilled / reservation ×2 / openreservation / FOM chargemaster+department / advance-options / roomresettlement pages + Reporting fetchcompname/fetchcompany. `flush()` invalidates all 5 keys per property on every subgroup/revmast/room_mast write path — verified 0 un-flushed write sites via automated sweep (caught 4 initially-missed paths: FOM submitchargemaster insert, updatechargemasterstore, deletechargemaster, subgroup company/agent update).
- **MEASURED** (live prop 135, walkin page): 15 queries / 63.6ms cold → 13 queries / 19ms warm; all cache keys match raw DB exactly (57 combined company/agent rows, names identical).
- **TEST**: php -l clean ×3; suite 37 passed (53 assertions); cache round-trip verified (1 query → 0 → 1 after flush).
- **RISK**: LOW — read-only caching of static master lists; per-date room availability deliberately NOT cached; flush covers all writes; verified MemberMaster (comp_type='member'), PrintController (BANQ desk), HouseKeeping (room_stat) writes don't affect cached keys.
- **ROLLBACK**: remove helper + revert read-site/write-site edits (git checkout).

### feat: Journal Book report (legacy JournalBook parity)
- **MODULE**: 12 Accounts / Finance
- **FILES**: `routes/company.php` (5 routes), `app/Http/Controllers/Finance/FinanceController.php` (+5 methods: journalBook, journalBookVtypes, journalBookQuery, printJournalBook, exportJournalBook — reuse shared `dayBookRows()`), `app/Exports/JournalBookExport.php` (new), `resources/views/property/finance/journalbook.blade.php` (new), `resources/views/property/print/printjournalbook.blade.php` (new)
- **CHANGE**: Added legacy `JournalBook` — ledger postings for a voucher type in a date range (vdate/vtype/vno/docid/account/narration/dr/cr), vtype dropdown defaulting to `JV` (Journal), PDF print, Excel export. Mirrors legacy `Proc_203_70_14FE4CC` (`VIEWLEDGER LEFT JOIN SUBGROUP ... WHERE V_date BETWEEN ... AND V_TYPE='<type>' ORDER BY V_DATE,V_TYPE,V_NO,V_ADD,V_SNO`). Reuses Trail Balance permission 111211 + BUG-044-scoped join (via dayBookRows). Read-only.
- **TEST**: php -l clean; view:cache compiles; 5 routes registered; suite 37 passed (53 assertions); live-DB prop 169 Apr-2026 — JV 332 rows Dr=Cr=₹1,015,580.20 exact (matches Day Book JV filter), PMT 174 rows Dr=Cr=₹9,466,537.15 exact; Excel export + PDF render smoke-tested.
- **RISK**: LOW — read-only report; no writes; no route/field/contract changes.
- **ROLLBACK**: remove 5 routes + 5 methods + 2 views + export class.

### perf: batched per-row lookups on hot report paths (PERF-02)
- **MODULE**: 16 Night Audit / 14 Front Office API / 14 Reporting
- **FILES**: `app/Http/Controllers/NightAudit/Reports/DailyReport.php` (loops → `whereIn` batches), `app/Http/Controllers/Api/InhouseRoomGet.php` (per-booking advance → grouped fetch), `app/Http/Controllers/Reporting.php` (lookuproomtypefetch, roominventoryfetch, getindex), `tests/Feature/PerformanceEagerLoadTest.php` (regression tests)
- **CHANGE**: Night Audit Daily Report — replaced ~100 per-row aggregate queries with grouped `whereIn` lookups (224 → 66 queries, 14.2s → 7.4s on live prop 135). In-house reserved rooms — per-booking advance batched into one grouped `Paycharge` fetch (880 → 5, 1.7s → 0.04s). Room-type availability `lookuproomtypefetch` — per-category×per-day busy-room queries → 2 bulk window fetches + in-memory date-overlap counting (310 → 4, 1.17s → 0.04s). Room inventory `roominventoryfetch` — per-room balance/advance → one grouped Paycharge aggregate keyed by (folionodocid, sno1) (110 → 3). Dashboard `getindex` — today/yesterday memo-voucher sums batched.
- **PARITY**: BEFORE/AFTER JSON diff on live data — all paths byte-identical (Daily Report 0 diffs; lookuproomtype 154 daily values / 0 mismatches; roominventory 54 rooms / 0 mismatches; getindex totals match; matched original `null` vs `0` defaults exactly).
- **TEST**: php -l clean; read-only live-DB regression tests assert query-count bounds on 4 paths (Daily Report ≤120, reservedrooms ≤50, lookuproomtype ≤20, roominventory ≤20); suite 37 passed (53 assertions).
- **RISK**: LOW — pure query restructuring, output-identical; no schema/route/contract changes. (Write-path `Fetch::postchargesone` per-row queries left untouched — out of scope, risky.)
- **ROLLBACK**: revert the three controller files (git checkout).

---

## 2026-08-16

### feat: Cash Book / Bank Book reports (legacy CashBook/BankBook parity)
- **MODULE**: 12 Accounts / Finance
- **FILES**: `routes/company.php` (5 routes), `app/Http/Controllers/Finance/FinanceController.php` (+5 methods: cashBankBook, cashBankBookAccounts, cashBankBookQuery, printCashBankBook, exportCashBankBook + shared `cashBankBookRows()`), `app/Exports/CashBankBookExport.php` (new), `resources/views/property/finance/cashbankbook.blade.php` (new), `resources/views/property/print/printcashbankbook.blade.php` (new)
- **CHANGE**: Added legacy `CashBook`/`BankBook` reports — ledger filtered to accounts whose `acgroup.nature` is 'Cash' (CASH-IN-HAND) or 'Bank' (BANK ACCOUNTS/BANK OD-AC), per-account opening/running/closing balance, book toggle + optional account filter, PDF print, Excel export. Uses the BUG-044-scoped join; canonical nature from acgroup (denormalized `ledger.groupnature` is stale for 372 rows on prop 169). Reuses Trail Balance permission 111211.
- **TEST**: php -l clean; view:cache compiles; 5 routes registered; suite 33 passed (39 assertions); live-DB prop 169 Apr-2026 — Cash 1 acct (CASH IN HAND ₹158,802→₹80,138), Bank 3 accts (CREDIT CARD A/C, HDFC, UPI), 0 identity mismatches, controller output == export output.
- **RISK**: LOW — read-only report; no writes; no route/field/contract changes.
- **ROLLBACK**: remove 5 routes + 5 methods + 2 views + export class.

### feat: Day Book report (legacy DayBook parity) + BUG-044 acgroup join fix
- **MODULE**: 12 Accounts / Finance
- **FILES**: `routes/company.php` (5 routes), `app/Http/Controllers/Finance/FinanceController.php` (+5 methods: dayBook, dayBookVtypes, dayBookQuery, printDayBook, exportDayBook + shared `dayBookRows()`), `app/Exports/DayBookExport.php` (new), `resources/views/property/finance/daybook.blade.php` (new), `resources/views/property/print/printdaybook.blade.php` (new)
- **CHANGE**: Added the legacy `DayBook` report — chronological register of ALL ledger postings in a date range (vdate/vtype/vno/docid/account/narration/dr/cr), optional vtype filter, PDF print, Excel export. Reuses Trail Balance permission 111211 + finance report view family.
- **BUG-044 FIXED**: discovered via Dr/Cr parity validation that `acgroup.group_code` is not globally unique (shared across properties) → the unscoped `leftJoin('acgroup')` multiplied report rows ~5%. Scoped all 12 join sites (GL query/print/accounts, DTL query/print, DayBook, 3 export classes) with `a.propertyid` match. GL + DTL totals corrected too.
- **TEST**: php -l clean ×4; view:cache compiles; 5 routes registered; suite 33 passed (39 assertions); live-DB prop 169 Apr-2026: JV filter Dr=Cr exact (332 rows ₹1,015,580.20), ALL 2,822 rows, GL identity 104 accts / 0 mismatches, GL total = Day Book total (₹20,851,979.69).
- **RISK**: LOW — read-only report + join-scoping fix; no writes; no route/field/contract changes.
- **ROLLBACK**: remove 5 routes + 5 methods + 2 views + export class; revert join scoping.

### chore: Legacy-only module verification (8 forms) — classification, read-only
- **MODULE**: Cross-module (MODULE_STATUS ⚠️ list)
- **SCAN**: routes + controllers + views + live DB (SHOW TABLES) for FrmLostFound, FrmDenomination, FrmForExRec/FrmForeignExMast, FrmMeterReading, FrmGuestWakeUp, FrmPaxDetails, FrmUnSettledBillsInfo, FrmHotKey.
- **RESULT**: Lost&Found EXISTS (HouseKeeping lostfound); Denomination/ForEx/MeterReading MISSING; PaxDetails/HotKey OBSOLETE (not standalone; superseded); UnSettledBillsInfo REPLACED(partial); WakeUp = GM-01 already tracked.
- **WHY**: queue item P0 #5 — classify before building; no code changed.
- **TEST**: suite 33 passed (unchanged).

### feat: Bulk Tools deletion audit (BUG-043) — no silent financial deletes in admin tools
- **MODULE**: Tools / Finance safety (mission §9)
- **FILES**: `app/Http/Controllers/Tools/ToolsController.php` (+`auditFinancialDeletion()` helper; `deletedate`, `deletetablerecord`, `deletemultiplerecords`, `resetOutletData`)
- **CHANGE**: (1) `deletedate` (Data Empty Tool) audit was **dead code** — unreachable after both branches `return`, so a full property wipe (42 tables incl. paycharge/ledger/suntran/kot/sale1/2) left **zero audit trail**. Now writes a `userupdate` audit row with pre-wipe per-table row counts BEFORE deleting, inside the same transaction. (2) `deletetablerecord`/`deletemultiplerecords` (Table Management) and `resetOutletData` (POS Recycle) now audit paycharge → `PaychargeLog::auditDeleted`, ledger → `LedgerLogService::store`, suntran → `Suntranlog` copies before delete (BUG-030/037/039 patterns).
- **WHY**: ML-08 — remaining silent financial deletes in admin bulk tools.
- **TEST**: php -l clean; suite 33 passed (39 assertions).
- **RISK**: LOW — audit-only writes before existing deletes; no route/field/contract changes.
- **ROLLBACK**: remove audit calls + helper.

### feat: General Ledger report (legacy Led parity) + Accounts analysis
- **MODULE**: 12 Accounts / Finance
- **FILES**: `routes/company.php` (5 routes), `app/Http/Controllers/Finance/FinanceController.php` (+6 methods: generalLedger, generalLedgerAccounts, generalLedgerQuery, printGeneralLedger, exportGeneralLedger), `app/Exports/GeneralLedgerExport.php` (new), `resources/views/property/finance/generalledger.blade.php` (new), `resources/views/property/print/printgeneralledger.blade.php` (new), `.ai/ACCOUNTS_GAPS.md` (new)
- **CHANGE**: Added the legacy `Led` general-ledger report (transaction-level per account with opening/running/closing balance) — Laravel only had the summary Detailed Trial Ledger. Includes optional account filter, PDF print, Excel export. Reuses Trail Balance permission (111211) + finance report view family.
- **WHY**: Report parity — legacy HMS had Led/LedDeb/LedCred/LedInt/DayBook/CashBook/BankBook/JournalBook/Aging/DueList; only DetailedTrial existed in Laravel.
- **TEST**: php -l clean; view:cache compiles; 5 routes registered; suite 33 passed (39 assertions); live-DB validation property 169 — 216 account identities (opening+trans=closing) OK, 67 running-balance recomputations OK, 0 mismatches.
- **RISK**: LOW — read-only report, no writes; no route/field/contract changes.
- **ROLLBACK**: remove the 5 routes + 6 methods + 2 views + export class.

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
### UI Pass 3b — 11 update/edit page headers + 4 autocomplete null-guards
- **FILES**: 11 update blades (updateroommaster, updateplanmaster, updatechargemaster, updateroomcategory, updatepartymaster, updatedepartmaster, updateroomfeature, updatecompanymaster, updatetaxform, updatetaxstructure, updaterecipemaster) + 4 null-guards (updateroommaster, updateroomcategory, updatechargemaster, updateroomfeature)
- **CHANGE**: Standard "Edit X" page headers on all edit pages; fixed `#name`/`#namelist` autocomplete null-listener pageerrors (addEventListener on missing element) — same family as the Pass-3 list-page fixes
- **TEST**: Playwright on QA — all 4 reachable edit routes render headers with 0 page errors; suite 68 passed (165 assertions); view:cache clean
### Standards compliance audit + rule 10.6 fix (paychargelog service)
- **FILES**: `app/Services/PayChargeLogService.php` (new), Banquet.php (5 sites), CompanyController.php (2), Reporting.php (1) — all inline `DB::table('paychargelog')->insert()` centralized into the service
- **CHANGE**: HMS rule 10.6 — log-table writes must go through a dedicated service class. Pure insert passthrough (same table/data/transaction), mirrors LedgerLogService precedent.
- **AUDIT**: documented in `.ai/STANDARDS_COMPLIANCE.md` — Eloquent-first (192+ legacy insert sites) and constructor gaps (34/119, incl. 2 public QR-ordering controllers) documented as legacy deviations, NOT refactored (would break functionality / rewrite financial logic).
- **TEST**: php -l clean; suite 68 passed (165 assertions).
### UI Pass 4 — Complete BLUE UI transformation (#0d6efd)
- **FILES**: `public/admin/css/hms.css` (token recolor: navy/teal → Bootstrap blue #0d6efd family), `resources/views/auth/login.blade.php` (blue gradient), `resources/views/property/dashboardcss.blade.php` (63 purple/indigo/fuchsia hexes → blue family, semantic green/amber/red preserved), `public/admin/css/style.css` (95× #7571f9 → #0d6efd + purple label classes), 9 blades with #667eea/#764ba2 gradients (tickets, footer, recon reports, salebillentry, tablemanagement)
- **CHANGE**: Whole-app blue identity — sidebar #0b5ed7→#084298 gradient, white topbar with blue accents, blue buttons/focus/links, blue DataTables styling, blue dashboard KPI gradients, blue login gateway. Centralized CSS variables (--hms-primary etc.), zero functionality change.
- **TEST**: Playwright on QA — login gradient rgb(13,110,253), sidebar blue gradient, buttons #0d6efd, dashboard 5 KPI cards 0 errors, roommaster/outletsetup/poskot clean, mobile 358px fits; 0 purple remnants (only summernote editor's own "Cold Purple" swatch remains — editor content, not theme). Suite 68 passed (165 assertions).
- **NOTE**: QA server needed `DB_DATABASE=analysis_qa` env override (a shell env var was overriding .env.qa — Dotenv immutability).
### UI Pass 5 — Full module color sweep + select2 plugin gap fix
- **COLOR SWEEP**: converted ALL remaining legacy brand colors to the blue family — 9 report blades (#fa65b1 pink scrollbars), index2 legend (violet Inspected, old-blue Occupied), 8 HK screens (#1e3a5f/#2d6a9f → brand), tools quotation (2), HR/extra (7, pink), print/lostfound (#5b21b6), frontend QR outlet page (#e91e63 pink → blue), assignment report print. Semantic colors (green/amber/red status) preserved.
- **SELECT2 BUG (same class as the DataTables fix)**: 51 views call select2 but the shared layouts never loaded it → `$(...).select2 is not a function` on report pages. Vendored asset already existed (`admin/plugins/select2/`); wired into property/tools/admin layout headers (JS after jQuery, CSS in head) + 3 pages that reload their own jQuery (dailyfunctionsheet, bookinginquirydetail, rewardpointreport).
- **TEST**: Playwright — all 16 swept pages + 5 select2 pages render with 0 errors; suite 68 passed (165 assertions).

### fix: Report-module permission guards + upsert + date-range fixes (RPT-01..05)
- **MODULE**: Reporting / Finance
- **FILES**: `app/Http/Controllers/Reporting.php` (+4 methods guarded, upsert fixed, date range corrected), `app/Http/Controllers/Finance/FinanceController.php` (tdsreport guard uncommented), `resources/views/property/report_bulkcharge.blade.php` (todate variable)
- **CHANGE**: Fixed RPT-02 (P1): `billreprintsubmit` financial write now guarded with `revokeopen(141115)`. RPT-03: `updatemenuitems`/`updateitemrates` guarded with `revokeopen(141215)`, upsert logic restored (updates existing rates instead of always inserting duplicates). RPT-05: `tdsreport` permission check uncommented. RPT-01: bulk-charge report date range corrected (fromdate = month-ago, todate = today); `$todate` now passed to view. RPT-07: duplicate `revokeopen(141212)` call removed.
- **WHY**: P1 financial write without permission guard; P2 upsert bug creating duplicate rows; P2 commented-out permission checks.
- **TEST**: php -l clean on all 3 files; view:cache compiles; MySQL offline so full suite skipped (no failures).
- **RISK**: LOW — added guards to previously unguarded methods (fail-closed); date-range fix is read-only view default; upsert fix is more correct behavior (update existing + insert new).
- **ROLLBACK**: revert the 3 files.

### feat: Guest Management modules — Wake-up Call (GM-01) + Guest Messages (GM-02)
- **MODULE**: Housekeeping / Guest Management
- **FILES**: `app/Http/Controllers/HouseKeeping.php` (+14 methods), `app/Models/Guestwakeup.php` (new), `app/Models/Guestmessage.php` (new), `database/migrations/2026_08_18_000001_create_guestwakeup_table.php` (new), `database/migrations/2026_08_18_000002_create_guestmessage_table.php` (new), `resources/views/property/housekeeping/wakeuplist.blade.php` (new), `resources/views/property/housekeeping/printwakeuplist.blade.php` (new), `resources/views/property/housekeeping/guestmessagelist.blade.php` (new), `resources/views/property/housekeeping/printguestmessagelist.blade.php` (new), `routes/company.php` (+12 routes)
- **CHANGE**: Implemented GM-01 (Wake-up Call Booking): desk screen to book/manage guest wake-up calls linked to in-house rooms (room/guest auto-fill from roomocc, vno sequence, reminder/food-order/other-request flags, date-range list, print). GM-02 (House Guest Messages): desk screen to record/call/deliver messages for in-house guests (caller/telephone/message, status tracking, mark-delivered audit, date-range filter, print). Both follow legacy GuestWakeUp/GuestMessage schema. Read-only before migration.
- **WHY**: Legacy parity — both modules existed in legacy HMS (FrmGuestWakeUp, FrmHouGuestMsg) and were documented as MISSING in MODULE_STATUS.
- **TEST**: php -l clean on all 6 files; view:cache compiles; 12 routes registered; MySQL offline so suite skipped (no failures).
- **RISK**: LOW — purely additive, new tables + CRUD, no existing functionality touched.
- **ROLLBACK**: remove the 2 models + 2 migrations + 2 controller method groups + 12 routes + 4 views.

### feat: Guest Master browse page (GM-07, read-only)
- **MODULE**: Front Office / Guest Management
- **FILES**: `app/Http/Controllers/FrontOffice/Operations/HouseModelOperations.php` (+3 methods: guestmaster, fetchguestmaster, guestmastervisits), `resources/views/property/frontoffice/guestmaster.blade.php` (new), `routes/company.php` (+3 routes)
- **CHANGE**: Added read-only Guest Master page: search by name/mobile/email/guest-code, browse profiles (guest code, name, mobile, email, city, country, type, VIP, total stays, last check-in/out), stay-history modal (roomocc join with room category + in-house/checked-out status), link to existing profile edit screen (`guestaddprofile`). Read-only — no writes.
- **WHY**: Legacy `frmGuestInfo` was a searchable guest master list; Laravel only had the walkin lookup, no dedicated browse page.
- **TEST**: php -l clean; view:cache compiles; 3 routes registered; MySQL offline so suite skipped (no failures).
- **RISK**: LOW — read-only, reuses existing tables (guestprof + roomocc), no schema change.
- **ROLLBACK**: remove 3 methods + 3 routes + 1 view.
