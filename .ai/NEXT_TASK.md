# Analysis HMS — NEXT TASK (WORK QUEUE)

> Auto-maintained queue. Always work the highest-priority **incomplete** task.
> Priority: P0 security/data/financial/core → P1 logic/reports → P2 perf/UI → P3 docs/refactor.

---

## Completed
### ✅ UI Pass 2b — Housekeeping Command Center — 2026-08-17
- Completed the HK Command Center with real data: fixed the hkfloors INNER-join bug (P1, all rooms were dropped when floors unconfigured — 6 sites), real pending inspections, real workload efficiency, working read-only room modal.
- Playwright-verified (15 rooms, real statuses, 0 errors); suite 68 passed.
- **Next**: UI Pass 4 — POS/KOT touch pass + report filter standardization (see `UI_NEXT_TASK.md`).

### ✅ UI Pass 3 — Main Setup master-screen standardization (UI-only) — 2026-08-17
- Reusable `pageheader` partial + hms.css components applied to 21 master screens: standard Page Header → Form → Data Table anatomy.
- Playwright: 19/19 reachable screens render headers, 0 page errors; suite 68 passed. Also fixed roomcategory/tablemaster autocomplete null-guards + paymaster `Datatable` typo.
- **Next**: Housekeeping Command Center (Pass 2b, see `UI_NEXT_TASK.md`).

### ✅ BUG-QA-013 — DataTables 2.x loaded globally (P1 functional fix) — 2026-08-17
- Shared layouts never loaded DataTables while 101 views called it (49 with 2.x-only `new DataTable(...)` syntax) — tables had no sort/search/paging and pages threw JS errors.
- Vendored DataTables 2.3.2 + Buttons 3.2.0 + Responsive 3.0.3 into `plugins/datatables2/`, wired into property/tools/admin layout headers; null-guarded roommaster's autocomplete listeners.
- Playwright-verified: full table features + export buttons render, 0 DataTables/page errors, mobile OK; suite 68 passed.

### ✅ UI Pass 2a — Login page redesign (navy/teal brand, UI-only) — 2026-08-17
- Redesigned `auth/login.blade.php` into a branded navy/teal panel (brand band, icon fields, navy button, demo panel) with an additive `body-class` hook in the frontend layout; marketing chrome hidden on the login gateway only.
- All functionality preserved byte-for-byte; Playwright-verified desktop + mobile; homepage unaffected; suite 68 passed.
- **Next**: Housekeeping Command Center (Pass 2b, see `UI_NEXT_TASK.md`).

### ✅ UI Pass 1 — Bootstrap-5-style global design system (UI-only) — 2026-08-17
- **Studied** the reference package (AnalysisHMS_Manual: 135 screenshots, 15 modules, menu tree, housekeeping manuals) and the current UI (Ekka BS4.1.3 theme; shared `property/layouts/*`; dashboard already modern).
- **Mapped** every reference screen → Laravel route/blade/controller in `UI_REFERENCE_SCREEN_MAP.md` + `UI_REFERENCE_LARAVEL_MAP.md`.
- **Built** the design system: `public/admin/css/hms.css` (navy/teal tokens, modern chrome, cards/tables/forms/buttons/modals, responsive, print) + one `<link>` in the header. Zero functional change — framework swap deliberately avoided (BS4-era plugins + `data-toggle` everywhere).
- **Verified** via Playwright on the QA instance (before/after, mobile off-canvas, dynamic sidebar menu, topbar nav) + full suite 68 passed.
- **Next**: Login page + Housekeeping Command Center (see `UI_NEXT_TASK.md`).


