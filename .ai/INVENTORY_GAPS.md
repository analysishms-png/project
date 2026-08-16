# INVENTORY — Gap Analysis (Laravel vs Legacy HMS)

> Date: 2026-08-16 · Verified against `app/Http/Controllers/InventoryController.php` (5.7K lines), `MainSetup/Inventory/RecipeMastController.php`, `routes/company.php`/`web.php`/`reporting.php`, live `analysis` DB, and `.ai/HMS.text`.

## 1. Module Coverage (Laravel)

| Legacy form | Laravel equivalent | Status |
|---|---|---|
| FrmItemMast / FrmItemMastRaw | `itemmast` master (Type/ItemType/Kitchen/ItemGroup/Unit/RestCode + HSN/BarCode) | ✅ COMPLETE |
| FrmItemCatMast / FrmItemCatRaw | `itemcatmast` | ✅ COMPLETE |
| FrmItemGroupMast / Raw | `itemgroup` (list/print/export/store) | ✅ COMPLETE |
| FrmConsumMast | `itemmast.Type`/`ItemType` (consumable vs food) | ✅ COVERED (no separate table) |
| FrmOPStock | `openingstock` + submit/update/delete | ✅ COMPLETE |
| FrmStockTransfer | `stocktransfer` + submit/update/delete + `itemstockval` | ✅ COMPLETE |
| FrmPurch | MR entry (`mrentry`/`mrentrysubmit`/update/delete, pending PO) → `purchasebill` (Purch1/Purch2/Sale2/Suntran/Ledger posting) | ✅ COMPLETE |
| — (legacy MR/goods-inward) | `gin` (MR entry header) | ✅ COMPLETE |
| Requisition | `requisitionslip` (create/update/verify/delete + print) | ✅ COMPLETE |
| Stock Issue vs Requisition | `stockissuerequisition` (+ update/delete + print) | ✅ COMPLETE |
| Indent | `indent` (submit/update/delete) | ✅ COMPLETE |
| — | `kitchenclosingstock` (submit/update/delete) | ✅ COMPLETE (Laravel extra) |
| — | `recipemaster` (RecipeMastController + print/export) | ✅ COMPLETE (Laravel extra) |
| — | `unitmast`, item category/group export | ✅ COMPLETE |
| FrmDeliveredItemDetail ("Not Delivered Order") | `pendingkotreport` (pending KOT) | ⚠️ PARTIAL — POS-side; legacy "not delivered order" detail not identical |

## 2. Financial Deletion Audit (this pass)

### BUG-039 — FIXED ✅ (HIGH — financial safety)
- **`deletepurchbill`**: soft-deleted Purch1/Purch2/Sale2/Stock (delflag='Y') but **hard-deleted Ledger rows with zero audit** (same defect class as BUG-037/038).
- **`purchasebillupdate`** and **`purchasebillsubmit`** (edit + submit re-post flows): hard-deleted **Suntran + Ledger** rows with zero audit before re-posting.
- **Fix** (project-convention patterns, both already used elsewhere):
  - Ledger → `LedgerLogService::store($ledgers, $user)` — exactly the VoucherEntry pattern (26 columns verified present on both `ledger` rows and `ledger_logs` table; live DB has 201,619 ledger rows).
  - Suntran → `Suntranlog` fill+save per row — exactly the SaleBill/Pointofsale merge pattern (23 Suntran cols vs 22 Suntranlog cols; only `delremark` dropped, which the guarded model already handles silently — same as existing usage).
- **Verified**: php -l clean; 33 tests pass; column mapping validated against real purchase-ledger row + live schemas.

### Remaining hard deletes (correctly NOT ledger-audited — verified non-financial)
- `mrentryupdate`/`deletemrentry` — Stock rows (MR edit re-posts stock; POS pass established KOT/Stock deletes are non-ledger). `deletemrentry` soft-deletes via delflag='Y' — good.
- `deletestocktransfer`, `deleteopeningstock`, `requisitionstockisuedelete`, `deleteindent` — Stock/Indent/Gin rows only (no ledger/paycharge impact).
- `deleteinv` (EnviroInventory config), kitchen-closing-stock delete — config/stock, no ledger.

## 3. Report / MIS Comparison

| Report | Laravel | Status |
|---|---|---|
| Stock Register | `stockregister` (PrintController) | ✅ EXISTS |
| Stock Report (Trade) | `stockreporttrade` | ✅ EXISTS |
| Stock Movement | `stockmovementreport` | ✅ EXISTS |
| Actual / LPR valuation | `actualdata` / `lprdata` / `fetchValuationData` | ✅ EXISTS |
| Purchase Amount | `getpurchaseamount` + submit/print | ✅ EXISTS |
| Delay Delivery | `delaydeliveryreport` + fetch/print | ✅ EXISTS |
| Receiver Pending Material | `receiverpendingmaterial` + print | ✅ EXISTS |
| Pending MR / Final Pending MR | `pendingmr` / `finalpendingmr` | ✅ EXISTS |
| Item Group / Category print + Excel | `printitemgroup` / `printitemcategory` + exports | ✅ EXISTS |
| Recipe Master print + Excel | `printrecipemaster` / `recipemaster/export` | ✅ EXISTS |
| Not Delivered Order (legacy FrmDeliveredItemDetail) | `pendingkotreport` | ⚠️ PARTIAL (POS pending-KOT only; legacy "not delivered" item-level detail not replicated) |

## 4. Business-Logic Notes (verified, no change)

- **MR entry** (`mrentrysubmit`): links pending POs (`mrcontradocId`), writes Gin + Stock, dedupe checks (`checkduplicatechalan`, `checkduplicatememinvno`), permission-guarded (revokeopen), part of the Purchase→Stock→Ledger flow.
- **Purchase bill** (`purchasebillsubmit`): VoucherPrefix-driven docid/vno (matches ADRES pattern), posts Purch1/Purch2 + Sale2 (tax lines) + Suntran (accounting) + Ledger, with delete+repost idempotency (now audited).
- **Stock transfer / requisition / indent**: permission-guarded, verified stock-value lookup (`itemstockval`), no ledger impact — store-internal movements only.
- **Opening stock**: `openingstocksubmit` writes Stock with OP type; delete is Stock-only.

## 5. Gaps Requiring Business Decision

| # | Gap | Notes |
|---|---|---|
| INV-01 | "Not Delivered Order" item-level report | Legacy POS report; Laravel has pending-KOT only. Confirm need before building. |
| INV-02 | Stock deletion during MR edit is unlogged (Stocklog exists but unused here) | Inventory-level; POS pass judged stock deletes non-ledger. Add Stocklog if business wants full stock-movement audit. |
| INV-03 | `stockregister`/valuation report filters vs legacy | Verify report equivalence during REPORTS/MIS parity pass. |

## 6. Definition of Done

- [x] Module traced (Laravel + legacy)
- [x] Financial deletions audited (BUG-039: LedgerLogService + Suntranlog)
- [x] Non-financial deletes verified (no ledger impact)
- [x] Report parity mapped
- [x] Tests pass (33/39), php -l clean, live-schema validated
- [ ] INV-01/02/03 — business decisions
- [ ] `.ai/INVENTORY_GAPS.md` — done
