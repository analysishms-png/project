# Analysis HMS — UI REFERENCE ↔ LARAVEL MAP

How to read: Reference screen (from `AnalysisHMS_Manual/App_Testing/`) → Laravel **route** (path in `routes/company.php`, 685 routes, path-named) → **blade** (`resources/views/property/`). Controller shown for the major modules. Route and blade names follow the same convention, so the route is the entry URL and the blade is the render.

Mapping rule: **never change a route, its parameters, or its permissions.** Redesign only the blade presentation.

## Dashboard
| Reference | Route | Blade | Controller |
|---|---|---|---|
| 00_Dashboard / homepage.png | `/company` | `companyreg.blade.php` | CompanyController |

## Finance
| Reference | Route | Blade | Controller |
|---|---|---|---|
| Balance_Sheet | `balancesheet` | `balancesheet.blade.php` | FinanceController |
| Bank_Reconciliation | `bankreconciliation` | `bankreconciliation.blade.php` | Finance\Transaction\BankReconcilation |
| Cheque_Cleared_Register | `chequeclearedregister` | `chequeclearedregister.blade.php` | FinanceController |
| Cheque_Not_Cleared_Register | `chequenotclearedregister` | `chequenotclearedregister.blade.php` | FinanceController |
| Detailed_Trial_Ledger | `detailedtrialledger` (+`/export`) | `detailedtrialledger.blade.php` | FinanceController |
| Group_Accounts | `groupaccouns` / `groupaccountentry` | `groupaccountentry*.blade.php` | FinanceController |
| Ledger_Accounts | `ledgeraccount` | `ledgeraccount*.blade.php` | FinanceController |
| Profit_Loss | `profitandloss` (check) | `profitandloss.blade.php` | FinanceController |
| TDS_Category | `tdscategory` (+`/edit`) | `tdscategory.blade.php` | Master\TdsCategoryController |
| TDS_Report | `tdsreport` | `tdsreport.blade.php` | FinanceController |
| Trail_Balance | `trialbalance` (check) | `trialbalance.blade.php` | FinanceController |
| Trail_Group | `trialgroup` (check) | `trialgroup.blade.php` | FinanceController |
| Verification_Dashboard | `verificationdashboard` | `verificationdashboard.blade.php` | Finance\Transaction\VoucherVerification |
| Voucher_Entry | `voucherentry` / `editvoucherentry/{docid}` | `voucherentry*.blade.php` | Finance\Transaction\VoucherEntry |
| FA_Parameter | `financeparameter` | `financeparameter.blade.php` | FinanceEnviro |

## Main Setup
| Reference | Route | Blade |
|---|---|---|
| Amenities_Master | `amenitiesmaster` (+`/items`) | `amenitiesmaster.blade.php` |
| Booking_Source | `bookingsource` (+`/export`) | `bookingsource.blade.php` + `updatebookingsource.blade.php` |
| Business_Source | `businesssource` (+`/export`) | `businesssource.blade.php` + `updatebusinesssource.blade.php` |
| Charge_Master | `chargemaster` (+`/export`) | `chargemaster.blade.php` + `updatechargemaster.blade.php` |
| Company_Master | `companymaster` | `companymaster.blade.php` + `companyupdate.blade.php` |
| Department | `departmaster` | `departmaster.blade.php` + `updatedepartmaster.blade.php` |
| Employee_Master | `employeemaster` (check) | `employeemaster.blade.php` |
| Events | `events` (+`/export`) | `events.blade.php` |
| FOM_Parameter | `fomparameter` | `fomparameter.blade.php` |
| Floor_Master | `floormaster` | `floormaster.blade.php` |
| Guest_Status | `gueststatus` (+`/export`) | `gueststatus.blade.php` + `updategueststatus.blade.php` |
| Inventory_Parameter | `invparameter` | `invparameter.blade.php` |
| Item_Entry | `itementery` (+`/export`) | `itementry.blade.php` |
| Item_List | `itemlists` / `itemlist/export` | `itemlists.blade.php` |
| Menu_Category | `menucategorys` (+`/export`) | `menucat*.blade.php` |
| Menu_Group | `menugroup` | `menugroup.blade.php` |
| Menu_Item | `menuitems` (+`menuitemcopy`) | `menuitem*.blade.php` |
| POS_Parameter | `posparameter` / `posgeneralparam` | `posparameter.blade.php` |
| Party_Master | `partymaster` | `partymaster.blade.php` + `updatepartymaster.blade.php` |
| Payment_Type | `paymaster` (+`/export`) | `paymaster.blade.php` |
| Plan_Master | `planmaster` | `planmaster.blade.php` + `updateplanmaster.blade.php` |
| Printing_Setup | `printsetup` (check) | `printsetup.blade.php` |
| Recipe_Master | `recipemaster` (+`deleterecipemaster/{sn}`) | `recipemaster.blade.php` + `updaterecipemaster.blade.php` |
| Room_Category | `roomcategory` (+print) | `roomcategory.blade.php` + `updateroomcategory.blade.php` |
| Room_Features | `roomfeature` (+print) | `roomfeature.blade.php` + `updateroomfeature.blade.php` |
| Room_Master | `roommaster` (+print) | `roommaster.blade.php` + `updateroommaster.blade.php` |
| Setup_Outlet | `outletsetup` | `outletsetup.blade.php` |
| Table_Master | `tablemaster` | `tablemaster.blade.php` |
| Tax_Master | `taxmaster` (+print) | `taxmaster.blade.php` + `updatetaxform.blade.php` |
| Tax_Structure | `taxstructure` (+print) | `taxstructure.blade.php` + `updatetaxstructure.blade.php` |
| User_Master | `usermaster` / `updateusermaster` | `usermaster.blade.php` + `updateusermaster.blade.php` |
| Venue_Master | `venuemaster` (+`deletevenuemaster/{sn}/{ucode}`) | `venuemaster.blade.php` |