### ✅ P0 — Advance-delete audit + Phase-4 browser walkthrough (BUG-QA-011/012) — 2026-08-17
- **Audited** deleteadvancedeposit → paychargelog → advreconreportfetch DelAmount linkage end-to-end; verified the audited delete path is SOUND (QA fixture: deleted ₹2000 → DelAmount=2000, Recon=0, zero double counting).
- **BUG-QA-011 FIXED (P1)**: availableRooms closure in openupdatereservation/openupdatewalkin referenced un-captured `$row` → 500 on cache miss (found via browser walkthrough). Capture `$roomCat` scalar.
- **BUG-QA-012 FIXED (P2)**: deleteguestledger audit row lacked refdocid/amtcr/paytype (466 live ADRES deletions invisible to report DelAmount), fetch was vno-only, not atomic → full linkage + vtype scope + transaction.
- **QA infra built**: analysis_qa DB (schema + prop-102) + second instance :8001 (env overrides required — shell exports DB_DATABASE; immutable Env repo ignores .env.qa). Browser walkthrough: login → reservation → advance → check-in (advance-copy verified) → advance delete.
- Tests: `AdvanceDeleteAuditTest` (8 tests / 19 assertions). Suite: **68 passed (165 assertions)**.
- Historical note: 466 legacy deletions can't be retroactively linked (amounts never stored) — candidate data-repair pass via ToolsController if business wants history.


### ✅ P0 — Phase-12 report reconcile: DayBook/JournalBook/CashBankBook/GeneralLedger totals vs ledger (BUG-QA-010) — 2026-08-17
- Cross-checked all four ledger-composition reports against the raw `ledger` table per property (72 props, 2026, read-only).
- **BUG-QA-010 FIXED (P1)**: queries INNER-joined `subgroup` (legacy was `VIEWLEDGER LEFT JOIN SUBGROUP`) — 683 orphan-subcode rows / ₹7.02M dr silently dropped across 41 properties (HPOST advance legs on properties with unconfigured roomchrgdueac). All sites → `leftJoin`; **0/72 mismatches** after fix. Cash/Bank opening+period=closing verified.
- Data notes: PBPC/PBPB vouchers dr-only in ledger on some props (payable elsewhere) — accounts review; TDS join is party-name lookup, unchanged.
- Tests: `ReportReconcileTest` (3 tests / 20 assertions). Suite: **61 passed (146 assertions)**.

### ✅ P0 — Phase-6 critical workflow: reservation→advance→check-in→folio reconcile (BUG-QA-008/009) — 2026-08-17
- Traced write paths end-to-end (submitadvdeposit / submitwalkin advance-copy / submitRoomSettle — all transactional ✓); replicated the app's authoritative `advreconreportfetch` logic read-only on live data.
- **BUG-QA-008 FIXED (P1)**: post-check-in advances never reached the folio (`folionodocid` empty in `submitadvdeposit` even when ContraDocId set) → now folio-linked when in-house (main + tax rows), mirroring the check-in copy. 564/727 historical flags root-caused (post-check-in advances + manual ACCOUNT-TRANSFER workarounds); new advances will reconcile automatically.
- **BUG-QA-009 FIXED (P2)**: `deleteadvancedeposit` audit+delete now transactional.
- Tests: `AdvanceFolioLinkageTest` (4 tests / 11 assertions). Suite: **58 passed (126 assertions)**. Docs: AI_QA_BUGS, AI_QA_PROGRESS, CHANGELOG.

### ✅ P0 — Master QA pass: Banquet financial atomicity (6 defects) + HolidayController auth gap — 2026-08-17
- **Banquet** (all P1 financial): `performaInvoiceSubmit` transaction was commented out with orphan active commit (re-enabled + rollbacks); `deletebanquetbill` / `deletePerformaInvoice` (catch masked errors as success → fixed) / `deleteadvancebanquet` / `deletebanquet` / `banquetbillsubmit` wrapped in transactions.
- **HolidayController**: zero auth guard — unauthenticated `GET /holiday/data` leaked rows; added sibling-pattern constructor middleware.
- Swept all 27 write-bearing controllers; MainController admin routes verified controller-guarded (302); orphaned legacy `holiday` table documented.
- Tests: `BanquetHolidayQATest` (7 tests / 25 assertions). Suite: **54 passed (115 assertions)**. Docs: AI_QA_BUGS, AI_QA_PROGRESS, CHANGELOG.

