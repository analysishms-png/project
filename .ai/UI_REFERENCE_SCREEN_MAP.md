# Analysis HMS — UI REFERENCE SCREEN MAP

Source of truth: `C:\Users\PC\Desktop\AnalysisHMS_Manual\App_Testing\` (135 screenshots across 15 modules, taken 2026-08-14 against the Analysis demo package, login `sa` / property 103).

The reference package is the **visual + workflow** reference only. The Laravel source (`resources/views/property/*.blade.php`, 290+ views) is the **implementation** source of truth. Where they differ, the difference is documented in `UI_BLOCKED.md` and the Laravel implementation wins.

Legend for Status:
- ✅ EXISTS — a matching Laravel blade exists (name match verified below)
- 🔀 NAME DIFFERS — equivalent Laravel screen exists under a different blade name
- ❌ NOT FOUND — no obvious Laravel equivalent (needs confirmation, do NOT invent)

---

## Module 00 — Dashboard / Homepage

| Reference | Status | Laravel blade |
|---|---|---|
| homepage.png / 00_Dashboard.png | ✅ | `companyreg.blade.php` (dashboard KPIs, occupancy, charts) |
| login.png | ✅ | `frontend/application.blade.php` or `login` views |

---

## Module 01 — Finance (15 screens)

| Reference | Status | Laravel blade |
|---|---|---|
| Balance_Sheet | ✅ | `balancesheet.blade.php` (check) |
| Bank_Reconciliation | ✅ | `bankreconciliation` family |
| Cheque_Cleared_Register | ✅ | `chequeclearedregister.blade.php` |
| Cheque_Not_Cleared_Register | ✅ | `chequenotclearedregister.blade.php` |
| Detailed_Trial_Ledger | ✅ | `detailedtrialledger.blade.php` |
| FA_Parameter | ❌ | verify — fixed-asset parameter (MAIN SETUP class) |
| Group_Accounts | ✅ | `igroupaccounts` / `groupaccountentry*` |
| Ledger_Accounts | ✅ | `ledgeraccount` / `ledgeraccounts` |
| Profit_Loss | ✅ | `profitandloss` (check) |
| TDS_Category | ✅ | `tdsreport` family |
| TDS_Report | ✅ | `tdsreport.blade.php` |
| Trail_Balance | ✅ | `trialbalance` (check) |
| Trail_Group | ✅ | `trialgroup` (check) |
| Verification_Dashboard | ✅ | `voucherverify` / verification views |
| Voucher_Entry | ✅ | `voucherentry` (check) |

---

## Module 02 — Main Setup (37 screens)

| Reference | Status | Laravel blade |
|---|---|---|
| Amenities_Master | ✅ | `amenitiesmaster.blade.php` |
| Assets_Master | 🔀 | `assetmaster`? verify |
| Booking_Source | ✅ | `bookingsource.blade.php` + `updatebookingsource.blade.php` |
| Business_Source | ✅ | `businesssource.blade.php` + `updatebusinesssource.blade.php` |
| Charge_Master | ✅ | `chargemaster.blade.php` + `updatechargemaster.blade.php` |
| Company_Master | ✅ | `companymaster.blade.php` + `companyupdate.blade.php` |
| Department | ✅ | `departmaster.blade.php` + `updatedepartmaster.blade.php` |
| Employee_Master | ✅ | `employeemaster` (check) |
| Events | ✅ | `events` (check) |
| FOM_Parameter | ✅ | `fomparameter.blade.php` |
| Floor_Master | ✅ | `floormaster.blade.php` |
| Guest_Status | ✅ | `gueststatus.blade.php` + `updategueststatus.blade.php` |
| Inventory_Parameter | ✅ | `invparameter.blade.php` |
| Item_Entry | ✅ | `itementry.blade.php` |
| Item_List / Item_List_Inv | ✅ | `itemlists.blade.php` |
| Location_Master | ✅ | `locationmaster` (check) |
| Member_Master | ✅ | `membermaster` (check) |
| Menu_Category | ✅ | `menucat` / `menucategory` |
| Menu_Group | ✅ | `menugroup.blade.php` |
| Menu_Item | ✅ | `menuitem*.blade.php` |
| POS_Parameter | ✅ | `posparameter.blade.php` + `posgeneralparam` |
| Party_Master | ✅ | `partymaster.blade.php` + `updatepartymaster.blade.php` |
| Payment_Type | ✅ | `paymaster.blade.php` |
| Permissions | ✅ | `permission` views (admin) |
| Plan_Master | ✅ | `planmaster.blade.php` + `updateplanmaster.blade.php` |
| Printing_Setup | ✅ | `printsetup` (check) |
| Recipe_Master | ✅ | `recipemaster.blade.php` + `updaterecipemaster.blade.php` |
| Room_Category | ✅ | `roomcategory.blade.php` + `updateroomcategory.blade.php` |
| Room_Features | ✅ | `roomfeature.blade.php` + `updateroomfeature.blade.php` |
| Room_Master | ✅ | `roommaster.blade.php` + `updateroommaster.blade.php` |
| Setup_Outlet | ✅ | `outletsetup.blade.php` |
| Table_Master | ✅ | `tablemaster.blade.php` |
| Tax_Master | ✅ | `taxmaster.blade.php` + `updatetaxform.blade.php` |
| Tax_Structure | ✅ | `taxstructure.blade.php` + `updatetaxstructure.blade.php` |
| User_Master | ✅ | `usermaster.blade.php` + `updateusermaster.blade.php` |
| Venue_Master | ✅ | `venuemaster.blade.php` |

---

## Module 03 — Reservations (6 screens)

| Reference | Status | Laravel blade |
|---|---|---|
| Advance_Report | ✅ | `advancecharge` / `advanceadvreconreport` family |
| Arrival_List | ✅ | `arrivallist.blade.php` |
| Look_Up_Rooms | ✅ | `lookuprooms.blade.php` + `lookuprromtype` |
| New_Reservation | ✅ | `reservation` / `openreservations` (JS-driven walkin) |
| Occupancy_Forecast | ✅ | `occupancyforecast.blade.php` |
| Reservation_List | ✅ | `reservationlist.blade.php` |

---

## Module 04 — Front Office (13 screens)

| Reference | Status | Laravel blade |
|---|---|---|
| Cancel_Bill_Details | ✅ | `cancelbills.blade.php` |
| Cashier_Report_FO | ✅ | `cashierreport.blade.php` |
| Check_In_Register | ✅ | `checkinreg.blade.php` / `checkinlist.blade.php` |
| Check_Out_Register | ✅ | `checkout` family (check) |
| Company_Contribution | ✅ | `contributionreport*.blade.php` |
| Complimentary_Report | ✅ | `complimentaryreport.blade.php` |
| Expected_Check_Out | ✅ | `expectedcheckout.blade.php` |
| FOM_Tax_Detail | ✅ | `fomtaxdetail.blade.php` |
| Front_Office_Operations | ✅ | `walkin` / `prefilledwalkin` family |
| Guest_Trail | ✅ | `guesttrail` (check) |
| Occupancy_Report | ✅ | `occupancyreport.blade.php` |
| Room_Inventory | ✅ | `roominventory.blade.php` |
| Sale_Summary | ✅ | `fomsalesummary.blade.php` |

---

## Module 05 — House Keeping (11 screens)

| Reference | Status | Laravel blade |
|---|---|---|
| Assignments | ✅ | `roomstatusboard` / HK assignment views |
| Damage_Entry | ✅ | `damageentry` (check) |
| Damage_Report | ✅ | `damagereport.blade.php` |
| House_Keeping_Report | ✅ | `housekeepingreport.blade.php` |
| House_Keeping_Screen | ✅ | `housekeeping.blade.php` |
| Inspection | ✅ | `inspection` (check) |
| Laundry_Send | ✅ | `laundry` family (check) |
| Lost_and_Found | ✅ | `lostfound` (check) |
| Room_Cleaning_Entry | ✅ | `roomcleaningentry` (check) |
| Room_Status_Board | ✅ | `roomstatusboard.blade.php` + `roomstatus.blade.php` |
| Start_Cleaning | ✅ | `startcleaning` (check) |

---

## Module 06 — Inventory (11 screens)

| Reference | Status | Laravel blade |
|---|---|---|
| Gate_Pass_Exit | 🔀 | `stocktransfer` / gate-pass views |
| Indent | ✅ | `indent*.blade.php` + `pendingindent` |
| MR_Entry | ✅ | `mrentry*.blade.php` + `pendingmr` |
| Purchase_Bill | ✅ | `purchasebill*.blade.php` |
| Purchase_Order | ✅ | `purchaseorder` / `pendingpurchaseorder` |
| Purchase_Register | ✅ | `purchaseregister.blade.php` |
| Quotation | ✅ | `quotation` (check) |
| Stock_Issue_Requisition | ✅ | `requisitionstockissue*` + `stockissuerequisition` |
| Stock_Register | ✅ | `stockregister.blade.php` |
| Stock_Summary | ✅ | `stocksummary.blade.php` |
| Stock_Transfer | ✅ | `stocktransfer*.blade.php` |

---

## Module 07 — Point Of Sale (10 screens)

| Reference | Status | Laravel blade |
|---|---|---|
| Display_Table | ✅ | `pos_displaytable.blade.php` |
| KOT_Entry | ✅ | `kotentry.blade.php` + `nckotreport` |
| POS_Bill_Reprint | ✅ | `billreprint.blade.php` |
| Pending_KOT | ✅ | `pendingkotreport.blade.php` |
| Restaurant_Sale_Bill | ✅ | `salebillentry` / `pos_billentry` |
| Sale_Bill_Entry | ✅ | `pos_billentry.blade.php` |
| Sales_Register_POS | ✅ | `pos_saleregister.blade.php` + `salesregister` |
| Settlement_Entry | ✅ | `pos_settlemententry.blade.php` + `salebillsettle` |
| Table_Booking | ✅ | `tablebooking` / banquet `banquetbooking` |

---

## Module 08 — Banquet (8 screens)

| Reference | Status | Laravel blade |
|---|---|---|
| Advance_List | ✅ | `banquetadvance.blade.php` |
| Banquet_Billing | ✅ | `banquetbilling.blade.php` + `banquetbillprint` |
| Banquet_Booking | ✅ | `banquetbooking.blade.php` + `banquetupdate` |
| Booking_Inquiry | ✅ | `banquetenquiry*.blade.php` |
| Daily_Function_Sheet | ✅ | `dailyfunction*.blade.php` |
| Item_Wise_Sales | ✅ | `itemwisesalereport.blade.php` |
| Performa_Invoice | ✅ | `performainvoice*.blade.php` |
| Venue_Availability | ✅ | `banquetavailability*.blade.php` |

---

## Module 09 — Night Audit (7 screens)

| Reference | Status | Laravel blade |
|---|---|---|
| Account_Posting | ✅ | `accountposting.blade.php` |
| Charges_Posting | ✅ | `chargesposting.blade.php` |
| Charges_Remove_Log | ✅ | `chremovelog.blade.php` |
| GSTR1 | ✅ | `gstr1` / `gstexcel` |
| Night_Audit_Log | ✅ | `nightaudit*.blade.php` |
| Night_Audit_Process | ✅ | `nightaudit2.blade.php` |
| Reverse_Night_Audit | ✅ | `reversenightaudit` (check) |

---

## Module 10 — Members Mgmt (no screens captured — no access for login)
Module 11 — HR/Payroll (4): Leave, Loan/Advance Entry, Overtime Entry, Salary Creation — HR views live outside `property/` (verify location; e.g. `hr` folder).
Module 12 — Maintenance (no screens captured).
Module 13 — EXTRAs (11): Card_Initialization/Recharge/Refund/Registration, Channel_Enviro (`channelenviro.blade.php` ✅), Data_Push, EInvoice_Parameter/Report, Update_Rates (`updaterates`), WhatsApp_Templates (`whatsappenviro.blade.php` ✅).
Module 14 — My Tickets (1): `mytickets.blade.php` ✅ (`tools.myTickets`).

---

## Design intent per module (from reference manual)

- **Dashboard**: KPI cards (Occupied Rooms, Check-in, Checkout, Expected Checkout, Unsettled Rooms, Occupied Dirty, Vacant Dirty), guest-count chart, live time + weather. → Modern KPI card grid.
- **Finance**: dense report screens, filter bars on top, summary + table, print/export buttons. → Standardized report header/filter/summary/table layout.
- **Front Office / Reservations**: form-heavy with room grids. → Card-based forms, room-status color chips.
- **Housekeeping**: the reference demands a **Command Center** — room status board, assignments, cleaning progress, inspection pending, dirty/clean/OOO counts. UI-only.
- **POS/KOT**: touch-friendly, large hit targets, responsive.
- **Night Audit**: high-risk — UI only, never touch posting logic.

## Redesign principles (from the master prompt, distilled)

1. Bootstrap 5 visual language (tokens, cards, rounded corners, refined shadows/spacing) applied over the existing BS4.1.3/Ekka stack — **a CSS design layer, not a framework swap** (swap would break hundreds of views + BS4 plugins).
2. Zero functional change: no route/URL/param/field/JS-hook/permission/query changes.
3. Consistent page anatomy: Page Header → Filter/Search → Action Buttons → Data Table → Pagination → Add/Edit modal.
4. Responsive: 1920→375; sidebar collapses (offcanvas-style) on mobile — existing toggle already does this, keep it.