## Reservations
| Reference | Route | Blade |
|---|---|---|
| Advance_Report | `advancecharge` / `advreconreport` / `advresreport` | `advancecharge.blade.php` etc. |
| Arrival_List | `arrivallist` | `arrivallist.blade.php` |
| Look_Up_Rooms | `lookuprooms` (+`lookuprromtype`) | `lookuprooms.blade.php` |
| New_Reservation | `reservation` / `openreservations` | `reservation*.blade.php` (JS-driven) |
| Occupancy_Forecast | `occupancyforecast` | `occupancyforecast.blade.php` |
| Reservation_List | `reservationlist` (+`updatereservation`) | `reservationlist.blade.php` + `updatereservation.blade.php` |

## Front Office
| Reference | Route | Blade |
|---|---|---|
| Cancel_Bill_Details | `cancelbills` | `cancelbills.blade.php` |
| Cashier_Report_FO | `cashierreport` | `cashierreport.blade.php` |
| Check_In_Register | `checkinreg` / `checkinlist` (+`/data`) | `checkinreg.blade.php` + `checkinlist.blade.php` |
| Check_Out_Register | `checkout` family | `checkout*.blade.php` |
| Company_Contribution | `contributionreport` (+print) | `contributionreport*.blade.php` |
| Complimentary_Report | `complimentaryreport` | `complimentaryreport.blade.php` |
| Expected_Check_Out | `expectedcheckout` | `expectedcheckout.blade.php` |
| FOM_Tax_Detail | `fomtaxdetail` | `fomtaxdetail.blade.php` |
| Front_Office_Operations | `walkin` / `prefilledwalkin` / `updatewalkin` | `walkin*.blade.php` |
| Guest_Trail | `guesttrail` (check) | `guesttrail.blade.php` |
| Occupancy_Report | `occupancyreport` | `occupancyreport.blade.php` |
| Room_Inventory | `roominventory` | `roominventory.blade.php` |
| Sale_Summary | `fomsalesummary` | `fomsalesummary.blade.php` |

## Housekeeping
| Reference | Route | Blade |
|---|---|---|
| Room_Status_Board | `roomstatusboard` / `roomstatus` | `roomstatusboard.blade.php` + `roomstatus.blade.php` |
| House_Keeping_Screen | `housekeepingscreen` / `housekeeping` | `housekeeping.blade.php` |
| Assignments | `assignments` (+`/print` `/view`) | `assignmentreport.blade.php` |
| Start_Cleaning / Room_Cleaning_Entry / Inspection | HK entry routes | `roomcleaningentry*.blade.php` (verify) |
| Damage_Entry / Damage_Report | `damageentry` / `damagereport` | `damagereport.blade.php` |
| Lost_and_Found | `lostfound*` (+register/print) | `lostfound*.blade.php` |
| Laundry_Send | `laundrysend` / `laundryreceive` | `laundrysend.blade.php` |
| House_Keeping_Report | `housekeepingstatusreport` / `housekeepingreport` | `housekeepingreport.blade.php` |

## Inventory
| Reference | Route | Blade |
|---|---|---|
| Indent | `indent` / `pendingindent` (+print) | `indent*.blade.php` |
| MR_Entry | `mrentry` / `pendingmr` (+`mrprinting/{docid}`) | `mrentry*.blade.php` |
| Purchase_Bill | `purchasebill` (+print) | `purchasebill*.blade.php` |
| Purchase_Order | `purchaseorder` / `pendingpurchaseorder` (+print) | `pendingpurchaseorder.blade.php` |
| Purchase_Register | `purchaseregister` | `purchaseregister.blade.php` |
| Quotation | `quotation` (+delete) | `quotation.blade.php` |
| Stock_Issue_Requisition | `requisitionstockissue*` (+print) | `requisitionstockissue*.blade.php` |
| Stock_Register | `stockregister` | `stockregister.blade.php` |
| Stock_Summary | `stocksummary` | `stocksummary.blade.php` |
| Stock_Transfer | `stocktransfer*` (+print) | `stocktransfer*.blade.php` |