### ✅ P1 — Housekeeping module testing pass (BUG-045/046 + permission guards) — 2026-08-17
- **BUG-045 FIXED**: housemaster CRUD guarded with wrong code `121512` (0 rows on prop 135) — now `revokeopen(151112) ?? revokeopen(121512)`; prop-135 users were completely blocked from Housekeeper Master.
- **17 unguarded HK write paths now permission-checked** (savehousecleaning, lostfound, laundry ×4, cleaningtype/supervisor/floormaster CRUD, assignments ×2, startcleaning, cleaningentry, damage ×2, OOO, inspection) with codes from the live menuhelp route→code map + dual-code fallbacks (startcleaning 151114/151115, roomcleaningentry 151112/151115, assignments 151113/151114).
- **BUG-046 FIXED**: validation catches crashed with "Array to string conversion" on 3 damage/OOO endpoints — now `Arr::flatten`.
- Transactions added to 5 multi-table write methods (housemaster/supervisor + employee sync, storeoutofororder). Emoji var + dup query cleaned.
- Tests: `HouseKeepingModuleTest` (6 tests / 9 assertions). Suite: 47 passed (90 assertions).

### ✅ P2 — Remaining loop batching: Banquet/POS/focc (PERF-02 tail) — 2026-08-17
- **Scan result**: the Banquet & POS hot LIST pages (displaytable, posbillentry, settlemententry, saleregfetch, settlereportfetch, colorfilldisp, advancelistData, banqoutstandingfetch, availablitybanquet, allbillxhr*) are **already join-based single queries** — no per-row loops. The remaining per-row loops were in write/print paths, now batched:
- **Pos::possalebillsettle mergedBills** — 2 queries per merged bill (Sale1 + first non-zero Paycharge row) → 1 grouped `whereIn` fetch per table, keyed in memory. **Parity**: 2 live merged bills, 0 mismatches (paycharge PK = (propertyid, docid, sno, sno1) → ordered by sno to reproduce the un-ordered `first()`).
- **Banquet::advancebanquetsubmit + editAdvanceSubmit tax posting** — 1 revmast name lookup per tax row → 1 batched `whereIn` fetch with **first-row-wins** map. **Parity**: rev_code is NOT unique per property (MT10310 = 'BA - NOTAX' vs 'STR - Hall Rent'!) — ordered by Desk_code (PK scan order) + first-row-wins to reproduce the original `value()` exactly; 2 codes, 0 mismatches.
- **Reporting::focc_reportfetch** — 1 Depart lookup per non-FOM payment row → 1 batched `pluck('name','dcode')` (dcode unique per property — verified). 8 codes, 0 mismatches.
- Suite: 41 passed (81 assertions) — incl. walkin + reservation + FOM-charge master-data cache regression tests (PERF-03). Docs: PERFORMANCE_AUDIT (PERF-02), CHANGELOG_AI, COMPLETED_TASKS.

### ✅ P2 — Cache master data (PERF-03) — 2026-08-17
- **ADDED**: `App\Helpers\MasterDataCache` — `Cache::remember` wrappers for the 5 hottest master lists: `travelAgents`, `corporates`, `companiesAndAgents`, `rooms`, `fomCharges` (24h TTL safety net; correctness relies on explicit flush).
- **Wired read sites** (CompanyController: walkin, walkinprefilled, reservation ×2, openreservation, FOM chargemaster/department pages, advance-options, roomresettlement; Reporting: fetchcompname/fetchcompany): 17+ sites now serve from cache.
- **Invalidation**: `flush()` (all 5 keys per property) added to **23 write paths** — subgroup insert/update/delete (ledgerstore party/ledger, subgroupupdate, quickAddTravelAgent, quickAddCompany), revmast insert/update/delete (taxmaster, FOM submitchargemaster/updatechargemasterstore/deletechargemaster, menucat/dept), room_mast insert/update/delete. Verified via automated sweep: **0 un-flushed write sites** remain in CompanyController.
- **Left untouched (verified safe)**: MemberMaster subgroup writes (comp_type='member' — never enters cached keys), PrintController revmast (BANQ desk — not FOM), HouseKeeping room_stat updates (operational status, not master list).
- **Measured** (live prop 135, walkin page): **15 queries / 63.6ms cold → 13 queries / 19ms warm**; all 5 cache keys match raw DB exactly (57 combined agent/company rows, names identical). File driver persists across requests. Suite: 37 passed (53 assertions).
- **Note**: per-date room availability queries are intentionally NOT cached (they change with bookings); only static master lists.

