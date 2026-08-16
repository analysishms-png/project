# KOT / NC / TOKEN — GAP ANALYSIS (Laravel vs Legacy HMS)

> Verified 2026-08-16 against Laravel source (`Kot.php`, `Pos.php`, `Pointofsale.php`, `Reporting.php`, `CompanyController.php`), legacy HMS.bas/HMS.text, and live `analysis` DB.

## Checklist (user request)

| Item | Laravel | Legacy | Status |
|---|---|---|---|
| KOT creation | `submitkotentry` (Kot.php:528) — per-outlet docid/vno, stock rows, NC branch | KOT insert with VNo/TokenNo | ✅ COMPLETE |
| KOT print | `sendprintdata` → `printdelay` table (external spooler), printedit/void handling, printer-path routing by kitchen/outlet | TokenPrint/CKOTPrintPath printer routing | ✅ COMPLETE (routing) |
| KOT transfer | `kottransferstore` (Kot.php:1171) — retarget pending KOT + stock, preserves original tokenno | — | ✅ COMPLETE |
| KOT cancellation | cancel deletes KotModal + reverses Stock rows (not ledger) | VoidYN | ✅ COMPLETE |
| NC KOT | `nctypecheckbox` in submitkotentry; `nckot=Y`, `pending=N` | NCKOT column | ✅ COMPLETE |
| NC reason | `ncreason` captured on entry + shown in `nckotreport` | Reasons/NCType | ✅ COMPLETE |
| NC type master | `nctype_mast` table + `fetchncpreviouskot` | NCType | ✅ COMPLETE |
| **Token sequence** | ❌ `kot.tokenno` always `''`; `depart.cur_token_no_kot` never read/incremented | `Update Depart Set CurTokenNoKOT=CurTokenNoKOT+1` per KOT → KOT.TokenNo | ⚠️ **MISSING — FIXED this pass (server-side)** |
| Token print | ❌ `printdelay` has no token column; spooler renders vno only | `PrintTokenNo`/`TokenPrint`/`TokenPrintAfter`/`TokenHeader`/`SaleBillTokenHeader1` on KOT/bill | ⚠️ MISSING (needs spooler + printdelay schema) |
| Token reset (daily) | ❌ `auto_reset_token` saved in departmaster but never acted upon | `Update Depart Set CurTokenNo=0,CurTokenNoKOT=0 where AutoResetToken='Yes'` (nightly) | ⚠️ MISSING |
| Meal token master | ❌ no `FrmPlanTokenMast` equivalent | `FrmPlanTokenMast` (Code/Name token plans) | ⚠️ MISSING (business decision) |
| Token reports | ❌ none | `PlanMealTokens` report (FDO Reports) | ⚠️ MISSING (report) |
| Outlet routing | ✅ printer path per restcode/kitchen, system-based printing | CKOTPrintPath | ✅ COMPLETE |
| Kitchen printing | ✅ printdelay + spooler (per kitchen `itemmast.Kitchen`) | CKOTPrintYN | ✅ COMPLETE |

## Verified evidence (live `analysis` DB, property 158)

- **3,192 KOT rows, 0 with tokenno** — token column never written.
- `depart.cur_token_no_kot` = NULL for most outlets, `'1'` for RS158 — counter never incremented.
- `depart.auto_reset_token` exists but no code resets counters.
- KOT schema is **legacy-complete** (`nckot`, `nctype`, `ncreason`, `tokenno`, `freesno`, `schemecode`, `itemrestcode`, `pax`, `printed`, `printflag`, `mergedwith`) — the schema was ported, the sequence logic was not.

## Implemented this pass (safe, additive)

**KOT token sequence** (`Kot.php` `submitkotentry`):
- Per outlet (restcode) group, read `depart.cur_token_no_kot` (+1), persist counter, write `kot.tokenno` on all rows of the new KOT docid.
- Mirrors legacy `Update Depart Set CurTokenNoKOT=...` on KOT insert.
- Transfer/merge paths (`kottransferstore`, merged-KOT rebuild) already preserve `tokenno` — untouched.
- No schema change; non-financial (order-level); zero impact on vno/docid/stock/billing.

## Remaining gaps (need business decision / external spooler)

1. **Token display on KOT** — `kotentry` screen pending-KOT table + `sendprintdata` → add token; `printdelay` needs a `tokenno` column + the external spooler must render it (out of this repo's control).
2. **Daily token reset** — hook into night-audit rollover: reset `cur_token_no`/`cur_token_no_kot` where `auto_reset_token='Y'` (legacy `AutoResetToken`).
3. **Meal token master (`FrmPlanTokenMast`)** — plan-token master for meal plans; confirm hotel need before building.
4. **`PlanMealTokens` report** — legacy FDO report listing meal-plan tokens; build after (3) is confirmed.

## NC — no gaps found
NC entry (flag+type+reason), NC type master, previous-NC fetch, and the NC KOT report (with reason) are all complete — do NOT duplicate.