## POS
| Reference | Route | Blade |
|---|---|---|
| Display_Table | `displaytable` | `pos_displaytable.blade.php` |
| KOT_Entry | `kotentry` (+`nckotreport`) | `kotentry.blade.php` |
| POS_Bill_Reprint | `billreprint` | `billreprint.blade.php` |
| Pending_KOT | `pendingkotreport` | `pendingkotreport.blade.php` |
| Sale_Bill_Entry | `salebillentry` / `pos_billentry` | `pos_billentry.blade.php` |
| Sales_Register | `pos_saleregister` / `salesregister` | `pos_saleregister.blade.php` |
| Settlement_Entry | `pos_settlemententry` / `salebillsettle` | `pos_settlemententry.blade.php` |
| Restaurant_Sale_Bill | `salebillsettle` / `hallbillsettle/{docid}` | `salebillsettle.blade.php` |

## Banquet
| Reference | Route | Blade |
|---|---|---|
| Advance_List | `banquetadvance` | `banquetadvance.blade.php` |
| Banquet_Billing | `banquetbilling` (+`banquetbillprint/{docid}`) | `banquetbilling.blade.php` |
| Banquet_Booking | `banquetbooking` (+`banquetupdate`) | `banquetbooking.blade.php` |
| Booking_Inquiry | `bookinginquiry` (+`deletebanquetenquiry/{inqno}`) | `banquetenquiry*.blade.php` |
| Daily_Function_Sheet | `dailyfunction` | `dailyfunction*.blade.php` |
| Item_Wise_Sales | `itemwisesalereport` | `itemwisesalereport.blade.php` |
| Performa_Invoice | `performainvoice` (+print) | `performainvoice*.blade.php` |
| Venue_Availability | `banquetavailability` (+daywise) | `banquetavailability*.blade.php` |

## Night Audit
| Reference | Route | Blade |
|---|---|---|
| Account_Posting | `accountposting` | `accountposting.blade.php` |
| Charges_Posting | `chargesposting` | `chargesposting.blade.php` |
| Charges_Remove_Log | `chremovelog` | `chremovelog.blade.php` |
| GSTR1 | `gstr1` (+`getGSTR1Data/{from}/{to}`) | `gstr1` views |
| Night_Audit_Log | `nightaudit` | `nightaudit*.blade.php` |
| Night_Audit_Process | `nightaudit2` | `nightaudit2.blade.php` |
| Reverse_Night_Audit | `reversenightaudit` (check) | `reversenightaudit.blade.php` |

## EXTRAs / Tickets
| Reference | Route | Blade |
|---|---|---|
| Channel_Enviro | `channelenviro` | `channelenviro.blade.php` |
| EInvoice_Parameter / Report | `einvoiceparameter` / `einvoicereport` | `einvoiceparameter.blade.php` |
| WhatsApp_Templates | `whatsappenviro` | `whatsappenviro.blade.php` |
| My_Tickets | `tools.myTickets` | `mytickets.blade.php` |

## Global chrome (all property pages)
| Piece | File | Notes |
|---|---|---|
| Layout | `property/layouts/main.blade.php` | includes header+sidebar+@yield('main-container')+footer |
| Header / topbar | `property/layouts/header.blade.php` (921 lines) | brand, hamburger, header icons, profile dropdown, notifications, **large inline JS** |
| Sidebar | `property/layouts/sidebar.blade.php` (255 lines) | `.nk-sidebar`, dynamic menu via `/getmainmenu` + `/fetchsubmenu` + `/fetchlastmenu`, mobile toggle |
| Footer scripts | `property/layouts/footer.blade.php` (824 lines) | BS4-era plugins (summernote-bs4, datepickers, moment, chart.js…) |
| Theme CSS | `public/admin/css/style.css` | **Bootstrap v4.1.3** Ekka theme (#7571f9), FontAwesome + custom fonts |
| Content wrapper | every page | `.content-body` > `.container-fluid` |

## Redesign constraint (critical)
- The property module is **Bootstrap 4.1.3** (Ekka). Hundreds of views + BS4-era plugins depend on BS4 attributes/classes (`data-toggle`, `data-dismiss`, `ml-*`, `.dropdown-menu-right`, etc.).
- **Do not swap the framework.** Redesign = Bootstrap-5-**style** design system layer (`hms.css`) + modernized chrome markup that preserves every ID/class/URL/JS hook + progressive per-screen restyling using the design system's component classes.
- Verified `package.json` has `bootstrap ^5.2.3` — used only by non-property areas; leave it.