### ✅ P2 — Per-date room availability caching (PERF-03 follow-up) — 2026-08-17
- **ADDED**: `MasterDataCache::availableRooms(property, variant, roomcat, checkin, checkout, closure)` — **version-keyed** per-date availability cache. `flushAvailability()` bumps a per-property version counter → every previously-cached availability key for the property becomes unreachable in one cache write (no key enumeration). TTL 300s is a safety net only.
- **Wired read sites**: `RoomController::getRoomswalkin` (walkin page — posts once per room row) + `getRooms` (reservation page) + `CompanyController` openupdatewalkin/openupdatereservation per-row same-night room pickers. Walkin/reservation variants kept separate (slightly different subquery semantics).
- **Invalidation (17 write paths)**: walkin submit/update/room-change/delete, reservation update (UpdateReservation) + delete, API booking, channel-manager booking (ChannelPublic), frontend self-booking, HouseKeeping OOO blockout create/clear/damage-report, Pointofsale checkout, **night audit (`submitnightaudit` — roomocc.depdate + no-show cancel), room-move settlement (`submitRoomSettle` + `Frontend/RoomSettlement`), ToolsController `deletedate` bulk purge (both branches), RoomController `mergeroompost`/`mergereverseroompost`**. Left uncached: reservation-submit auto-fill validation (must see fresh availability) + `mergefolio`/checkout-time `leaderyn` toggles with no room/date change (no availability impact).
- **Verified complete**: brace-aware sweep across ALL controllers found 0 write methods lacking flush; CronController confirmed to have NO availability-table writes (autoCharge only posts paycharge) — night-audit roomocc.depdate updates live in CompanyController::submitnightaudit (now flushed). Suite: 41 passed (81 assertions).
- **Next**: remaining N+1 sites (PERF-02) — Banquet/POS/focc loops DONE 2026-08-17 (see Completed); PERF-05/06/07 need schema/index changes (approval).

### ✅ P1 — Journal Book report (legacy JournalBook parity) — 2026-08-17
- **ADDED**: **Journal Book report** (`journalbook`) — ledger postings for a voucher type in a date range (vdate/vtype/vno/docid/account/narration/dr/cr), vtype dropdown defaulting to `JV` (Journal), PDF print (`printjournalbook`), Excel export (`JournalBookExport`). 5 routes, 5 controller methods reusing shared `dayBookRows()`, 2 views. Reuses Trail Balance permission 111211.
- Mirrors legacy `Proc_203_70_14FE4CC` (`VIEWLEDGER LEFT JOIN SUBGROUP ... WHERE V_date BETWEEN ... AND V_TYPE='<type>' ORDER BY V_DATE,V_TYPE,V_NO,V_ADD,V_SNO`).
- **Verified live** (prop 169, Apr 2026): JV = 332 rows Dr=Cr=₹1,015,580.20 exact (matches Day Book JV filter — parity confirmed), PMT = 174 rows Dr=Cr=₹9,466,537.15 exact; Excel export + PDF render smoke-tested. Suite: 37 passed (53 assertions).
- **Accounts report parity now COMPLETE**: General Ledger ✅, Day Book ✅, Cash/Bank Book ✅, Journal Book ✅ — remaining: Aging/DueList (P2, need bucket-definition decision).

