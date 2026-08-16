# Analysis HMS — FRONT OFFICE GAPS

> Flow-by-flow comparison: current Laravel vs legacy HMS (`.ai/HMS.bas`/`.text`). Verified 2026-08-16 against live `analysis` DB.
> STATUS: COMPLETE / PARTIAL / MISSING / IMPROVED / OBSOLETE / UNKNOWN. Legacy is a reference — Laravel improvements are kept.

---

## FO Flow Map

### 1. Reservation
| Aspect | Laravel | Legacy | STATUS |
|--------|---------|--------|--------|
| Booking create | `CompanyController` reservation methods, `booking` + `grpbookingdetails` | FrmBookingInquiry, FrmReservationStatus | COMPLETE |
| Room blocking (hard/soft) | ⚠️ verify | FrmBlock, FrmSoftBlock, FrmBlockMast | ⚠️ VERIFY |
| Rate plan / package | PlanMast, plan1/plandetails | FrmPackage, FrmPlanPkg, FrmPlanPackMast | COMPLETE |
| Booking source / market | BookingSource, BussSource | FrmBusinessSrc, FrmMarketSeg | COMPLETE |
| Advance capture (ADRES) | `Advance::submitadvdeposit` → paycharge (refdocid=booking, vtype ADRES; ARRES refund) | frmAdvanceDepDialog | COMPLETE |
| No-show handling | ❌ no explicit no-show workflow | ⚠️ verify | **MISSING/VERIFY** — 1,372 stale reservations past arrival with no folio on live DB |
| Cancellation | `revcancel`, booking.Cancel='Y' | FrmCancel | COMPLETE |
| Advance refund on cancel | ARRES rows | legacy refund dialog | COMPLETE |
| Reservation statuses | Confirm, Commit, Cancel, Booked, Waiting, Tentative | similar | COMPLETE |

### 2. Advance (mission §10 special check)
| Check | Result |
|-------|--------|
| ReservationAdvance = FolioAdvance + Used + Refunded + Transferred | ⚠️ **MISMATCHES FOUND** — live DB shows reservations with advance where folio=0 (see FRONT_OFFICE_REPORTS.md, `advreconreport`) |
| Deletion audit | ✅ **FIXED 2026-08-16** — `deleteadvancedeposit`/`deleteadvancebanquet` now log to paychargelog (BUG-030) |
| Historical deletion amounts | ❌ BUG-031 — old code copied only amtdr → amtcr=NULL in log (trail only) |
| Restore/repost | ✅ **IMPLEMENTED 2026-08-16** — guarded, audited, never-duplicate restore of missing folio advance (see COMPLETED_TASKS.md) |

### 3. Check-in
| Aspect | Laravel | STATUS |
|--------|---------|--------|
| Walk-in check-in | `submitwalkin` — creates RoomOcc + GuestFolio + paycharge transfer + guestprof; advance transfer (leader/non-leader split) | COMPLETE |
| Reservation check-in | `updatewalkin`/`walkinupdate` (11,158/11,328) | COMPLETE |
| Advance transfer | paycharge CHK rows on folio (refdocid=booking, folionodocid=folio); REV round-offs excluded | COMPLETE |
| Transaction safety | ⚠️ `submitwalkin` uses DB::transaction? **VERIFY** (settlement path confirmed transactional; check-in needs audit) | VERIFY |
| Room repeat-check guard | ✅ exists (checkrepeat on roomocc + grpbookingdetails) | COMPLETE |

### 4. RoomOcc / GuestFolio / PayCharge
| Check | Live-DB result | STATUS |
|-------|----------------|--------|
| RoomOcc without folio | **13 rows** | ⚠️ DATA MISMATCH |
| Folios without RoomOcc | **70 rows** | ⚠️ DATA MISMATCH (may include departed folios; verify) |
| Folio whose booking is CANCELLED | **79 folios** | ⚠️ DATA MISMATCH (checked-in guests on cancelled reservations) |
| Folios without booking link | 39,428 (walk-ins — expected) | OK (expected) |
| Advance with no grpbookingdetails | 0 | OK |

### 5. Room Change
| Laravel | Legacy | STATUS |
|---------|--------|--------|
| `submitroomchange` (10,994), `openchangeroom` (16,989) | FrmChangeDepart | COMPLETE — verify atomicity + folio/rate carry-over |

### 6. Additional Charge
| Laravel | Legacy | STATUS |
|---------|--------|--------|
| `openchargeposting` (13,153) + room charge posting | sundry charges | COMPLETE |

### 7. Settlement / Checkout / Bill
| Laravel | Legacy | STATUS |
|---------|--------|--------|
| `submitRoomSettle` (17,746) — **transactional** (DB::beginTransaction + rollback), creates REC voucher, sets paycharge SettleDate, roomocc type='O' + chkoutdate, grpbookingdetails chkoutyn='Y' | `Update Paycharge set SettleDate=... where FolioNoDocid=...` + FomBillDetails Status='SETTLE' | COMPLETE (IMPROVED — transactional) |
| Resettlement | `openbillresettlement` (18,142), `updateRoomSettle` (18,187) | legacy settle reports | COMPLETE |
| Bill print | PrintController + billprint views | FrmFOM | COMPLETE |
| Settlement inconsistencies | ⚠️ verify settled folios with open balance; paycharge settledate vs roomocc chkoutdate drift | VERIFY |

