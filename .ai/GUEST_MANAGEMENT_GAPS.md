# Analysis HMS — GUEST MANAGEMENT GAPS

> Comparison: current Laravel (verified from source + live `analysis` DB) vs legacy HMS (`.ai/HMS.text`, `.bas`).
> Status legend: ✅ COMPLETE / ⚠️ PARTIAL / ❌ MISSING / 🔄 REPLACED (better than legacy)

---

## 1. Module inventory — what exists

### Laravel (verified)
| Feature | Route(s) | Controller | Table | Status |
|---|---|---|---|---|
| Guest profile add/edit (from folio) | `guestaddprofile` | `FrontOffice/Operations/HouseModelOperations@openguestaddprofile/newguestprofileadd` | `guestprof` | ✅ |
| Change profile (walkin) | `walkinguestupdate` → `walkinupdate` | `CompanyController@walkinupdate` | `guestprof` | ✅ |
| Standalone new profile | `guestaddprofile.route` / `newguestprofileadd` | `FrontOffice/Operations/HouseModelOperations` | `guestprof` | ✅ |
| Change profile (mprof / extra guest) | `profilechangeguestonly`, `updatemprof` | `FrontOffice/Operations/HouseModelOperations` | `guestfolioprofdetail`, `guestfolio`, `roomocc` | ✅ |
| Guest history lookup (name/mobile) | `guesthistory` | `Fetch@guesthistory` | `roomocc` + `guestfolio` + `guestprof` | ✅ |
| Guest status (VIP etc.) | `gueststatus`, `gueststatusstore`, print, export | `CompanyController@opengueststatus` | `gueststats` | ✅ |
| Guest ledger | `guestledger` | `CompanyController@openguestledger` | `paycharge` (folio) | ✅ |
| Guest charge | `guestcharge` | `CompanyController@openguestcharge` | `paycharge` | ✅ |
| Guest trail report | `guesttrail` / `fetchguesttraildata` | `Reporting@guesttrail` | `paycharge` | ✅ |
| Guest reward points | (POS) | `Pointofsale` + `Reporting` | `guestreward` | ✅ |
| Guest portal (self-service) | `guest-portal/*` | `FeedbackMasterController` | — | ✅ (modern) |
| ID / photo / signature capture | inside profile forms | — | `guestprof.pic_path`, `idpic_path`, `guestsign` | ✅ |
| Address / nationality / type / gender / DOB / age | profile forms | — | `guestprof` | ✅ |
| Company / Travel Agent on folio | `guestfolio.company`, `travelagent` | — | `guestfolio` | ✅ |
| Vehicle number (per stay) | `vehiclenum` field | — | `guestfolio.vehiclenum` | ⚠️ per-stay only |
| Comments (3 slots) | — | — | `guestprof.comments1/2/3` | ⚠️ columns exist, never written by UI |
| Likes/dislikes | POS feedback | `Pointofsale` | `guestprof.likes/dislikes` | ✅ (POS) |
| Foreign guest (passport/visa) | — | — | `guestprof.id_proof/idproof_no/issuing*` | ⚠️ partial |

### Legacy HMS (verified)
| Form | Table | Purpose |
|---|---|---|
| `frmGuestInfo` | `GuestProf`, `GuestProfFor`, `GuestProfVeh` | Guest master (search + edit) |
| `frmGuestComments` | `GuestComments` | Guest comments register (Code, Name, Add1/2, City, PinCode, PhoneR/O, MobileNo, FaxNo, DateTime, Comments) |
| `FrmGuestWakeUp` | `GuestWakeUp` | Wake-up call booking (DocID, VNo, RoomNo, Extension, RemReqd, FoodOrd, OtherReq, WDate, WTime) |
| `FrmHouGuestMsg` | `GuestMessage` | House guest messages (RoomNo, Caller, Telephone, Message, RecdDate/Time) |
| `frmGuestParamMast` | `GuestParam` | Guest master custom fields (T1Field1..T2Field8) |
| `FrmGuestStat` | `GuestStat` | Guest status master |
| `FrmGuestAddObj` | — | (additional-guest object) |
| `FormCReport` / `FormC` | `GuestProfFor` | C-Form (foreign guest police registration) report |

---

## 2. GAP LIST (prioritized)