### ✅ P1 — Eager-loading/batching on hot report paths (PERF-02) — 2026-08-17
- **Night Audit Daily Report** (`NightAudit/Reports/DailyReport::dailyreportfetch`): replaced ~100 per-row aggregate queries (4× per FO revcode, 3× per dept×category cell, plus tax/deposit/occupancy loops) with grouped `whereIn` batches — **224 → 66 queries, 14.2s → 7.4s** on live property 135.
- **In-house reserved rooms** (`Api/InhouseRoomGet::reservedrooms`): per-booking advance lookup batched into one grouped `Paycharge` fetch — **880 → 5 queries, 1.7s → 0.04s**.
- **Room-type availability** (`Reporting::lookuproomtypefetch`): per-category × per-day (21 days) busy-room queries replaced with 2 bulk window fetches + in-memory date-overlap counting — **310 → 4 queries, 1.17s → 0.04s**; 154 daily values parity-checked, 0 mismatches.
- **Room inventory** (`Reporting::roominventoryfetch`): per-room balance+advance (2 queries each) replaced with one grouped `Paycharge` aggregate keyed by (folionodocid, sno1) — **110 → 3 queries**; 54 rooms parity-checked, 0 mismatches.
- **Front-office dashboard** (`Reporting::getindex`): today/yesterday memo-voucher sums (VoucherType + Paycharge per outlet×voucher, twice) replaced with batched VoucherType fetch + 2 grouped Paycharge sums — query count drops with outlet count; combinedTotal/yesterdaycombinedTotal parity-verified.
- **Output parity verified**: BEFORE/AFTER JSON diff on all 5 paths — 0 differences (matched original `null` vs `0` defaults exactly).
- **Tests**: `PerformanceEagerLoadTest` (read-only, live-DB, skips when DB down) now asserts query-count bounds on 4 paths (Daily Report ≤120, reservedrooms ≤50, lookuproomtype ≤20, roominventory ≤20) — a regression to per-row loops blows past these. Suite: 37 passed (53 assertions).
- **Note**: none of these used Eloquent relations in loops — the N+1 was explicit per-row queries; the batching principle (group-by-key fetch then in-memory lookup) is the same fix. `Fetch::postchargesone` (room-charge write path) also has per-row queries but is a write path — left untouched (risky).
- **Next**: apply the same batching scan to remaining list/report fetches (Banquet loops, Pos loops, focc_reportfetch depart lookup — small).

### ✅ P1 — Cash Book / Bank Book reports (legacy CashBook/BankBook parity) — 2026-08-16
- **ADDED**: **Cash Book / Bank Book report** (`cashbankbook`) — ledger filtered by `acgroup.nature` (Cash: CASH-IN-HAND; Bank: BANK ACCOUNTS/BANK OD-AC) via BUG-044-scoped join, per-account opening/running/closing balance, book toggle + optional account filter, PDF print (`printcashbankbook`), Excel export (`CashBankBookExport`). 5 routes, 5 controller methods + shared `cashBankBookRows()`, 2 views. Reuses Trail Balance permission 111211.
- **Canonical nature**: `acgroup.nature` (not the denormalized `ledger.groupnature` — stale for 372 rows on prop 169).
- **Verified live** (prop 169, Apr 2026): Cash 1 acct (CASH IN HAND ₹158,802→₹80,138), Bank 3 accts (CREDIT CARD A/C, HDFC, UPI), 0 identity mismatches; controller output == export output. Suite: 33 passed (39 assertions).
- **Next**: Aging/DueList need bucket decision (P2).

