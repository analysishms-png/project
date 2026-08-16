# Analysis HMS — LEGACY TO LARAVEL MAP

> Old HMS (`.ai/HMS.bas`/`.text`, ~995K lines, 151 forms; `.ai/visahl.sql` schema) → current Laravel.
> The old HMS is a *reference*, not the source of truth — keep Laravel improvements.

---

## MODULE: Reservation
**OLD HMS:**
- Forms: FrmBookingInquiry, FrmReservationStatus, FrmBlock, FrmSoftBlock, FrmCancel, FrmPackage, FrmPlanPkg
- Tables: booking, grpbookingdetails, plan1, plandetails
- Business logic: booking inquiry, blocking (hard/soft), packages/plans, cancellation

**CURRENT LARAVEL:**
- Routes: `routes/property.php` / `routes/company.php` (reservation views)
- Controller: CompanyController (reservation methods)
- Model: Booking, GrpBookingDetails, PlanMast, BookingSource
- Views: `resources/views/property/*reservation*`
- Tables: booking, grpbookingdetails, plan1, plandetails

**STATUS:** COMPLETE
**MISSING:** (verify) room-blocking UI parity (FrmBlock/FrmSoftBlock), package assignment parity
**RECOMMENDED ACTION:** Verify block/soft-block + package flows; check advance capture on reservation (see Advance Deposit)

---

## MODULE: Advance Deposit
**OLD HMS:**
- Forms: frmAdvanceDepDialog
- Tables: paycharge, paychargelog
- Business logic: advance taken at reservation; transfer at check-in; traceable to folio; refund on cancellation

**CURRENT LARAVEL:**
- Controller: CompanyController (advance methods) + ToolsController (advance charge tool, `advancechargesubmit`)
- Models: Paycharge, PaychargeLog
- Views: `property/advancelistreceipt.blade.php`

**STATUS:** PARTIAL
**MISSING:** Mission §10 — **no advance→folio→settlement reconciliation/mismatch report** (ResAdv ₹1000 vs FolioAdv ₹0 vs PayChargeLog ₹1000)
**RECOMMENDED ACTION:** P0 — build read-only reconciliation report + mismatch detector; add restore/repost guard (check duplicates/settled/refunded/merged/cancelled before restore). No auto-mutation.

---

## MODULE: POS / KOT
**OLD HMS:**
- Forms: FrmPOS, FrmHotKey, FrmNCType, FrmChangeKitch, FrmPOSBillDeletion, FrmPOSRecycleData, FrmPOSSaleDataTransfer, FrmPOSBillModificationDatewise/ItemGroupwise, FrmWaiterMast, FrmDeliveryBoyMast
- Tables: sale1, sale2, kot, items, itemrate, paycharge

**CURRENT LARAVEL:**
- Routes: `routes/pointofsale.php`, `routes/pointofsale/kot.php`
- Controller: Pointofsale, Kot
- Models: Sale1, Sale2, Kot, Items, ItemRate
- Views: property POS views + prints; SaleBillPrintEvent (Reverb)

**STATUS:** COMPLETE (IMPROVED — websocket printing)
**MISSING:** (verify) NC-type master (FrmNCType), POS hotkey config (FrmHotKey), bill-modification tools parity (Datewise/ItemGroupwise)
**RECOMMENDED ACTION:** Verify NC + modification tools; confirm bill/payment/stock atomicity (ML-04)

---

## MODULE: Accounts (Suntran/Ledger)
**OLD HMS:**
- Forms: FrmACC, FrmAdjust, FrmMergeCharge, FrmRevMergeCharge, FrmSerialiseVr
- Tables: suntran (DocId, Sno, Vtype, VNo, Vdate, PartyCode, SunCode, Amount, BaseAmount, RevCode, RestCode…), ledger, subgroup, revmast

**CURRENT LARAVEL:**
- Service: `app/Services/AccountPosting.php`
- Models: Suntran, Ledger, SubGroup, Revmast
- Tables: suntran, ledger, subgroup, revmast — **schema matches legacy visahl.sql Suntran**

**STATUS:** COMPLETE
**MISSING:** (verify) merge/reverse-merge charge flows (FrmMergeCharge/FrmRevMergeCharge), voucher serialization, debit/credit balance validation (ML-07)
**RECOMMENDED ACTION:** Audit AccountPosting for posting integrity + audit trail; verify no silent financial deletion (ML-08)

---

