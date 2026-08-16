# Analysis HMS — FRONT OFFICE REPORTS

> FO report inventory: Laravel vs legacy. STATUS: EXISTS / PARTIAL / MISSING / IMPROVED / OBSOLETE.
> Verified 2026-08-16.

---

## Report status

| Report | Laravel | Legacy | STATUS |
|--------|---------|--------|--------|
| Advance reservation report | `advresreport` (Reporting@advresreport, vtype ADRES/ARRES, revokeopen 131213) | legacy advance report (Sum PC.AmtCr joined booking) | EXISTS |
| **Advance/Folio reconciliation** | ✅ **NEW 2026-08-16** — `advreconreport` (res advance vs folio advance vs deleted; flags MISMATCH/OVER-CREDIT/PENDING-TRANSFER/CANCELLED-CHECK; row trace modal; restore/repost) | legacy "Can not delete advance" guard | **IMPROVED (was MISSING)** |
| Expected checkout | `expectedcheckout` (guestfolio+roomocc where chkoutdate null) | legacy | EXISTS |
| FOCC report | `focc_report` (+amount/print) | legacy | EXISTS |
| Cashier settlement | cashier/POS settlement reports | legacy settlement reports | EXISTS |
| Charge-removal (deletion) log | `NightAuditlogController@fetchchremovelog` (paychargelog) | legacy | EXISTS |
| Night audit | nightauditlog views | legacy | EXISTS |
| Housekeeping / room status | housekeeping view | FrmHouseStatus | EXISTS |
| In-house list | roomocc-based screens | FrmHouseStatus | EXISTS |
| KOT / NC reports | `nckotreport` | legacy | EXISTS |
| Reservation list | reservationlist views | legacy | EXISTS |
| Guest ledger | ledger views | legacy | EXISTS |
| No-show report | ❌ | ⚠️ verify legacy | **MISSING** — build read-only (1,372 candidates on live DB) |
| Orphan room / folio-without-room report | ❌ | — | **MISSING** — diagnostic (13 orphan rooms, 70 folios w/o room) |
| Folio-on-cancelled-booking report | ❌ | — | **MISSING** — diagnostic (79 on live DB) |
| Settlement consistency report | ❌ | — | **MISSING** — settled folios vs open balance |
| Arrival/departure vs folio mismatch | ❌ | — | **MISSING** — verify then build |

## New reports added (2026-08-16)
- `advreconreport` — Advance/Folio reconciliation (route `advreconreport`, POST `advreconreportfetch`, POST `advreconreportdetail`, POST `advreconrestore`)
- `fodiagnostics` — Front Office mismatch diagnostics (GET `fodiagnostics`, POST `fodiagnosticsfetch`) — tabs: No-Show candidates, Orphan Rooms, Folios w/o Room, Folio on Cancelled Booking, Reservation-vs-Folio (departure/room/category/rate/plan/carry mismatches), Settlement Balance. Read-only.

## Recommended next (read-only, safe)
1. ✅ DONE — FO mismatch diagnostic report.
2. Data remediation for flagged items (room/rate/plan drift, settlement balances) — requires business decisions + approval (data mutation).