### ✅ P1 — Day Book report (legacy DayBook parity) + BUG-044 — 2026-08-16
- **ADDED**: **Day Book report** (`daybook`) — chronological register of ALL ledger postings in a date range (vdate/vtype/vno/docid/account/narration/dr/cr), optional vtype filter, PDF print (`printdaybook`), Excel export (`DayBookExport`). 5 routes, 5 controller methods + shared `dayBookRows()`, 2 views. Reuses Trail Balance permission 111211.
- **BUG-044 FIXED**: `acgroup.group_code` is not globally unique (shared across properties — verified 30158→157/158, 31104→103/104, …) → unscoped `leftJoin('acgroup')` multiplied ledger-report rows ~5%. Scoped 12 join sites (GL query/print/accounts, DTL query/print, DayBook, GL/DTL/DayBook exports) with `a.propertyid` match.
- **Verified live** (prop 169, Apr 2026): JV filter Dr=Cr exact (332 rows ₹1,015,580.20), ALL 2,822 rows, GL identity 104 accts / 0 mismatches, GL total = Day Book total ₹20,851,979.69. Suite: 33 passed (39 assertions).
- **Next**: Cash Book / Bank Book (P1) — ledger filtered to cash/bank group nature.

### ✅ P0 — Legacy-only module verification (8 forms) — 2026-08-16
- **Scanned** routes + controllers + views + live DB (SHOW TABLES) for the MODULE_STATUS ⚠️ list.
- **EXISTS**: Lost & Found (HouseKeeping lostfound CRUD + register + print, `lostfound` table).
- **MISSING (3)**: Denomination (legacy `DenominationDetail`, POS Reports — cashier closeout), ForEx (`ForEx` table, FrmForeignExMast/FrmForExRec), MeterReading (`FMReading`/`FMReading1`; maintenance has location+assets only).
- **OBSOLETE (2)**: PaxDetails (embedded sub-form of FrmGuestWakeUp, not standalone; pax fields pervasive), HotKey (voucher-entry helper dialog superseded by VoucherEntry UI).
- **REPLACED (partial)**: UnSettledBillsInfo (pos_saledeletereport del/unsettle + pendingkotreport + dashboard UnsettledRooms).
- **Already tracked**: WakeUp = GM-01 (GUEST_MANAGEMENT_GAPS.md).
- **Next**: only build MISSING items with business confirmation (mission §23). Suite: 33 passed (unchanged — read-only).

### ✅ P0 — Bulk Tools deletion audit (BUG-043) — 2026-08-16
- **`deletedate` (Data Empty Tool)**: audit was **dead code** — the `$userupdate` block sat after both branches' `return`, so a full property wipe (42 tables incl. paycharge/ledger/suntran/kot/sale1/2) executed with **zero audit trail**. Fixed: pre-wipe `userupdate` audit row with per-table row counts, written BEFORE deletes inside the same transaction (rolls back with a failed wipe → no false record).
- **`deletetablerecord` / `deletemultiplerecords` (Table Management)**: now audit financial rows before delete via new `auditFinancialDeletion()` → `PaychargeLog::auditDeleted` (paycharge), `LedgerLogService::store` (ledger), `Suntranlog` copies (suntran) — BUG-030/037/039 patterns.
- **`resetOutletData` (POS Recycle)**: same audit applied to the paycharge/suntran rows wiped per outlet vtype.
- **KOT cancel path** already verified non-ledger (KotModal + Stock) in the KOT pass — no change.
- ML-08 now ✅ DONE (was VERIFY). Suite: 33 passed (39 assertions).