## MODULE: Housekeeping / Room Status
**OLD HMS:**
- Forms: FrmHouseStatus, FrmItemIssuedOnCleaning
- Tables: roommast, roomcat

**CURRENT LARAVEL:**
- Controller: HouseKeeping
- Views: `property/housekeeping.blade.php`
- Models: RoomMast, RoomCat

**STATUS:** COMPLETE
**MISSING:** —
**RECOMMENDED ACTION:** Fix COLLATE mismatch perf issue (PERF-06, needs approval — schema)

---

## MODULE: Inventory / Purchase / Store
**OLD HMS:**
- Forms: FrmItemMast, FrmItemCatMast, FrmItemGroupMast, FrmConsumMast, FrmOPStock, FrmStockTransfer, FrmPurch, FrmItemIssuedOnCleaning
- Tables: itemmast, itemcatmast, itemgrp, stock, purch1, purch2, indent, porder, gin

**CURRENT LARAVEL:**
- Controller: InventoryController (5.9K lines)
- Models: Stock, Purch1, Purch2, Indent, Porder, Gin, ItemMast
- Tables: same names

**STATUS:** COMPLETE
**MISSING:** (verify) stock transfer atomicity (ML-06), opening stock flow (FrmOPStock)
**RECOMMENDED ACTION:** Verify stock issue/transfer/purchase posting flows + transaction safety

---

## MODULE: Banquet
**OLD HMS:**
- Forms: FrmEventMast, FrmVenueMast, FrmVenueFeat, FrmHallItemCatMast, FrmLocationMast, FrmLocationFacilities
- Tables: hallbook, hallsale1, hallsale2, hallstock, venueocc

**CURRENT LARAVEL:**
- Controller: Banquet (4.7K lines)
- Models: HallBook, HallSale1, HallSale2, VenueMast, VenueOcc
- Views: `property/banquetbillprint.blade.php`

**STATUS:** COMPLETE
**MISSING:** (verify) venue features/facilities masters parity
**RECOMMENDED ACTION:** Verify banquet booking→sales→stock flow; ensure atomicity (ML-05)

---

## MODULE: Tax / GST / E-Invoice
**OLD HMS:**
- Forms: FrmTaxMast, FrmTaxStruMast
- Tables: taxstru (CGSS/SGSS rates, slab operators Between/<=)

**CURRENT LARAVEL:**
- Helpers: calculateTax, getGstRate (slab logic matches legacy)
- Models: TaxStru, EInvoiceBill
- Views: `property/finance/tdsreport.blade.php`, Gstr1 helper

**STATUS:** COMPLETE
**MISSING:** (verify) e-invoice IRN flow end-to-end
**RECOMMENDED ACTION:** Verify GST rate selection matches legacy slab rules (ML-14)

---

## Remaining legacy forms — bulk mapping (MISSING/UNKNOWN — verify before building)

| Legacy form | Purpose | Laravel | Status |
|-------------|---------|---------|--------|
| FrmLostFound | Lost & found | ? | ⚠️ VERIFY |
| FrmDenomination | Cashier denomination | ? | ⚠️ VERIFY |
| FrmForExRec / FrmForeignExMast | Foreign exchange | ? | ⚠️ VERIFY |
| FrmMeterReading | Meter reading | ? | ⚠️ VERIFY |
| FrmGuestWakeUp | Wake-up calls | ? | ⚠️ VERIFY |
| FrmPaxDetails | Pax details | ? | ⚠️ VERIFY |
| FrmUnSettledBillsInfo | Unsettled bills | ? | ⚠️ VERIFY |
| FrmHotKey | POS hotkeys | ? | ⚠️ VERIFY |
| FrmComplaintMast / FrmComplaintClearing | Complaints | ? | ⚠️ VERIFY |
| FrmNAMessageA/B/C | Night-audit messages | nightauditlog/ | PARTIAL |
| FrmSMSEnviro / FrmSMSMultiType | SMS config/bulk | whatsapp logs | PARTIAL |
| FrmClaimEntry / FrmExpense | Expense claims | Finance | PARTIAL |
| FrmWaiterMast / FrmDeliveryBoyMast | POS staff masters | ? | ⚠️ VERIFY |
| FrmGuestAddObj / FrmGuestStat | Guest objectives/stats | ? | ⚠️ VERIFY |
| FrmRevenueWiseBudget | Budget MIS | Reporting | PARTIAL |

> ⚠️ VERIFY = scan Laravel routes/views/models first; classify MISSING/REPLACED/OBSOLETE; only build with business confirmation.
