# Analysis HMS — MISSING LOGIC

> Business-logic gaps even where UI exists. Compare workflows, not screens.

---

## Financial / accounting (P0)

| # | Gap | Evidence needed | Status |
|---|-----|-----------------|--------|
| ML-01 | **Advance → folio → settlement traceability** — mission §10 requires Reservation→Advance→Check-in→Folio→Settlement linkage + mismatch detection (ResAdv ₹1000 / FolioAdv ₹0 / PayChargeLog ₹1000). | ✅ **PARTIALLY DONE 2026-08-16** — read-only `advreconreport` (routes in reporting.php; Reporting@advreconreport/fetch/detail) traces ADRES/ARRES → CHK transfer → paychargelog deletions and flags MISMATCH/OVER-CREDIT/PENDING-TRANSFER. Verified on live `analysis` DB (11 real mismatches in sample). | Restore/repost option + settlement leg still MISSING (needs approval — mutates data) |
| ML-02 | **Check-in atomicity** — booking + grpbookingdetails + roomocc + guestfolio + paycharge + advance transfer should be one DB transaction (mission §18). Verify `CompanyController::checkin*` uses DB::transaction or partial writes. | Read checkin method | VERIFY |
| ML-03 | **Check-out atomicity** — settlement + bill + room release + ledger posting in one transaction. | Read checkout method | VERIFY |
| ML-04 | **POS billing atomicity** — sale1/sale2 + kot + payment + stock in one transaction. | Read Pointofsale bill methods | VERIFY |
| ML-05 | **Folio merge / reverse-merge** — legacy FrmMergeCharge/FrmRevMergeCharge. Confirm Laravel has both with audit. | Scan CompanyController | VERIFY |
| ML-06 | **Stock issue/transfer atomicity** — legacy FrmStockTransfer. | Scan InventoryController | VERIFY |
| ML-07 | **Voucher posting integrity** — Suntran debit/credit balance check (mission §8 Accounts). | Read AccountPosting service | VERIFY |
| ML-08 | **Financial deletion audit** — mission §9: no silent deletion of PayCharge/GuestFolio/POS/KOT/Purchase/Voucher/Ledger/Settlement. | ✅ **DONE 2026-08-16** — all paycharge deletions audited via `PaychargeLog::auditDeleted()` (BUG-037: POS settle/repost ×2, alt-settle, chargesposting, night-audit cron, AccountPosting batch, 2 ROFF deletes; advance deletes from BUG-030). Remaining: `ToolsController::deletedate`, `delete_table_record`, `deletemultiplerecords` (admin bulk tools) | VERIFY (bulk tools) |

## Operational (P1)

| # | Gap | Status |
|---|-----|--------|
| ML-09 | Reservation cancellation/advance refund flow — confirm refund posts back to ledger with audit. | ✅ **PARTIAL 2026-08-16** — `submitadvdeposit` handles Refund type (amtdr) with tax lines and audit fields; `updatecancel` exists. Remaining: confirm refund links back to the original ADRES (contraid) and ledger posting on cancel. |
| ML-10 | Room blocking (FrmBlock/FrmSoftBlock) — confirm soft/hard block exists. | VERIFY |
| ML-11 | **KOT token sequence** — `kot.tokenno` + `depart.cur_token_no_kot` (legacy `CurTokenNoKOT` per-outlet sequential KOT token). | ✅ **DONE (server-side) 2026-08-16** — `submitkotentry` writes `tokenno = cur_token_no_kot + 1` per outlet and persists counter. Remaining: token display/print (printdelay schema + spooler), daily auto-reset hook (`auto_reset_token`), meal-token master + `PlanMealTokens` report (business decisions) | PARTIAL |
| ML-11 | Room availability calculation — confirm considers blocks + departures + day-use. | VERIFY |
| ML-12 | Night audit close — confirm date rollover + auto room charge + report consistency. | VERIFY |
| ML-13 | POS NC (no-charge)/cancellation/merge/split bills — confirm all exist (FrmNCType legacy). | VERIFY |
| ML-14 | Tax slab application (TaxStru Between/<= operators) — confirm rate selection matches legacy (helpers already show slab logic). | VERIFY |
| ML-15 | EPABX/telephone call charging — module existence unknown. | UNKNOWN |
| ML-16 | Membership/reward points accrual & redemption — module existence unknown. | UNKNOWN |

## Rule reminder

If neither Laravel, DB, legacy, nor docs support a rule → mark **UNKNOWN BUSINESS RULE** in `.ai/QUESTIONS.md`. Do not invent.
