# POS — GAP ANALYSIS (Laravel vs Legacy HMS)

> Verified 2026-08-16 against Laravel source (`Pos.php`, `Kot.php`, `Pointofsale.php`, `Banquet.php`, `ChargePosting.php`), legacy HMS.bas/HMS.text, and live `analysis` DB.

## Flow trace (Laravel)

Menu (`MenuItem` CRUD) → KOT (`submitkotentry`) → KOT Transfer (`kottransferstore`) → Order → Bill (`posbillentry`, `setentrypos`) → Tax/Discount (bill line calc) → NC (`fetchncpreviouskot`) → Payment / Room Charge (`possalebillsettleupdate` PPOS/IPOS) → Settlement (`possalebillsettle`, `salebillsettlesubmit` TOUT/REC) → Stock (Kot stock rows → stock deduction on settle) → Accounting (PPOS/IPOS daily re-posting → folio; `AccountPosting` service).

## Check results (user checklist)

| Check | Status | Notes |
|---|---|---|
| Duplicate bill | ✅ No | Sale bills get unique vno per outlet via VoucherPrefix; merged bills share one docid (mergedwith) |
| Duplicate payment | ✅ No | Settlement **deletes + re-posts** (idempotent rebuild); UI disables settle when TOUT exists (`setentrypos` `settled=Yes`); deletions now audited |
| KOT cancellation | ✅ No paycharge impact | Cancels KotModal + reverses Stock reservation rows only (not ledger) |
| KOT transfer | ✅ | `kottransferstore` — re-targets pending KOT + stock |
| Bill merge | ✅ | `mergedwith` list; merged bills settle as one (all docids deleted+reposted, now audited) |
| Bill split | ⚠️ PARTIAL | `split` flag used on bill-cancel; no dedicated split-bill UI (legacy `FrMultiBill` covered merge AND split) |
| Room charge | ✅ | PPOS/IPOS posted to folio via `possalebillsettleupdate`; nightly re-posted from suntran |
| Complimentary (NC) | ✅ | `fetchncpreviouskot` / NC flag on KOT lines |
| Discount | ✅ | Line-level discount in bill entry |
| Tax | ✅ | Per-item tax in sale2 (taxper/taxcondamt) |
| Payment modes | ✅ | CASH/UPI/CRED/CARD etc. via revmast paycodes; TOUT for room transfer |
| Stock deduction | ✅ | Stock rows on KOT entry; reversed on cancel/transfer |
| Accounting posting | ✅ | Daily PPOS/IPOS re-posting (manual `chargesposting`, night-audit cron, `AccountPosting` batch) |
| **Every paycharge deletion logged** | ✅ **FIXED this pass (BUG-037)** | see deletion audit map below |

## PayCharge deletion audit map (user requirement: "Ensure every financial deletion is logged")

| Site | Flow | Before this pass | Now |
|---|---|---|---|
| `Pos::deletebillxhr` | POS bill delete (reason required) | ✅ logged, but **amtcr omitted** (credit rows lost) | ✅ logged incl. amtcr via helper |
| `Pos::possalebillsettleupdate` (~788) | sale-bill room-charge re-post | ❌ **unlogged** | ✅ logged |
| `Pos::possalebillsettle` (~1224) | settlement re-post (merged docids) | ❌ **unlogged** | ✅ logged |
| `Pointofsale::salebillsettlesubmit` (~2976) | alt settlement re-post | ❌ **unlogged** | ✅ logged |
| `CompanyController::chargesposting` | daily POS→folio re-post | ❌ **unlogged** | ✅ logged |
| `CronController` (night audit) | nightly POS→folio re-post | ❌ **unlogged** | ✅ logged |
| `AccountPosting` service | batch POS→folio re-post | ❌ **unlogged** | ✅ logged |
| `CompanyController` bill-cancel ROFF delete | round-off removed on cancel | ❌ **unlogged** | ✅ logged |
| `CompanyController` settlement ROFF delete | round-off recompute | ❌ **unlogged** | ✅ logged |
| `CompanyController::deleteadvancedeposit` | advance delete | ✅ logged (BUG-030) | unchanged |
| ToolsController property purges | whole-property wipe (admin tool) | n/a (bulk purge, not row-level) | **documented** — out of row-audit scope; confirmations required at tool level |

Helper: `PaychargeLog::auditDeleted($rows, $reason, $user=null)` (new static) — writes full original row (docid/vno/vtype/sno/paycode/amtdr/amtcr/linkage/u_name/u_entdt) with reason + actor. Mirrors the proven BUG-030 insert shape; `$rows` accepts model/object/collection.

## Legacy report / utility comparison

| Legacy form | Laravel equivalent | Status |
|---|---|---|
| `FrmPOS` (main) | Pos screens (displaytable, posbillentry, settlemententry) | ✅ COMPLETE |
| `FrmPOSBillDeletion` | `deletebillxhr` + paychargelog (reason + audit) | ✅ COMPLETE (audit superior) |
| `FrmFomBillDeletion` | bill cancel flow (`fombilldetails status=Cancel` + paycharge audit) | ✅ COMPLETE |
| `FrMultiBill` (merge/split) | merge via mergedwith; **split partial** | ⚠️ PARTIAL (split UI missing) |
| `FrmUnSettledBillsInfo` | unsettled-bills report (routes/reporting.php + Pos) | ✅ COMPLETE |
| `FrmPOSRecycleData` (supervisor) | paychargelog audit + advance restore (superior, audited) | ✅ REPLACED (improved) |
| `FrmPOSBillModificationDatewise` | ❌ none | ⚠️ **MISSING** — edit POS bill items by date |
| `FrmPOSBillModificationItemGroupwise` | ❌ none | ⚠️ **MISSING** — edit POS bill items by item group |
| `FrmPOSSaleDataTransfer` | ❌ none | ⚠️ OBSOLETE/UNVERIFIED — outlet/period data transfer; likely covered by current live posting + re-post flow |
| `frmFacilityBillAdv` | Banquet/facility billing | ✅ covered by Banquet module |

## Findings / notes

1. **BUG-037 (HIGH, financial audit)**: 8 paycharge-delete sites were unlogged or incomplete; all now audited via shared helper. Live data: paycharge rows carry all logged columns (verified against live row). No duplicate-advance/duplicate-payment risk introduced — logging is insert-only.
2. Settlement re-posting is delete+recreate (idempotent). Server-side re-settle of an already-settled bill is not blocked, but rebuilds identical rows (no dup) and is now fully audited; UI blocks via TOUT flag.
3. KOT/Stock deletes (KotModal, Stock) are order-level, not ledger — intentionally not paycharge-audited.
4. ToolsController `resetOutletData` / property purges wipe POS tables entirely — business-level admin action; needs explicit confirmation flow (exists), row-level audit not applicable.
5. `FrmPOSBillModification*` — powerful financial-editing utilities; building them requires business confirmation (mission §23) before implementation.

## Recommended actions

1. ✅ DONE — audit all paycharge deletions (BUG-037).
2. ⏳ Business decision — bill-split UI (FrMultiBill parity) — P1/P2.
3. ⏳ Business decision — POS bill modification by date/item-group — P2 (financial editing tool, needs sign-off).
4. ⏳ Verify `FrmPOSSaleDataTransfer` semantics vs current architecture — P3 (likely obsolete).