| # | Gap | Legacy evidence | Laravel state | Priority | Recommendation |
|---|---|---|---|---|---|
| GM-01 | **Wake-up call booking** ✅ (`FrmGuestWakeUp`) | `GuestWakeUp` table: DocID, VNo, RoomNo, RoomCat, Extension, RemReqd, FoodOrd, OtherReq, WDate, WTime, GuestProf, FolioNo — full CRUD linked to RoomOcc/GuestFolio | ✅ ADDED 2026-08-18: `guestwakeup` table, 6 routes, HouseKeeping controller, wakeuplist blade + print | P1 | Implement module: table + CRUD + room/folic-linked + report. Modern replacement could be guest-portal "wake-up request", but desk-side screen is the legacy contract. |
| GM-02 | **House guest messages** ✅ (`FrmHouGuestMsg`) | `GuestMessage` table: RoomNo, RoomCat, Caller, Telephone, Message, RecdDate, RecdTime, GuestProf, FolioNo — insert/update/delete/print | ✅ ADDED 2026-08-18: `guestmessage` table, 6 routes, HouseKeeping controller, guestmessagelist blade + print | P1 | Implement: take message at desk for an in-house guest, deliver/notify, log with audit fields. |
| GM-03 | **Guest comments register** (`frmGuestComments`) | `GuestComments` table (multi-row per guest: phone, fax, address, DateTime, Comments) | ⚠️ PARTIAL — `guestprof.comments1/2/3` columns exist but are **always set to NULL** on insert; no UI writes them; no history (single guest row, not a register) | P2 | Either wire comments1/2/3 into the profile UI, or create a `guestcomments` table for the audit trail. Do not duplicate — decide one path. |
| GM-04 | **Foreign guest / C-Form data** (`FormCReport`) | `GuestProfFor` table: PassNo, IssDate, IssPlace, PassExpDate, VisaNo, VisaIssDate, VisaIssPlace, ExpDate, VisaDuration, DateArr, ArrPlace, PropStay, AddIndia, ModeTravel, Dest, ModeTravelUsed; report filters `GF.Type='Foreign'` | ⚠️ PARTIAL — `guestprof` has `id_proof`, `idproof_no`, `issuingcity/country`, `expiryDate`, `type` but NO passport/visa detail fields, no dedicated C-Form report | P1 (India hotel compliance) | Add passport/visa fields to profile (or child table) + C-Form report for foreign guests (`type='Foreign'`). Verify with hotel whether C-Form filing is done manually today. |
| GM-05 | **Guest vehicle profile** (`GuestProfVeh`) | `GuestProfVeh` table linked by `Code` (guest profile) | ⚠️ PARTIAL — only per-stay `guestfolio.vehiclenum` (shown when travelmode = By Car); no per-guest vehicle history | P3 | Optional: add vehicle history under profile. Low business value unless valet tracking needed. |
| GM-06 | **Guest master custom fields** (`frmGuestParamMast` / `GuestParam`) | `GuestParam` table with T1Field1..T2Field8 (16 custom fields) configured per property | ❌ MISSING — no `guestparam` table (0 in DB), no config screen | P3 | Only if hotel needs custom guest fields; else mark OBSOLETE. |
| GM-07 | **Guest master browse/list screen** ✅ | `frmGuestInfo` = searchable master list (CODE, NAME ordered, drill to full profile) | ✅ ADDED 2026-08-18: `guestmaster` read-only page — search by name/mobile/email/code, stay-history modal, links to profile edit | P2 | Build a read-only Guest Master page: search by name/mobile/email, list past stays (via roomocc/guestfolio join), link to profile edit. |

---

## 3. What is NOT a gap (already covered, do not duplicate)

- **Guest profile CRUD** — ✅ complete (add, edit, change, mprof/extra guest, photo, ID, signature).
- **Identity proof** — ✅ `id_proof`, `idproof_no`, `idpic_path`, issuing country/city, expiry.
- **Address** — ✅ add1/add2, city/state/country (+ names), zip, phone, mobile, email.
- **Nationality/type** — ✅ `nationality`, `type`, `country_name`, `gender`, `marital_status`, `dob/age`.
- **Company / Travel Agent** — ✅ on folio (`guestfolio.company`, `travelagent`), subgroup master.
- **Additional guest** — ✅ `guestfolioprofdetail` (mprof) + `updatemprof`/`profilechangeguestonly`.
- **Guest history (per name/mobile lookup)** — ✅ `Fetch@guesthistory`.
- **Guest communication** — ✅ SMS/WhatsApp (scheduled SMS, checkin/checkout templates), guest portal service requests, support tickets. (Legacy `GuestMessage` was desk-to-guest paper messages — different mechanism, see GM-02.)
- **Guest ledger / folio** — ✅ `guestledger`, `guestcharge`, `guesttrail`, folio-based paycharge.
- **Reward points** — ✅ `guestreward` (POS + report).

---

## 4. Recommended implementation order (safe, no business-rule invention)

1. **GM-01 Wake-up** (P1) — pure additive module; mirrors legacy schema; report + desk screen.
2. **GM-02 Guest messages** (P1) — pure additive; simple CRUD + room lookup.
3. **GM-04 C-Form / foreign guest** (P1) — ADD fields to profile form (no schema change needed if reusing `guestprof`; new child table only if needed) + report. ⚠️ ASK hotel whether C-Form is a compliance requirement today.
4. **GM-07 Guest Master page** (P2) — read-only, reuses existing data.
5. **GM-03 comments wiring** (P2) — pick one path (UI write vs register table).
6. **GM-05/GM-06** (P3) — optional / mark OBSOLETE after business confirmation.

> ⚠️ Rule: no new table/field without confirming the hotel actually needs it (mission §23 — never invent business rules). GM-01/02 are safe because legacy proves the business used them.

---

## 5. Verification notes (this session)

- Live DB: `guestprof` 69,864 rows; tables checked — no `guestcomments`, `guestmessage`, `guestwakeup`, `guestproffor`, `guestprofveh`, `guestparam`.
- `guestprof.comments1/2/3` written as NULL in `newguestprofileadd` (HouseModelOperations) and `submitwalkin` (CompanyController).
- Legacy `GuestWakeUp`/`GuestMessage` inserts carry audit fields (U_Name, U_EntDt, U_AE) — replicate if implementing.
- Legacy `FormCReport` reached via `GRepFormName = "FormC"` / `"FormCReport"` — no Laravel equivalent found.