### ✅ P1 — Accounts analysis + General Ledger report (legacy Led parity) — 2026-08-16
- **Traced** (Laravel vs legacy HMS): Voucher Entry (save/update/delete/print, LedgerLogService-audited), Voucher Verification, Bank Reconciliation, Trial/Group Trial/P&L/Balance Sheet/TDS, Detailed Trial Ledger (summary only), ledger master CRUD (deleteledger guarded) vs legacy report set (Led, LedDeb/LedCred/LedInt, DayBook, CashBook, BankBook, JournalBook, AgingDr/Cr, DUELIST, AcCheckList, MemLed, DetailedTrial).
- **ADDED**: **General Ledger report** (`generalledger`) — transaction-level per-account listing with opening/running/closing balance, optional account filter, PDF print (`printgeneralledger`), Excel export (`GeneralLedgerExport`). Reuses Trail Balance permission 111211; finance report view family. Read-only.
- **Verified live** (property 169, 17,881 ledger rows): 216 account identities (opening+trans=closing) OK, 67 running-balance recomputations OK, 0 mismatches.
- **Deletion audit**: deletevoucherentry ✅ audited (prior pass), deleteledger ✅ guarded — no new financial-safety defects in Accounts.
- **Still missing**: Aging (Dr/Cr), Due List — read-only candidates; need bucket-definition decision (P2).
- New doc: `.ai/ACCOUNTS_GAPS.md`. Suite: 33 passed (39 assertions).

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
4. **~~Audit remaining silent deletes~~** (ML-08) — ✅ **DONE 2026-08-16 (BUG-043)**: `deletedate` dead-code audit fixed + `deletetablerecord`/`deletemultiplerecords`/`resetOutletData` now audit paycharge/ledger/suntran before delete.
5. **~~Verify legacy-only modules exist in Laravel~~** — ✅ **DONE 2026-08-16** (read-only): Lost&Found EXISTS; Denomination/ForEx/MeterReading MISSING; PaxDetails/HotKey OBSOLETE; UnSettledBillsInfo REPLACED(partial); WakeUp = GM-01. Details in MODULE_STATUS + LEGACY_TO_LARAVEL_MAP.
6. **~~Remaining transaction-safety audit~~** (ML-02..07) — ✅ **DONE 2026-08-17**: ML-02/03 (check-in/out) transactional; ML-04 **FIXED** (salebillupdate, possalebillsettle, salebillsettlesubmit, nillsettle); ML-05 VERIFIED (mergeroompost/reverse transactional); ML-06 **FIXED** (mrentrysubmit, openingstocksubmit, requisitionstock* ×3); ML-07 **FIXED P0** (AccountPosting::accountpoststore transaction was commented out — re-enabled).

### P1
5. **GUEST MANAGEMENT gaps** (GUEST_MANAGEMENT_GAPS.md) — GM-01 Wake-up module, GM-02 House guest messages (both legacy-proven, additive) or GM-04 C-Form/foreign-guest report (compliance — confirm need with hotel first).
5b. **KOT token follow-ups** (KOT_NC_TOKEN_GAPS.md) — display token on KOT screen + print (printdelay schema + spooler), daily auto-reset in night audit, meal-token master + PlanMealTokens report (business decisions).
6. **~~Eager loading on top hot list/report pages~~** (BUG-025/PERF-02) — ✅ **DONE 2026-08-17** (see Completed): batched per-row lookups in NightAudit Daily Report (224→66 queries, 14.2s→7.4s), Api/InhouseRoomGet::reservedrooms (880→5), Reporting::lookuproomtypefetch (310→4), Reporting::roominventoryfetch (110→3), Reporting::getindex loops (today/yesterday memo-voucher sums) — all output-parity verified; regression tests added. Remaining N+1 sites still open (PERF-02 partially done).
7. **Missing report inventory** — complete MISSING_REPORTS.md by diffing legacy report forms vs Laravel Reporting routes; implement highest-value missing report (Day Book + Cash/Bank Book 2026-08-16, Journal Book 2026-08-17; Aging/DueList need bucket decision).
8. **GST/E-Invoice verification** — confirm taxcalc/e-invoice flows match TaxStru legacy rules.

### P2
8. **~~Cache master data~~** (travel agents, revenue codes, room lists) via `Cache::remember` (PERF-03) — ✅ **DONE 2026-08-17** (see Completed): `App\Helpers\MasterDataCache` + flush on all 23 write paths; walkin page 15q/63.6ms → 13q/19ms. Per-date room availability also cached (version-keyed + flush on 12 booking/blockout paths) — see Completed.
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