### 8. Night Audit
| Laravel | Legacy | STATUS |
|---------|--------|--------|
| `opennightaudit` (15,043), `submitnightaudit` (15,055), `opennightaudit2`/`submitnightaudit2` | night audit forms | COMPLETE |
| Audit log view | `NightAuditlogController` (charge-removal log from paychargelog) | COMPLETE |
| Rollover integrity | ⚠️ verify ncurdate rollover + auto room charge post | VERIFY |

---

## GAP LIST (prioritized)

| # | Gap | Evidence | Priority | Action |
|---|-----|----------|----------|--------|
| FO-G01 | Advance/folic mismatch detection | Live DB: checked-in guests with res advance but ₹0 folio advance (11+ in sample, more DB-wide) | P0 | ✅ DONE — `advreconreport` + restore/repost |
| FO-G02 | No-show workflow | 1,372 stale reservations past arrival, no folio, not cancelled | P1 | Build read-only No-Show report; no-show marking needs business rule confirmation |
| FO-G03 | Check-in transaction safety | `submitwalkin` multi-table writes — verify DB::transaction wrapping | P1 | ✅ DONE 2026-08-16 — verified transactional (RoomOcc+GuestFolio+GuestFolioProfDetail+GuestProf+PlanDetail+CHK advance in one DB::beginTransaction). Checkout `submitRoomSettle` also transactional. Room change was NOT — fixed (BUG-034) with transaction + `==` comparison fix |
| FO-G04 | Orphan rooms (13) / folios without rooms (70) | Live DB | P1 | Diagnostic report (read-only); fix requires data review + approval |
| FO-G05 | Folios on cancelled bookings (79) | Live DB | P1 | Diagnostic report; decide policy (reopen booking / settle / void) with business |
| FO-G06 | Room-blocking parity | Legacy FrmBlock/FrmSoftBlock vs Laravel | P2 | Verify existence; classify |
| FO-G07 | Settlement consistency check | Verify settled folios with open balance | P2 | Diagnostic report |
| FO-G08 | Night audit rollover verification | — | P2 | Read + verify |
| FO-G09 | Booking-vs-folio field drift (departure×21, room×33, rate×56, plan×10, carry×196) | Live DB (property 158) | P1 | ✅ DONE — `fodiagnostics` tab resvfolio (read-only); data fixes need business approval |
| FO-G10 | Settlement inconsistencies (checked-out folios with open balance) | 28 on property 158 | P1 | ✅ DONE — `fodiagnostics` tab settlement; resolution (collect/waive) needs business decision |
| FO-G11 | No-show candidates | 49 on property 158 (1,372 all properties) | P1 | ✅ DONE — `fodiagnostics` tab noshow; no-show marking needs business rule |
| FO-G12 | Room-change msno1 corruption (BUG-034) | Live DB: 109CHK\|2026\|152 msno1=2 vs leader sno1=6 | P0 | ✅ FIXED (code) 2026-08-16 — `==` + transaction + regression test; 1 historical corrupt row needs approval to repair |
| FO-G13 | Checked-out rows without checkout date | 2 historical (115CHK\|2026\|166, 157CHK\|2026\|360) | P2 | Documented; repair needs approval |

## Validation gaps (compare reservation vs folio — mission list) — VERIFIED 2026-08-16 (live DB, active rooms)
| Compare | Result (live `analysis` DB, active rooms) | Verdict |
|---------|-------------------------------------------|---------|
| arrival | 0 mismatches (>2d) — check-in date tracks booking arrival | ✅ OK |
| departure | **21 mismatches** (>2d) | ⚠️ GAP (in `fodiagnostics` tab resvfolio) |
| room allocation | **33 mismatches** (occupied room ≠ booked room, no room-change record) | ⚠️ GAP |
| room category | **1 mismatch** | ⚠️ minor |
| rate | **56 mismatches** (roomrate ≠ booked Tarrif) | ⚠️ GAP |
| meal plan | **10 mismatches** (plancode ≠ Plan_Code) | ⚠️ minor |
| company/travel-agent/source carry | **196 folios missing company or travel agent carried from booking** | ⚠️ GAP |
| room change tracking | **2,886 room changes recorded** (newroomno/chngdate) | ✅ feature works |
| settlement | **28 checked-out folios with open balance** on property 158 (e.g., folio 374 open ₹1,647) | ⚠️ GAP (in `fodiagnostics` tab settlement) |

> ✅ **IMPLEMENTED 2026-08-16**: `fodiagnostics` page (read-only) with tabs: No-Show / Orphan Rooms / Folios w/o Room / Folio on Cancelled Booking / Reservation-vs-Folio / Settlement Balance. These VERIFY items are now visible in one report — move to FRONT_OFFICE_TESTS.md as live checks.
