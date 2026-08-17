# HOUSEKEEPING — GAP ANALYSIS (Laravel vs Legacy HMS)

> Verified 2026-08-16 against Laravel source, legacy HMS.bas/HMS.text, and live `analysis` DB.

## Module scope compared

| Area | Laravel | Legacy HMS | Status |
|---|---|---|---|
| Room cleaning entry (per-room, amenities stepper) | `roomcleaningentry` + `submitcleaningentry` | — | ✅ COMPLETE (Laravel is MORE complete) |
| Cleaning completion audit (roomstatusbefore/after) | `hkcleaninghdr` | — | ✅ COMPLETE |
| Room status board (TR/OO/OD/OR/OC/VR/VD/VC) | `RoomStatus` controller + `roomstatus` view | `FrmHouseStatus` (dashboard) | ✅ COMPLETE (Laravel is MORE complete) |
| Status change audit (Clean/Dirty) | `roomclean` (C/D) | — | ✅ COMPLETE |
| Status change audit (OOO/release/damage-OOO) | `roomclean` (O/R) — **ADDED this pass** | — | ✅ COMPLETE |
| Housekeeping assignment | `hkcleaninghdr.assno/assid`, assignment screens | — | ✅ COMPLETE |
| Supervisor / inspection | assignment + cleaning-entry review | — | ✅ COMPLETE (inspection gate via completion flow) |
| Amenities | amenity stepper in cleaning entry | — | ✅ COMPLETE |
| Damage report | `hkdamage` + `damagereport`/`fetchdamagereportdata`/`updatedamagereport` | — | ✅ COMPLETE |
| Damage → OOO auto-block | `storeoutofororder` (writes roomblockout type=O, room_stat=O) | — | ✅ COMPLETE (audit ADDED this pass) |
| Lost & Found | `lostfoundform`/`lostfoundlist`/`lostfoundregister` | `FrmLostFound` | ✅ COMPLETE |
| Laundry | `laundrysend`/`laundryreceive` (+ edits) | — | ✅ COMPLETE |
| Cleaning items issued | amenity usage in cleaning entry | `FrmItemIssuedOnCleaning` / `DepartWiseItemIssueList` (store-side) | ⚠️ PARTIAL — legacy issues *department-wise store items*; Laravel captures *amenity usage* but there is no store/godown issue link. Belongs to INVENTORY module, NOT HK. Do NOT duplicate. |

## Critical safety verification (user requirement)

**Housekeeping status MUST NOT change Front Office availability.** Verified:

- FO availability (`getAvailability`, `getRooms`, `getRoomswalkin`, reservation pickers) derives ONLY from `roomocc` + `grpbookingdetails` (+ `roomblockout` type='O' exclusion in walkin picker).
- `room_stat` is **display-only** in FO contexts (housekeeping report shows Clean/Dirty; FO never filters/sells on it).
- No FO path filters rooms by `room_stat` → a housekeeper setting Dirty/Clean/OOO can never remove a room from sellable inventory or vice-versa. ✅ SAFE

## Audit history — verified & completed this pass

Before: `roomclean` held only C/D rows — the 24 OOO blockouts in 2026 had NO audit trail, and release-from-OOO had none either.

**ADDED (BUG-036):**
1. `savehousecleaning` type='O' branch → writes `roomclean` row (type='O', remarks `OOO: <reasons> [block]`).
2. `savehousecleaning` type='R' branch → null-guard on `$rblkout` (was unguarded → 500 on missing block) + writes `roomclean` row (type='R', `Released from OOO: <remark>`).
3. `storeoutofororder` (damage-report OOO) → writes `roomclean` row (type='O', `OOO via damage report: <desc>`).
4. All audit `remarks` truncated to `varchar(50)` (mb_substr) — column is 50 chars; long damage descriptions would otherwise silently truncate.

Schema: `roomclean.type varchar(1)` accepts 'O'/'R'; `u_name` not set on HK-initiated rows (consistent with existing C/D rows).

## Findings / notes

- `roomclean` C/D rows set `hosuekeeper`; O/R/damage rows leave it '' — acceptable (damage flow has no housekeeper).
- `savehousecleaning` C/D/O branches call `$roommast->room_stat = ...` without null-guard — a roomno absent from `room_mast` would 500. Pre-existing pattern; noted, not changed (behavior-preserving rule).
- Legacy `FrmItemIssuedOnCleaning` is a store inventory issue (`DepartWiseItemIssueList` with `GodownMast` locations) — out of HK scope; track in INVENTORY module analysis.
- No legacy HK workflow is missing in Laravel. Laravel supersets legacy.

## 2026-08-17 testing pass (BUG-045/046 + permission hardening)

| Finding | Severity | Fix |
|---|---|---|
| `housemaster`/`submithousemaster`/`updatehousemaster`/`deletehousekeepingmaster` guarded with `revokeopen(121512)` — legacy duplicate code present on only 21 props, **0 rows on prop 135**; canonical code is `151112` (41 props) | HIGH — every prop-135 user blocked from Housekeeper Master CRUD | `revokeopen(151112) ?? revokeopen(121512)` (4 user-pairs have only the legacy code; fallback preserves them; 0 props have 121512 without 151112) |
| 17 HK write paths had **no permission guard** (any authenticated user could POST): savehousecleaning, lostfound store/update, laundry send/receive store/update, cleaningtype/supervisor/floormaster CRUD, saveAssignmentReport/unassignRooms, submitstartcleaning, submitcleaningentry, storedamagereport/updatedamagereport/storeoutofororder, submitinspection | MEDIUM — no server-side authorization; menu visibility is driven by the same menuhelp codes | Guards added per live menuhelp route→code map; dual-code `??` fallbacks where one route maps to different codes across props (startcleaning 151114/151115, roomcleaningentry 151112/151115, assignments 151113/151114) |
| Validation catches on `storedamagereport`/`updatedamagereport`/`storeoutofororder` did `implode(' ', $ve->errors())` — `errors()` is array-of-arrays → **"Array to string conversion" fatal** on any validation failure | MEDIUM — any bad form submission 500s instead of showing field errors | `Arr::flatten($ve->errors())` (matches `submitinspection`) |
| `updatehousemaster`/`deletehousekeepingmaster`/`updatehksupervisor`/`deletehksupervisor` (master + employee sync) and `storeoutofororder` (close old blockout + insert + room_mast + audit) wrote multiple tables with no transaction | LOW-MED — partial failure leaves master/employee or blockout/audit inconsistent | DB::beginTransaction/commit/rollBack added |
| Emoji-named variable `$jaldiwahasehato📢` in `deletehousekeepingmaster`; duplicate `$scode` query in `submithousemaster` | cosmetic | renamed → `$deleted`; dup removed |

Verified already-safe (no change): report fetches are batched (hkstockreport, roomstatusboard, amenities/cleaning-register fetches — single join-based queries); `savehousecleaning`/`submitcleaningentry`/`submitstartcleaning`/`submitinspection`/`saveAssignmentReport` already transactional with audit rows.

Regression tests: `tests/Feature/HouseKeepingModuleTest.php` (6 tests / 9 assertions) — BUG-045, deny-without-permission ×2, authorized-user not blocked, validation-422, screen loads. Suite: 47 passed (90 assertions).

## Recommended actions

1. ✅ DONE — audit rows for OOO (manual + damage flow) and release.
2. ⏳ Optional — add optional `roomclean.u_name` capture for HK users (uses `Auth::user()`; damage flow already sets blockout `u_name`).
3. ⏳ Inventory module — evaluate `DepartWiseItemIssueList`-style store issue for cleaning consumables (business decision; not HK duplication).
