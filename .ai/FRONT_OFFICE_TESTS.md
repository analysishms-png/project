# Analysis HMS — FRONT OFFICE TESTS

> Test plan for the Front Office module. Two layers: (a) automated (SQLite-independent where possible), (b) live-data verification (MySQL `analysis`).
> Principle: never claim FIXED without verification.

---

## A. Automated tests (add to `tests/`)

### Unit (no DB / SQLite in-memory)
| # | Test | File |
|---|------|------|
| FO-T01 | `formatCurrency`/`calculateTax`/`getDayNameFromDate` helpers | `tests/Unit/HelpersTest.php` ✅ (27 passing) |
| FO-T02 | Advance amount semantics: deposit → amtcr, refund → amtdr (pure function if extracted) | future |
| FO-T03 | Reconciliation flag computation (MISMATCH/OVER-CREDIT/PENDING/OK) — extract into a service and unit-test | future (recommended refactor) |

### Feature (routes + views compile)
| # | Test | Status |
|---|------|--------|
| FO-T04 | `advreconreport` GET route returns 200 (auth) | future |
| FO-T05 | `advreconreportfetch` validates fromdate/todate | future |
| FO-T06 | `advreconreportdetail` rejects missing docid | future |
| FO-T07 | `advreconrestore` refuses: unknown booking / not checked-in / non-positive missing amount / settled folio / cancelled booking | future |
| FO-T08 | All FO blade views compile (`php artisan view:cache`) | ✅ run every change |

## B. Live-data verification (MySQL `analysis`) — run before/after FO changes

| # | Check | Result (2026-08-16 baseline) |
|---|-------|------------------------------|
| FO-V01 | `php artisan test` | 27 passed (33 assertions) |
| FO-V02 | View compile | ✅ all blades |
| FO-V03 | Recon query runs; sample flags | 11 MISMATCH + 2 PENDING-TRANSFER (200-row sample, property 158) |
| FO-V04 | Detail query (deletion history) | 30 log rows for a flagged booking |
| FO-V05 | Folios on cancelled bookings | 79 |
| FO-V06 | Orphan RoomOcc (no folio) | 13 |
| FO-V07 | Folios without RoomOcc | 70 |
| FO-V08 | Stale no-show candidates (past arrival, no folio, not cancelled) | 1,372 |
| FO-V09 | Cancelled bookings total | 1,560 |
| FO-V10 | Booking-vs-folio drift (active rooms, all properties) | departure 21 / room 33 / category 1 / rate 56 / plan 10 / carry 196 |
| FO-V11 | `fodiagnostics` tabs (property 158) | noshow 49 / orphan 0 / folionoroom 0 / cancelledfolio 4 / resvfolio ≥500 / settlement 28 |
| FO-V12 | Room changes tracked (newroomno) | 2,886 |

## C. Regression checklist after FO changes
1. `php artisan test` green
2. `php artisan view:cache` green
3. Recon report fetch returns JSON without SQL errors (live)
4. Restore endpoint: guard paths return clean errors; success path writes paycharge + paychargelog audit inside one transaction
5. Deleting an advance still works and now writes paychargelog (BUG-030)
6. Settlement/checkout smoke: folio settle still updates roomocc type='O' + SettleDate
