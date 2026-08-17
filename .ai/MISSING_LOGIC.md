# Analysis HMS — MISSING LOGIC

> Business-logic gaps even where UI exists. Compare workflows, not screens.

---

## Financial / accounting (P0)

| # | Gap | Evidence needed | Status |
|---|-----|-----------------|--------|
| ML-01 | **Advance → folio → settlement traceability** — mission §10 requires Reservation→Advance→Check-in→Folio→Settlement linkage + mismatch detection (ResAdv ₹1000 / FolioAdv ₹0 / PayChargeLog ₹1000). | ✅ **PARTIALLY DONE 2026-08-16** — read-only `advreconreport` (routes in reporting.php; Reporting@advreconreport/fetch/detail) traces ADRES/ARRES → CHK transfer → paychargelog deletions and flags MISMATCH/OVER-CREDIT/PENDING-TRANSFER. Verified on live `analysis` DB (11 real mismatches in sample). | Restore/repost option + settlement leg still MISSING (needs approval — mutates data) |
| ML-02 | **Check-in atomicity** — booking + grpbookingdetails + roomocc + guestfolio + paycharge + advance transfer should be one DB transaction (mission §18). Verify `CompanyController::checkin*` uses DB::transaction or partial writes. | Read checkin method | VERIFY |
| ML-03 | **Check-out atomicity** — settlement + bill + room release + ledger posting in one transaction. | Read checkout method | VERIFY |
| ML-04 | **POS billing atomicity** — sale1/sale2 + kot + payment + stock in one transaction. | Read Pointofsale bill methods | ✅ **DONE 2026-08-17** — `salebillsubmit` already transactional; **FIXED** `salebillupdate` (rewrites Sale2/Stock/Suntran/Sale1 — was untransactioned), `Pos::possalebillsettle` + `Pointofsale::salebillsettlesubmit` (delete+reinsert settlement paycharge — old code's tx was lost in a rewrite; restored), `nillsettle` (paycharge+roomocc updates). All now DB::beginTransaction/commit/rollBack wrapped. |
| ML-05 | **Folio merge / reverse-merge** — legacy FrmMergeCharge/FrmRevMergeCharge. Confirm Laravel has both with audit. | Scan CompanyController | ✅ **VERIFIED 2026-08-17** — `RoomController::mergeroompost` (141118) + `mergereverseroompost` (141119) both exist and are transactional (begin/commit/rollBack); merge re-links paycharge (no deletes) so ML-08 deletion-audit does not apply; flushAvailability wired 2026-08-17. |
| ML-06 | **Stock issue/transfer atomicity** — legacy FrmStockTransfer. | Scan InventoryController | ✅ **DONE 2026-08-17** — `stocktransfersubmit`/`stocktransferupdate`/`purchasebillsubmit` already transactional; **FIXED** `mrentrysubmit` (Gin+Stock+PO-consumption+vno), `openingstocksubmit` (Stock+vno), `requisitionstocksubmit` (2 stock sets+Indent clear+2 vno), `requisitionstockupsubmit` (delete+2 inserts), `requisitionstockisuedelete` (Indent clear+stock delete). All now transaction-wrapped. |
| ML-07 | **Voucher posting integrity** — Suntran debit/credit balance check (mission §8 Accounts). | Read AccountPosting service | ✅ **DONE 2026-08-17** — VoucherEntry save/update/delete transactional with Dr=Cr balance check (verified); **FIXED P0**: `AccountPosting::accountpoststore` had its transaction **commented out** (deletes PPOS/IPOS paycharge + HPOST ledger per date then re-posts — a mid-run failure would leave the day half-posted). Re-enabled beginTransaction/commit; rollback added on the early env-check return. |
| ML-08 | **Financial deletion audit** — mission §9: no silent deletion of PayCharge/GuestFolio/POS/KOT/Purchase/Voucher/Ledger/Settlement. | ✅ **DONE 2026-08-16** — all paycharge deletions audited via `PaychargeLog::auditDeleted()` (BUG-037: POS settle/repost ×2, alt-settle, chargesposting, night-audit cron, AccountPosting batch, 2 ROFF deletes; advance deletes from BUG-030). **BUG-043 (this pass)**: bulk Tools paths closed — `deletedate` (Data Empty Tool) audit was dead code (unreachable after both branches returned) → now writes `userupdate` audit with pre-wipe per-table row counts BEFORE deleting, same transaction; `deletetablerecord` / `deletemultiplerecords` (Table Management) and `resetOutletData` (POS Recycle) now call `auditFinancialDeletion()` → `PaychargeLog::auditDeleted` / `LedgerLogService::store` / `Suntranlog` copies for paycharge/ledger/suntran rows. KOT cancel path (KotModal + Stock, non-ledger) verified complete in KOT pass. | ✅ DONE |

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
