<?php

use App\Http\Controllers\Fetch;
use App\Http\Controllers\Reporting;
use App\Http\Controllers\Pos;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\NightAudit\Reports\DailyReport;
use Illuminate\Support\Facades\Route;

// Open Report Bulk
Route::get('report_bulkcharge', [Reporting::class, 'report_bulkcharge']); 
// Fetch Bulk Data
Route::post('fetchpaydata', [Reporting::class, 'fetchpaydata'])->name('fetchpaydata');
// Open Trial Report
Route::get('guesttrail', [Reporting::class, 'guesttrail']);
// Fetch Trial Data
Route::post('fetchguesttraildata', [Reporting::class, 'fetchguesttraildata'])->name('fetchguesttraildata');
// Fetch Bill Data
Route::post('fetchdatabillprint', [Reporting::class, 'fetchdatabillprint'])->name('fetchdatabillprint');
// Fetch Re-Print Bill Data
Route::post('fetchbilldata', [Reporting::class, 'fetchbilldata'])->name('fetchbilldata');
// Bill Reprint Submit
Route::post('billreprintsubmit', [Reporting::class, 'billreprintsubmit'])->name('billreprintsubmit'); 
// Fetch Comp Names
Route::post('fetchcompname', [Reporting::class, 'fetchcompname'])->name('fetchcompname');
// Open Checkin Register
Route::get('checkinreg', [Reporting::class, 'checkinreg']);
// Fetch Checkin Reg Data
Route::post('fetchcheckinregdata', [Reporting::class, 'fetchcheckinregdata'])->name('fetchcheckinregdata');
// Open Cashier Report
Route::get('cashierreport', [Reporting::class, 'cashierreport']);
// Fetch Unique UserNames
Route::get('fetchusersname', [Reporting::class, 'fetchusersname']);
// Fetch Cashier Report Data
Route::post('fetchcashierdata', [Reporting::class, 'fetchcashierreportdata'])->name('fetchcashierdata');
// Fetch Cashier Report Data
Route::post('fetchcashierdata2', [Reporting::class, 'fetchcashierreportdata2'])->name('fetchcashierdata2');
// Fetch Bill Cancel
Route::get('CancelBillDet', [Reporting::class, 'cancelbills']);
// Fetch Bill Cancel Data
Route::post('fetchcancelbilldata', [Reporting::class, 'fetchcancelbilldata'])->name('fetchcancelbilldata');
// Fetch Buss Source 
Route::get('fetchbussource', [Reporting::class, 'fetchbussource'])->name('fetchbussource');
// Open Fom Tax Detail
Route::get('fomtaxdetail', [Reporting::class, 'fomtaxdetail']); 
Route::get('download-fom-tax-excel', [Reporting::class, 'exportFomTaxExcel']); 
// Fetch Unique Tax Names
Route::post('fetchtaxesnames', [Reporting::class, 'fetchtaxesnames'])->name('fetchtaxesnames');
// Fom Tax Data
Route::post('fetchfomtaxdata', [Reporting::class, 'fetchfomtaxdata'])->name('fetchfomtaxdata');
// Open Outlet List Data Sale Register
Route::get('possalesreg', [Pos::class, 'saleregister'])->name('possalesreg');
// Settlement Report Fetch
Route::post('settlereportfetch', [Pos::class, 'settlereportfetch'])->name('settlereportfetch');
// Fetch Sale Register Data
Route::post('saleregfetch', [Pos::class, 'saleregfetch'])->name('saleregfetch');
// Fetch Sale Register tables Data
Route::post('gettablesbyoutlet', [Pos::class, 'gettablesbyoutlet'])->name('gettablesbyoutlet');
// Occupancy Report
Route::get('occupancyreport', [Reporting::class, 'occupancyreport']);
// Fetch Occupancy Report Data
Route::post('fetchoocxhr', [Reporting::class, 'fetchoocxhr'])->name('fetchoocxhr');
// Open Item Wise Detail Page
Route::get('itemwisesale', [Reporting::class, 'itemwisesale'])->name('itemwisesale');
// Item Wise Report Data Fetch
Route::post('itemwiserepfetch', [Reporting::class, 'itemwiserepfetch'])->name('itemwiserepfetch');
// Print Item Wise Sale Report
Route::get('printitemwisesale', [Reporting::class, 'printItemWiseSale'])->name('printitemwisesale');
// Open Salebill Delete Page
Route::get('deletedunsettledbill', [Reporting::class, 'deletedunsettledbill'])->name('deletedunsettledbill');
// Sale Delete ANd Unsettled Data Fetch
Route::post('saledelxhr', [Reporting::class, 'saledelxhr'])->name('saledelxhr');
// Fetch Outelt Items
Route::post('outletitems', [Fetch::class, 'outletitems'])->name('outletitems');
// Fetch salesummary Items
Route::get('salesumm', [Reporting::class, 'salesummary'])->name('salesumm');
// Sale Delete ANd Unsettled Data Fetch
Route::post('salesummaryrpt', [Reporting::class, 'salesummaryrpt'])->name('salesummaryrpt');
// Arrival List Open
Route::get('/arrivallist', [Reporting::class, 'arrivallist'])->name('arrivallist');
Route::post('/arrivallistfetch', [Reporting::class, 'arrivallistfetch'])->name('arrivallistfetch');
// Open Daily Report
Route::get('dailyreport', [Reporting::class, 'dailyreport']);
// Fetch Daily Report
Route::post('dailyreportfetch', [DailyReport::class, 'dailyreportfetch'])->name('dailyreportfetch');
// Daily Report Print Page Open
Route::get('dailyreportprint', [DailyReport::class, 'dailyreportprint']);
// Open Look Up Room Type
Route::get('lookuprromtype', [Reporting::class, 'lookuprromtype']);
// Look Up Room Type Fetch
Route::post('lookuproomtypefetch', [Reporting::class, 'lookuproomtypefetch'])->name('lookuproomtypefetch');
//open nc kot report
Route::get('nckotreport', [Reporting::class, 'nckotreport']);
//fetch nc kot report
Route::post('nckotreportfetch', [Reporting::class, 'nckotreportfetch'])->name('nckotreportfetch');
//open Advance reservation report
Route::get('advresreport', [Reporting::class, 'advresreport']);
//fetch advance reservation report
Route::post('advresreportfetch', [Reporting::class, 'advresreportfetch'])->name('advresreportfetch');
//open Advance/Folio reconciliation report (read-only diagnostic)
Route::get('advreconreport', [Reporting::class, 'advreconreport'])->name('advreconreport');
//fetch Advance/Folio reconciliation data
Route::post('advreconreportfetch', [Reporting::class, 'advreconreportfetch'])->name('advreconreportfetch');
//fetch Advance/Folio reconciliation detail (per reservation)
Route::post('advreconreportdetail', [Reporting::class, 'advreconreportdetail'])->name('advreconreportdetail');
//safe restore/re-post of missing folio advance (guarded, audited, never duplicates)
Route::post('advreconrestore', [Reporting::class, 'advreconrestore'])->name('advreconrestore');
//Front Office mismatch diagnostics (read-only)
Route::get('fodiagnostics', [Reporting::class, 'fodiagnostics'])->name('fodiagnostics');
Route::post('fodiagnosticsfetch', [Reporting::class, 'fodiagnosticsfetch'])->name('fodiagnosticsfetch');
//open Expected checkout report
Route::get('expectedcheckout', [Reporting::class, 'expectedcheckout']);
//fetch Expected checkout report
Route::post('expectedcheckoutfetch', [Reporting::class, 'expectedcheckoutfetch'])->name('expectedcheckoutfetch');
//open FOCC report
Route::get('foccreport', [Reporting::class, 'focc_report']);
// Fetch Focc Amount fetch
Route::post('foccamount', [Reporting::class, 'foccamount'])->name('foccamount');
//fetch FOCC report
Route::post('focc_reportfetch', [Reporting::class, 'focc_reportfetch'])->name('focc_reportfetch');
// Focc Report Print Page Open
Route::get('foccreportprint', [Reporting::class, 'foccreportprint']);
//open Pending KOT report
Route::get('pendingkotreport', [Reporting::class, 'pendingkotreport']);
//fetch Pending KOT report
Route::post('pendingkotreportfetch', [Reporting::class, 'pendingkotreportfetch'])->name('pendingkotreportfetch');
//open kot wise detail
Route::get('kotwisedetail', [Reporting::class, 'kotwisedetail']);
//fetch kot wise detail
Route::post('kotwisedetailfetch', [Reporting::class, 'kotwisedetailfetch'])->name('kotwisedetailfetch');
//open room inventory
Route::get('roominventory', [Reporting::class, 'roominventory']);
//fetch room inventory
Route::post('roominventoryfetch', [Reporting::class, 'roominventoryfetch'])->name('roominventoryfetch');
//open Void Bills
Route::get('voidbills', [Reporting::class, 'voidbills']);
//fetch Void Bills
Route::post('voidbillsfetch', [Reporting::class, 'voidbillsfetch'])->name('voidbillsfetch');
// Open FOM Sale Summary
Route::get('fomsalesummary', [Reporting::class, 'fomsalesummary'])->name('fomsalesummary');
// Fetch Fom Sale Summary
Route::post('fetchfomsalesummary', [Reporting::class, 'fetchfomsalesummary'])->name('fetchfomsalesummary');
// Open Company Contribuition Report
Route::get('contributionreport', [Reporting::class, 'contributionreport'])->name('contributionreport');
// Fetch Contribution Report
Route::post('fetchcontribuition', [Reporting::class, 'fetchcontribuition'])->name('fetchcontribuition');
// Open Contribuition Report Print
Route::get('contribuitionreportprint', function () {
    $permission = revokeopen(141313);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
    return view('property.contributionreportprint');
});

// Menu Item Rate Report 
Route::get('menuitemrate', [Reporting::class, 'menuitemratereport'])->name('menuitemrate');   
// Fetch Item Groups by Outlet
Route::post('fetchitemgroupsbyoutlet', [Reporting::class, 'fetchitemgroupsbyoutlet'])->name('fetchitemgroupsbyoutlet');
// Fetch Menu Item Rate Report Data
Route::post('fetchmenuitemratereport', [Reporting::class, 'fetchmenuitemratereport'])->name('fetchmenuitemratereport');
// Update Menu Items
Route::post('updatemenuitems', [Reporting::class, 'updatemenuitems'])->name('updatemenuitems');
// Update Item Rates
Route::post('updateitemrates', [Reporting::class, 'updateitemrates'])->name('updateitemrates');

// Delay Delivery Report
Route::get('delaydeliveryreport', [InventoryController::class, 'delayDeliveryReport'])->name('delaydeliveryreport');
Route::post('delaydeliveryreport/fetch', [InventoryController::class, 'delayDeliveryReportFetch'])->name('delaydeliveryreport.fetch');
Route::get('printdelaydeliveryreport', [InventoryController::class, 'printdelaydeliveryreport'])->name('printdelaydeliveryreport');
 // Receiver / Pending Material Report placeholder
Route::get('receiverpendingmaterial', [InventoryController::class, 'receiverPendingMaterial'])->name('receiverpendingmaterial');
Route::get('printreceiverpendingmaterial',[InventoryController::class, 'printReceiverPendingMaterial'])->name('printreceiverpendingmaterial');

// Room Wise Amenities Report
Route::get('roomwiseamenitiesreport', [\App\Http\Controllers\HouseKeeping::class, 'roomwiseamenitiesreport'])->name('roomwiseamenitiesreport');
Route::post('roomwiseamenitiesreportfetch', [\App\Http\Controllers\HouseKeeping::class, 'roomwiseamenitiesreportfetch'])->name('roomwiseamenitiesreportfetch');

// Amenities Report (Item wise Usage, Cost, Rooms, Pax)
Route::get('amenitiesreport', [\App\Http\Controllers\HouseKeeping::class, 'amenitiesreport'])->name('amenitiesreport');
Route::post('amenitiesreportfetch', [\App\Http\Controllers\HouseKeeping::class, 'amenitiesreportfetch'])->name('amenitiesreportfetch');

// Assignment Report fetch (GET route already in company.php)
Route::post('assignmentreportfetch', [\App\Http\Controllers\HouseKeeping::class, 'assignmentreportfetch'])->name('assignmentreportfetch');

// ─── Reward Point Report ──────────────────────────────────────────────────────
Route::get('rewardpointreport',           [Reporting::class, 'rewardpointreport'])->name('rewardpointreport');
Route::post('fetchrewardpointreport',     [Reporting::class, 'fetchrewardpointreport'])->name('fetchrewardpointreport');
Route::get('fetchrewardmobilenumbers',    [Reporting::class, 'fetchrewardmobilenumbers'])->name('fetchrewardmobilenumbers');

// Occupancy Forecast Report
Route::get('occupancyforecast', [Reporting::class, 'occupancyforecast'])->name('occupancyforecast');
Route::post('fetchoccupancyforecast', [Reporting::class, 'fetchoccupancyforecast'])->name('fetchoccupancyforecast');
// Room Management reconciliation (read-only diagnostics)
Route::get('roomrecon', [Reporting::class, 'roomrecon'])->name('roomrecon');
Route::post('roomreconfetch', [Reporting::class, 'roomreconfetch'])->name('roomreconfetch');
// Occupancy Forecast Print (DomPDF)
Route::get('printoccupancyforecast', [Reporting::class, 'printoccupancyforecast'])->name('printoccupancyforecast');
// Occupancy Forecast Excel Export
Route::get('exportoccupancyforecast', [Reporting::class, 'exportoccupancyforecast'])->name('exportoccupancyforecast');

// ─── GST Consolidated Register (all-source unified tax view) ─────────────────
Route::get('gstconsolidatedregister', [Reporting::class, 'gstconsolidatedregister'])->name('gstconsolidatedregister');
Route::post('gstconsolidatedregisterfetch', [Reporting::class, 'gstconsolidatedregisterfetch'])->name('gstconsolidatedregisterfetch');
Route::get('printgstconsolidatedregister', [Reporting::class, 'printgstconsolidatedregister'])->name('printgstconsolidatedregister');
Route::get('exportgstconsolidatedregister', [Reporting::class, 'exportgstconsolidatedregister'])->name('exportgstconsolidatedregister');

// ─── Night Audit Reconciliation Report ────────────────────────────────────────
Route::get('nightauditrecon', [Reporting::class, 'nightauditrecon'])->name('nightauditrecon');
Route::post('nightauditreconfetch', [Reporting::class, 'nightauditreconfetch'])->name('nightauditreconfetch');

// ─── AMR Morning Report ───────────────────────────────────────────────────────
Route::get('amrmorningreport', [Reporting::class, 'amrmorningreport'])->name('amrmorningreport');
Route::post('amrmorningreportfetch', [Reporting::class, 'amrmorningreportfetch'])->name('amrmorningreportfetch');

// ─── Checked-In Guest Detail Report ──────────────────────────────────────────
Route::get('checkedinguestdetail', [Reporting::class, 'checkedinguestdetail'])->name('checkedinguestdetail');
Route::post('checkedinguestdetailfetch', [Reporting::class, 'checkedinguestdetailfetch'])->name('checkedinguestdetailfetch');

// ─── Room-Wise Room Revenue Report ──────────────────────────────────────────
Route::get('roomwiseroomrevenue', [Reporting::class, 'roomwiseroomrevenue'])->name('roomwiseroomrevenue');
Route::post('roomwiseroomrevenuefetch', [Reporting::class, 'roomwiseroomrevenuefetch'])->name('roomwiseroomrevenuefetch');

// ─── Form C — Foreign Guest Registration (Compliance) ───────────────────────
Route::get('formcreport', [Reporting::class, 'formcreport'])->name('formcreport');
Route::post('formcreportfetch', [Reporting::class, 'formcreportfetch'])->name('formcreportfetch');

// ─── FO Settlement Report (SettleRep parity) ────────────────────────────────
Route::get('fosettlereport', [Reporting::class, 'fosettlereport'])->name('fosettlereport');
Route::post('fosettlereportfetch', [Reporting::class, 'fosettlereportfetch'])->name('fosettlereportfetch');

// ─── Reservation Status Dashboard ────────────────────────────────────────────
Route::get('reservationstatus', [Reporting::class, 'reservationstatus'])->name('reservationstatus');
Route::post('reservationstatusfetch', [Reporting::class, 'reservationstatusfetch'])->name('reservationstatusfetch');

// ─── Room Rent Audit Report ─────────────────────────────────────────────────
Route::get('roomrentaudit', [Reporting::class, 'roomrentaudit'])->name('roomrentaudit');
Route::post('roomrentauditfetch', [Reporting::class, 'roomrentauditfetch'])->name('roomrentauditfetch');

   // Movement List
   Route::get('movementlist', [Reporting::class, 'movementlist'])->name('movementlist');
   Route::post('movementlistfetch', [Reporting::class, 'movementlistfetch'])->name('movementlistfetch');
   Route::get('printmovementlist', [Reporting::class, 'printmovementlist'])->name('printmovementlist');

   // Discount Register
   Route::get('discountregister', [Reporting::class, 'discountregister'])->name('discountregister');
   Route::post('discountregisterfetch', [Reporting::class, 'discountregisterfetch'])->name('discountregisterfetch');

   // Food Cost Report
   Route::get('foodcost', [Reporting::class, 'foodcost'])->name('foodcost');
   Route::post('foodcostfetch', [Reporting::class, 'foodcostfetch'])->name('foodcostfetch');

   // Cover Analysis
   Route::get('coveranalysis', [Reporting::class, 'coveranalysis'])->name('coveranalysis');
   Route::post('coveranalysisfetch', [Reporting::class, 'coveranalysisfetch'])->name('coveranalysisfetch');

   // Waiter-Wise Sale
   Route::get('waitersale', [Reporting::class, 'waitersale'])->name('waitersale');
   Route::post('waitersalefetch', [Reporting::class, 'waitersalefetch'])->name('waitersalefetch');

   // Cashier Settlement
   Route::get('cashiersettlement', [Reporting::class, 'cashiersettlement'])->name('cashiersettlement');
   Route::post('cashiersettlementfetch', [Reporting::class, 'cashiersettlementfetch'])->name('cashiersettlementfetch');

   // Guest Payments
   Route::get('guestpayments', [Reporting::class, 'guestpayments'])->name('guestpayments');
   Route::post('guestpaymentsfetch', [Reporting::class, 'guestpaymentsfetch'])->name('guestpaymentsfetch');

   // Room Change History
   Route::get('roomchangehistory', [Reporting::class, 'roomchangehistory'])->name('roomchangehistory');
   Route::post('roomchangehistoryfetch', [Reporting::class, 'roomchangehistoryfetch'])->name('roomchangehistoryfetch');

   // Guest Trial Balance
   Route::get('guesttrialbalance', [Reporting::class, 'guesttrialbalance'])->name('guesttrialbalance');
   Route::post('guesttrialbalancefetch', [Reporting::class, 'guesttrialbalancefetch'])->name('guesttrialbalancefetch');

   // Pending KOT Report
   Route::get('pendingkotreport', [Reporting::class, 'pendingkotreport'])->name('pendingkotreport');
   Route::post('pendingkotreportfetch', [Reporting::class, 'pendingkotreportfetch'])->name('pendingkotreportfetch');

   // Room Nights Analysis
   Route::get('roomnights', [Reporting::class, 'roomnights'])->name('roomnights');
   Route::post('roomnightsfetch', [Reporting::class, 'roomnightsfetch'])->name('roomnightsfetch');

   // Check-Out Register
   Route::get('checkoutregister', [Reporting::class, 'checkoutregister'])->name('checkoutregister');
   Route::post('checkoutregisterfetch', [Reporting::class, 'checkoutregisterfetch'])->name('checkoutregisterfetch');

   // Advance Reconciliation Report
   Route::get('advancereconcil', [Reporting::class, 'advancereconcil'])->name('advancereconcil');
   Route::post('advancereconcilfetch', [Reporting::class, 'advancereconcilfetch'])->name('advancereconcilfetch');

   // Registered Guest Detail
   Route::get('registeredguestdetail', [Reporting::class, 'registeredguestdetail'])->name('registeredguestdetail');
   Route::post('registeredguestdetailfetch', [Reporting::class, 'registeredguestdetailfetch'])->name('registeredguestdetailfetch');

   // Edited Bills Report
   Route::get('editedbills', [Reporting::class, 'editedbills'])->name('editedbills');
   Route::post('editedbillsfetch', [Reporting::class, 'editedbillsfetch'])->name('editedbillsfetch');

   // KOT Edit/Delete Log
   Route::get('koteditdeletelog', [Reporting::class, 'koteditdeletelog'])->name('koteditdeletelog');
   Route::post('koteditdeletelogfetch', [Reporting::class, 'koteditdeletelogfetch'])->name('koteditdeletelogfetch');

   // Revenue Analysis
   Route::get('revenueanalysis', [Reporting::class, 'revenueanalysis'])->name('revenueanalysis');
   Route::post('revenueanalysisfetch', [Reporting::class, 'revenueanalysisfetch'])->name('revenueanalysisfetch');

   // Guest Charges MIS
   Route::get('guestchargesmis', [Reporting::class, 'guestchargesmis'])->name('guestchargesmis');
   Route::post('guestchargesmisfetch', [Reporting::class, 'guestchargesmisfetch'])->name('guestchargesmisfetch');

   // Extra Charges During Stay
   Route::get('extrachargesduringstay', [Reporting::class, 'extrachargesduringstay'])->name('extrachargesduringstay');
   Route::post('extrachargesduringstayfetch', [Reporting::class, 'extrachargesduringstayfetch'])->name('extrachargesduringstayfetch');

   // Party Outstanding Report (P1)
   Route::get('partyoutstanding', [Reporting::class, 'partyoutstanding'])->name('partyoutstanding');
   Route::post('partyoutstandingfetch', [Reporting::class, 'partyoutstandingfetch'])->name('partyoutstandingfetch');

   // Reservation Status Arrival (P1)
   Route::get('reservstatusarrival', [Reporting::class, 'reservstatusarrival'])->name('reservstatusarrival');
   Route::post('reservstatusarrivalfetch', [Reporting::class, 'reservstatusarrivalfetch'])->name('reservstatusarrivalfetch');

   // Reservation Status In-House (P1)
   Route::get('reservstatusinhouse', [Reporting::class, 'reservstatusinhouse'])->name('reservstatusinhouse');
   Route::post('reservstatusinhousefetch', [Reporting::class, 'reservstatusinhousefetch'])->name('reservstatusinhousefetch');

   // Plan Report (P2)
   Route::get('planreport', [Reporting::class, 'planreport'])->name('planreport');
   Route::post('planreportfetch', [Reporting::class, 'planreportfetch'])->name('planreportfetch');

   // Guest Wise Analysis (P2)
   Route::get('guestwiseanalysis', [Reporting::class, 'guestwiseanalysis'])->name('guestwiseanalysis');
   Route::post('guestwiseanalysisfetch', [Reporting::class, 'guestwiseanalysisfetch'])->name('guestwiseanalysisfetch');

   // Guest Wise Revenue (P2)
   Route::get('guestwiserevenue', [Reporting::class, 'guestwiserevenue'])->name('guestwiserevenue');
   Route::post('guestwiserevenuefetch', [Reporting::class, 'guestwiserevenuefetch'])->name('guestwiserevenuefetch');

   // Revenue Analysis (P2)
   Route::get('revenueanalysis2', [Reporting::class, 'revenueanalysis2'])->name('revenueanalysis2');
   Route::post('revenueanalysis2fetch', [Reporting::class, 'revenueanalysis2fetch'])->name('revenueanalysis2fetch');

   // Gratuity Report (P2)
   Route::get('gratuityreport', [Reporting::class, 'gratuityreport'])->name('gratuityreport');
   Route::post('gratuityreportfetch', [Reporting::class, 'gratuityreportfetch'])->name('gratuityreportfetch');

   // Cashier Collection MIS (P2)
   Route::get('cashiercollectionmis', [Reporting::class, 'cashiercollectionmis'])->name('cashiercollectionmis');
   Route::post('cashiercollectionmisfetch', [Reporting::class, 'cashiercollectionmisfetch'])->name('cashiercollectionmisfetch');

   // Account Checklist (P2)
   Route::get('accountchecklist', [Reporting::class, 'accountchecklist'])->name('accountchecklist');
   Route::post('accountchecklistfetch', [Reporting::class, 'accountchecklistfetch'])->name('accountchecklistfetch');

   // Delivery Status (P2)
   Route::get('deliverystatus', [Reporting::class, 'deliverystatus'])->name('deliverystatus');
   Route::post('deliverystatusfetch', [Reporting::class, 'deliverystatusfetch'])->name('deliverystatusfetch');

   // Function Wise Item Detail (P2)
   Route::get('functionwiseitemdetail', [Reporting::class, 'functionwiseitemdetail'])->name('functionwiseitemdetail');
   Route::post('functionwiseitemdetailfetch', [Reporting::class, 'functionwiseitemdetailfetch'])->name('functionwiseitemdetailfetch');

   // Item Wise Sale Hall (P2)
   Route::get('itemwisesalehall', [Reporting::class, 'itemwisesalehall'])->name('itemwisesalehall');
   Route::post('itemwisesalehallfetch', [Reporting::class, 'itemwisesalehallfetch'])->name('itemwisesalehallfetch');

   // HT Cashier Summary (P2)
   Route::get('htcashiersumm', [Reporting::class, 'htcashiersumm'])->name('htcashiersumm');
   Route::post('htcashiersummfetch', [Reporting::class, 'htcashiersummfetch'])->name('htcashiersummfetch');

   // Bill Wise Adjustment Report (P2)
   Route::get('billwiseadjustment', [Reporting::class, 'billwiseadjustment'])->name('billwiseadjustment');
   Route::post('billwiseadjustmentfetch', [Reporting::class, 'billwiseadjustmentfetch'])->name('billwiseadjustmentfetch');

// ═══════════════════════════════════════════════════════════════════════════
// MISSING REPORTS — POS, Banquet, Front Office (added by AI migration 2)
// ═══════════════════════════════════════════════════════════════════════════

// KOT Rate Change Report
Route::get('kotratechange', [Reporting::class, 'kotratechange'])->name('kotratechange');
Route::post('kotratechangefetch', [Reporting::class, 'kotratechangefetch'])->name('kotratechangefetch');

// Extra Charges During Stay
Route::get('extrachargesduringstay', [Reporting::class, 'extrachargesduringstay2'])->name('extrachargesduringstay2');
Route::post('extrachargesduringstayfetch2', [Reporting::class, 'extrachargesduringstayfetch2'])->name('extrachargesduringstayfetch2');

// FOM Bill Change Report
Route::get('fombillchangereport', [Reporting::class, 'fombillchangereport'])->name('fombillchangereport');
Route::post('fombillchangereportfetch', [Reporting::class, 'fombillchangereportfetch'])->name('fombillchangereportfetch');

// KOT Edit/Delete Log
Route::get('koteditdeletelog2', [Reporting::class, 'koteditdeletelog2'])->name('koteditdeletelog2');
Route::post('koteditdeletelog2fetch', [Reporting::class, 'koteditdeletelog2fetch'])->name('koteditdeletelog2fetch');

// Liquor Sale Report
Route::get('liquorsalerep', [Reporting::class, 'liquorsalerep'])->name('liquorsalerep');
Route::post('liquorsalerepfetch', [Reporting::class, 'liquorsalerepfetch'])->name('liquorsalerepfetch');

// Table Wise Sale
Route::get('tablewisesale', [Reporting::class, 'tablewisesale'])->name('tablewisesale');
Route::post('tablewisesalefetch', [Reporting::class, 'tablewisesalefetch'])->name('tablewisesalefetch');

// Order Detail Report
Route::get('orderdetailreport', [Reporting::class, 'orderdetailreport'])->name('orderdetailreport');
Route::post('orderdetailreportfetch', [Reporting::class, 'orderdetailreportfetch'])->name('orderdetailreportfetch');

// Sale Register Per Cover
Route::get('saleregpercover', [Reporting::class, 'saleregpercover'])->name('saleregpercover');
Route::post('saleregpercoverfetch', [Reporting::class, 'saleregpercoverfetch'])->name('saleregpercoverfetch');

// Tally POS Report
Route::get('tallyposreport', [Reporting::class, 'tallyposreport'])->name('tallyposreport');
Route::post('tallyposreportfetch', [Reporting::class, 'tallyposreportfetch'])->name('tallyposreportfetch');

// Company Wise Sale (Hall)
Route::get('companywisesalehall', [Reporting::class, 'companywisesalehall'])->name('companywisesalehall');
Route::post('companywisesalehallfetch', [Reporting::class, 'companywisesalehallfetch'])->name('companywisesalehallfetch');

// Excess Consumption
Route::get('excessconsumption', [Reporting::class, 'excessconsumption'])->name('excessconsumption');
Route::post('excessconsumptionfetch', [Reporting::class, 'excessconsumptionfetch'])->name('excessconsumptionfetch');

// Production Report
Route::get('productionreport', [Reporting::class, 'productionreport'])->name('productionreport');
Route::post('productionreportfetch', [Reporting::class, 'productionreportfetch'])->name('productionreportfetch');

// Open Item Sale
Route::get('openitemsale', [Reporting::class, 'openitemsale'])->name('openitemsale');
Route::post('openitemsalefetch', [Reporting::class, 'openitemsalefetch'])->name('openitemsalefetch');

// ABC Analysis (Guest)
Route::get('abcanalysis', [Reporting::class, 'abcanalysis'])->name('abcanalysis');
Route::post('abcanalysisfetch', [Reporting::class, 'abcanalysisfetch'])->name('abcanalysisfetch');

// ABC Analysis (Sales)
Route::get('abcanalysis sale', [Reporting::class, 'abcanalysisSale'])->name('abcanalysisSale');
Route::post('abcanalysissalefetch', [Reporting::class, 'abcanalysisSaleFetch'])->name('abcanalysissalefetch');

// Cancellation Letter
Route::get('cancellletter', [Reporting::class, 'cancellletter'])->name('cancellletter');
Route::post('cancellletterdata', [Reporting::class, 'cancellletterdata'])->name('cancellletterdata');

// Confirmation Letter
Route::get('confirletter', [Reporting::class, 'confirletter'])->name('confirletter');
Route::post('confirletterdata', [Reporting::class, 'confirletterdata'])->name('confirletterdata');

// Guest Charges MIS
Route::get('guestchargesmis2', [Reporting::class, 'guestchargesmis2'])->name('guestchargesmis2');
Route::post('guestchargesmis2fetch', [Reporting::class, 'guestchargesmis2fetch'])->name('guestchargesmis2fetch');

// Membership Ledger
Route::get('memled', [Reporting::class, 'memled'])->name('memled');
Route::post('memledfetch', [Reporting::class, 'memledfetch'])->name('memledfetch');

// Membership Tax Report
Route::get('memtaxreport', [Reporting::class, 'memtaxreport'])->name('memtaxreport');
Route::post('memtaxreportfetch', [Reporting::class, 'memtaxreportfetch'])->name('memtaxreportfetch');

// Pay Slip
Route::get('payslip', [Reporting::class, 'payslip'])->name('payslip');
Route::post('payslipfetch', [Reporting::class, 'payslipfetch'])->name('payslipfetch');

// PF Statement
Route::get('pfstatement', [Reporting::class, 'pfstatement'])->name('pfstatement');
Route::post('pfstatementfetch', [Reporting::class, 'pfstatementfetch'])->name('pfstatementfetch');

// Payroll Register
Route::get('payrollreg', [Reporting::class, 'payrollreg'])->name('payrollreg');
Route::post('payrollregfetch', [Reporting::class, 'payrollregfetch'])->name('payrollregfetch');

// Daily Diet
Route::get('dailydiet', [Reporting::class, 'dailydiet'])->name('dailydiet');
Route::post('dailydietfetch', [Reporting::class, 'dailydietfetch'])->name('dailydietfetch');

// Annexure
Route::get('annexure', [Reporting::class, 'annexure'])->name('annexure');
Route::post('annexurefetch', [Reporting::class, 'annexurefetch'])->name('annexurefetch');

// Room Nights
Route::get('roomnights', [Reporting::class, 'roomnights'])->name('roomnights');
Route::post('roomnightsfetch', [Reporting::class, 'roomnightsfetch'])->name('roomnightsfetch');

// Card Status Report
Route::get('cardstatusreport', [Reporting::class, 'cardstatusreport'])->name('cardstatusreport');
Route::post('cardstatusreportfetch', [Reporting::class, 'cardstatusreportfetch'])->name('cardstatusreportfetch');

// ═══════════════════════════════════════════════════════════════════════════
// MISSING REPORTS — Batch 3 (29 remaining reports)
// ═══════════════════════════════════════════════════════════════════════════

// Membership Reports
Route::get('birthmarrrep', [Reporting::class, 'birthmarrrep'])->name('birthmarrrep');
Route::post('birthmarrrepfetch', [Reporting::class, 'birthmarrrepfetch'])->name('birthmarrrepfetch');
Route::get('membillmissingreport', [Reporting::class, 'membillmissingreport'])->name('membillmissingreport');
Route::post('membillmissingreportfetch', [Reporting::class, 'membillmissingreportfetch'])->name('membillmissingreportfetch');
Route::get('membirthanndtls', [Reporting::class, 'membirthanndtls'])->name('membirthanndtls');
Route::post('membirthanndtlsfetch', [Reporting::class, 'membirthanndtlsfetch'])->name('membirthanndtlsfetch');
Route::get('memalinglabels', [Reporting::class, 'memalinglabels'])->name('memalinglabels');
Route::post('memalinglabelsfetch', [Reporting::class, 'memalinglabelsfetch'])->name('memalinglabelsfetch');
Route::get('memsalesregister', [Reporting::class, 'memsalesregister'])->name('memsalesregister');
Route::post('memsalesregisterfetch', [Reporting::class, 'memsalesregisterfetch'])->name('memsalesregisterfetch');
Route::get('memvisitdetail', [Reporting::class, 'memvisitdetail'])->name('memvisitdetail');
Route::post('memvisitdetailfetch', [Reporting::class, 'memvisitdetailfetch'])->name('memvisitdetailfetch');

// Front Office Reports
Route::get('complaintlist', [Reporting::class, 'complaintlist'])->name('complaintlist');
Route::post('complaintlistfetch', [Reporting::class, 'complaintlistfetch'])->name('complaintlistfetch');
Route::get('formiii', [Reporting::class, 'formiii'])->name('formiii');
Route::post('formiiifetch', [Reporting::class, 'formiiifetch'])->name('formiiifetch');
Route::get('registrationcard', [Reporting::class, 'registrationcard'])->name('registrationcard');
Route::post('registrationcarddata', [Reporting::class, 'registrationcarddata'])->name('registrationcarddata');

// Plan/Meal Reports
Route::get('planmealtokens', [Reporting::class, 'planmealtokens'])->name('planmealtokens');
Route::post('planmealtokensfetch', [Reporting::class, 'planmealtokensfetch'])->name('planmealtokensfetch');
Route::get('planpackschedule', [Reporting::class, 'planpackschedule'])->name('planpackschedule');
Route::post('planpackschedulefetch', [Reporting::class, 'planpackschedulefetch'])->name('planpackschedulefetch');
Route::get('planpackservice', [Reporting::class, 'planpackservice'])->name('planpackservice');
Route::post('planpackservicefetch', [Reporting::class, 'planpackservicefetch'])->name('planpackservicefetch');

// HR Report
Route::get('attendancerep', [Reporting::class, 'attendancerep'])->name('attendancerep');
Route::post('attendancerepfetch', [Reporting::class, 'attendancerepfetch'])->name('attendancerepfetch');

// Finance/Analysis Reports
Route::get('budgetanalysis', [Reporting::class, 'budgetanalysis'])->name('budgetanalysis');
Route::post('budgetanalysisfetch', [Reporting::class, 'budgetanalysisfetch'])->name('budgetanalysisfetch');
Route::get('businessanalysis', [Reporting::class, 'businessanalysis'])->name('businessanalysis');
Route::post('businessanalysisfetch', [Reporting::class, 'businessanalysisfetch'])->name('businessanalysisfetch');
Route::get('bussoccupancyreport', [Reporting::class, 'bussoccupancyreport'])->name('bussoccupancyreport');
Route::post('bussoccupancyreportfetch', [Reporting::class, 'bussoccupancyreportfetch'])->name('bussoccupancyreportfetch');
Route::get('costanalysis', [Reporting::class, 'costanalysis'])->name('costanalysis');
Route::post('costanalysisfetch', [Reporting::class, 'costanalysisfetch'])->name('costanalysisfetch');
Route::get('marketseganalysis', [Reporting::class, 'marketseganalysis'])->name('marketseganalysis');
Route::post('marketseganalysisfetch', [Reporting::class, 'marketseganalysisfetch'])->name('marketseganalysisfetch');

// Cash Card Reports
Route::get('cashcardcollectsumm', [Reporting::class, 'cashcardcollectsumm'])->name('cashcardcollectsumm');
Route::post('cashcardcollectsummfetch', [Reporting::class, 'cashcardcollectsummfetch'])->name('cashcardcollectsummfetch');
Route::get('cashcardtransrep', [Reporting::class, 'cashcardtransrep'])->name('cashcardtransrep');
Route::post('cashcardtransrepfetch', [Reporting::class, 'cashcardtransrepfetch'])->name('cashcardtransrepfetch');

// Other Reports
Route::get('epabxcallrep', [Reporting::class, 'epabxcallrep'])->name('epabxcallrep');
Route::post('epabxcallrepfetch', [Reporting::class, 'epabxcallrepfetch'])->name('epabxcallrepfetch');
Route::get('fbcoststatement', [Reporting::class, 'fbcoststatement'])->name('fbcoststatement');
Route::post('fbcoststatementfetch', [Reporting::class, 'fbcoststatementfetch'])->name('fbcoststatementfetch');
Route::get('facilitybillreg', [Reporting::class, 'facilitybillreg'])->name('facilitybillreg');
Route::post('facilitybillregfetch', [Reporting::class, 'facilitybillregfetch'])->name('facilitybillregfetch');
Route::get('monthlystatisticalreturn', [Reporting::class, 'monthlystatisticalreturn'])->name('monthlystatisticalreturn');
Route::post('monthlystatisticalreturnfetch', [Reporting::class, 'monthlystatisticalreturnfetch'])->name('monthlystatisticalreturnfetch');
Route::get('packageforecast', [Reporting::class, 'packageforecast'])->name('packageforecast');
Route::post('packageforecastfetch', [Reporting::class, 'packageforecastfetch'])->name('packageforecastfetch');
Route::get('paymentdueletter', [Reporting::class, 'paymentdueletter'])->name('paymentdueletter');
Route::post('paymentdueletterdata', [Reporting::class, 'paymentdueletterdata'])->name('paymentdueletterdata');
Route::get('refreport', [Reporting::class, 'refreport'])->name('refreport');
Route::post('refreportfetch', [Reporting::class, 'refreportfetch'])->name('refreportfetch');
Route::get('travelagentanalysis', [Reporting::class, 'travelagentanalysis'])->name('travelagentanalysis');
Route::post('travelagentanalysisfetch', [Reporting::class, 'travelagentanalysisfetch'])->name('travelagentanalysisfetch');

   // ═══ MISSING HMS REPORTS — Migration Batch ═══

   // 1. Arrival Departure Register
   Route::get('arrdepreg', [Reporting::class, 'arrdepreg']);
   Route::post('arrdepregfetch', [Reporting::class, 'arrdepregfetch']);

   // 2. Bank Clearance
   Route::get('bankclg', [Reporting::class, 'bankclg']);
   Route::post('bankclgfetch', [Reporting::class, 'bankclgfetch']);

   // 3. Bank Not Cleared
   Route::get('bankclgnot', [Reporting::class, 'bankclgnot']);
   Route::post('bankclgnotfetch', [Reporting::class, 'bankclgnotfetch']);

   // 4. Debit Ledger
   Route::get('ledgerdeb', [Reporting::class, 'ledgerdeb']);
   Route::post('ledgerdebfetch', [Reporting::class, 'ledgerdebfetch']);

   // 5. Interest Ledger
   Route::get('ledgerint', [Reporting::class, 'ledgerint']);
   Route::post('ledgerintfetch', [Reporting::class, 'ledgerintfetch']);

   // 6. Daily Cash Register (Roz Namcha)
   Route::get('roznamcha', [Reporting::class, 'roznamcha']);
   Route::post('roznamchafetch', [Reporting::class, 'roznamchafetch']);

   // 7. Goods Receipt Challan
   Route::get('grc', [Reporting::class, 'grc']);
   Route::post('grcfetch', [Reporting::class, 'grcfetch']);

   // 8. GSTR-1
   Route::get('gstr1report', [Reporting::class, 'gstr1report']);
   Route::post('gstr1reportfetch', [Reporting::class, 'gstr1reportfetch']);

   // 9. PLU File Export
   Route::get('plufile', [Reporting::class, 'plufile']);
   Route::post('plufilefetch', [Reporting::class, 'plufilefetch']);

   // 10. General Ledger (if not exists)
   Route::get('generalledger2', [Reporting::class, 'generalledger2']);
   Route::post('generalledger2fetch', [Reporting::class, 'generalledger2fetch']);

   // ===== HMS.text Missing Reports - Batch A (Front Office + Reservation) =====
   Route::get('bookingdetail', [Reporting::class, 'bookingdetail'])->name('bookingdetail');
   Route::post('bookingdetailfetch', [Reporting::class, 'bookingdetailfetch'])->name('bookingdetailfetch');

   Route::get('daysforecastrep', [Reporting::class, 'daysforecastrep'])->name('daysforecastrep');
   Route::post('daysforecastrepfetch', [Reporting::class, 'daysforecastrepfetch'])->name('daysforecastrepfetch');

   Route::get('guestbilldetails', [Reporting::class, 'guestbilldetails'])->name('guestbilldetails');
   Route::post('guestbilldetailsfetch', [Reporting::class, 'guestbilldetailsfetch'])->name('guestbilldetailsfetch');

   Route::get('guestchgjournal', [Reporting::class, 'guestchgjournal'])->name('guestchgjournal');
   Route::post('guestchgjournalfetch', [Reporting::class, 'guestchgjournalfetch'])->name('guestchgjournalfetch');

   Route::get('guestchgjournallog', [Reporting::class, 'guestchgjournallog'])->name('guestchgjournallog');
   Route::post('guestchgjournallogfetch', [Reporting::class, 'guestchgjournallogfetch'])->name('guestchgjournallogfetch');

   Route::get('guestobservrep', [Reporting::class, 'guestobservrep'])->name('guestobservrep');
   Route::post('guestobservrepfetch', [Reporting::class, 'guestobservrepfetch'])->name('guestobservrepfetch');

   Route::get('inhousecount', [Reporting::class, 'inhousecount'])->name('inhousecount');
   Route::post('inhousecountfetch', [Reporting::class, 'inhousecountfetch'])->name('inhousecountfetch');

   Route::get('guestinhousereport', [Reporting::class, 'guestinhousereport'])->name('guestinhousereport');
   Route::post('guestinhousereportfetch', [Reporting::class, 'guestinhousereportfetch'])->name('guestinhousereportfetch');

   Route::get('delbillunsetbill', [Reporting::class, 'delbillunsetbill'])->name('delbillunsetbill');
   Route::post('delbillunsetbillfetch', [Reporting::class, 'delbillunsetbillfetch'])->name('delbillunsetbillfetch');

   Route::get('resvadvrecd', [Reporting::class, 'resvadvrecd'])->name('resvadvrecd');
   Route::post('resvadvrecdfetch', [Reporting::class, 'resvadvrecdfetch'])->name('resvadvrecdfetch');

   Route::get('resvadvrecdarr', [Reporting::class, 'resvadvrecdarr'])->name('resvadvrecdarr');
   Route::post('resvadvrecdarrfetch', [Reporting::class, 'resvadvrecdarrfetch'])->name('resvadvrecdarrfetch');

   Route::get('resvadvrecdinhouse', [Reporting::class, 'resvadvrecdinhouse'])->name('resvadvrecdinhouse');
   Route::post('resvadvrecdinhousefetch', [Reporting::class, 'resvadvrecdinhousefetch'])->name('resvadvrecdinhousefetch');

   // ===== HMS.text Missing Reports - Batch B (Accounts) =====
   Route::get('bankreg', [Reporting::class, 'bankreg'])->name('bankreg');
   Route::post('bankregfetch', [Reporting::class, 'bankregfetch'])->name('bankregfetch');

   Route::get('ledgercred', [Reporting::class, 'ledgercred'])->name('ledgercred');
   Route::post('ledgercredfetch', [Reporting::class, 'ledgercredfetch'])->name('ledgercredfetch');

   Route::get('controlledaccounts', [Reporting::class, 'controlledaccounts'])->name('controlledaccounts');
   Route::post('controlledaccountsfetch', [Reporting::class, 'controlledaccountsfetch'])->name('controlledaccountsfetch');

   Route::get('partywiseoutstanding', [Reporting::class, 'partywiseoutstanding'])->name('partywiseoutstanding');
   Route::post('partywiseoutstandingfetch', [Reporting::class, 'partywiseoutstandingfetch'])->name('partywiseoutstandingfetch');

   Route::get('pmtbycashier', [Reporting::class, 'pmtbycashier'])->name('pmtbycashier');
   Route::post('pmtbycashierfetch', [Reporting::class, 'pmtbycashierfetch'])->name('pmtbycashierfetch');

   // ===== SALES DAY BOOK + STOCK LEDGER (HMS.text missing reports) =====
   Route::get('salesdaybook', [Reporting::class, 'salesdaybook'])->name('salesdaybook');
   Route::post('salesdaybookfetch', [Reporting::class, 'salesdaybookfetch'])->name('salesdaybookfetch');
   Route::get('stockledger', [Reporting::class, 'stockledger'])->name('stockledger');
   Route::post('stockledgerfetch', [Reporting::class, 'stockledgerfetch'])->name('stockledgerfetch');

// ============ BATCH C: GST / TAX REPORTS (codes 131231-131236) ============
Route::get('taxdetails', [Reporting::class, 'taxdetails'])->name('taxdetails');
Route::post('taxdetailsfetch', [Reporting::class, 'taxdetailsfetch'])->name('taxdetailsfetch');
Route::get('taxregister', [Reporting::class, 'taxregister'])->name('taxregister');
Route::post('taxregisterfetch', [Reporting::class, 'taxregisterfetch'])->name('taxregisterfetch');
Route::get('taxwisesale', [Reporting::class, 'taxwisesale'])->name('taxwisesale');
Route::post('taxwisesalefetch', [Reporting::class, 'taxwisesalefetch'])->name('taxwisesalefetch');
Route::get('taxreporthall', [Reporting::class, 'taxreporthall'])->name('taxreporthall');
Route::post('taxreporthallfetch', [Reporting::class, 'taxreporthallfetch'])->name('taxreporthallfetch');
Route::get('taxsummaryhall', [Reporting::class, 'taxsummaryhall'])->name('taxsummaryhall');
Route::post('taxsummaryhallfetch', [Reporting::class, 'taxsummaryhallfetch'])->name('taxsummaryhallfetch');
Route::get('taxwisedetailreporthall', [Reporting::class, 'taxwisedetailreporthall'])->name('taxwisedetailreporthall');
Route::post('taxwisedetailreporthallfetch', [Reporting::class, 'taxwisedetailreporthallfetch'])->name('taxwisedetailreporthallfetch');

   // ===== BATCH D: POS/STORE/PURCHASE REPORTS =====
   Route::get('cashiersale', [Reporting::class, 'cashiersale'])->name('cashiersale');
   Route::post('cashiersalefetch', [Reporting::class, 'cashiersalefetch'])->name('cashiersalefetch');
   Route::get('cashiersummary', [Reporting::class, 'cashiersummary'])->name('cashiersummary');
   Route::post('cashiersummaryfetch', [Reporting::class, 'cashiersummaryfetch'])->name('cashiersummaryfetch');
   Route::get('storeissuereport', [Reporting::class, 'storeissuereport'])->name('storeissuereport');
   Route::post('storeissuereportfetch', [Reporting::class, 'storeissuereportfetch'])->name('storeissuereportfetch');
   Route::get('purchaseledger', [Reporting::class, 'purchaseledger'])->name('purchaseledger');
   Route::post('purchaseledgerfetch', [Reporting::class, 'purchaseledgerfetch'])->name('purchaseledgerfetch');
   Route::get('cashcreditpurch', [Reporting::class, 'cashcreditpurch'])->name('cashcreditpurch');
   Route::post('cashcreditpurchfetch', [Reporting::class, 'cashcreditpurchfetch'])->name('cashcreditpurchfetch');

   // ===== BATCH E: RESTAURANT/KITCHEN REPORTS =====
   Route::get('restissue', [Reporting::class, 'restissue'])->name('restissue');
   Route::post('restissuefetch', [Reporting::class, 'restissuefetch'])->name('restissuefetch');
   Route::get('kitchenstkrep', [Reporting::class, 'kitchenstkrep'])->name('kitchenstkrep');
   Route::post('kitchenstkrepfetch', [Reporting::class, 'kitchenstkrepfetch'])->name('kitchenstkrepfetch');
   Route::get('kitchenstksumm', [Reporting::class, 'kitchenstksumm'])->name('kitchenstksumm');
   Route::post('kitchenstksummfetch', [Reporting::class, 'kitchenstksummfetch'])->name('kitchenstksummfetch');
   Route::get('saleregisteri', [Reporting::class, 'saleregisteri'])->name('saleregisteri');
   Route::post('saleregisterifetch', [Reporting::class, 'saleregisterifetch'])->name('saleregisterifetch');

   // ===== BATCH F: HALL/STORE/ISSUE REPORTS =====
   Route::get('settlerephall', [Reporting::class, 'settlerephall'])->name('settlerephall');
   Route::post('settlerephallfetch', [Reporting::class, 'settlerephallfetch'])->name('settlerephallfetch');
   Route::get('issuereg', [Reporting::class, 'issuereg'])->name('issuereg');
   Route::post('issueregfetch', [Reporting::class, 'issueregfetch'])->name('issueregfetch');
   Route::get('issueregister', [Reporting::class, 'issueregister'])->name('issueregister');
   Route::post('issueregisterfetch', [Reporting::class, 'issueregisterfetch'])->name('issueregisterfetch');
   Route::get('storeissreg', [Reporting::class, 'storeissreg'])->name('storeissreg');
   Route::post('storeissregfetch', [Reporting::class, 'storeissregfetch'])->name('storeissregfetch');
   Route::get('dailystoreissrpt', [Reporting::class, 'dailystoreissrpt'])->name('dailystoreissrpt');
   Route::post('dailystoreissrptfetch', [Reporting::class, 'dailystoreissrptfetch'])->name('dailystoreissrptfetch');

   // ===== BATCH G: STOCK/SALES ANALYSIS REPORTS =====
   Route::get('stockregstore', [Reporting::class, 'stockregstore'])->name('stockregstore');
   Route::post('stockregstorefetch', [Reporting::class, 'stockregstorefetch'])->name('stockregstorefetch');
   Route::get('stocksummstore', [Reporting::class, 'stocksummstore'])->name('stocksummstore');
   Route::post('stocksummstorefetch', [Reporting::class, 'stocksummstorefetch'])->name('stocksummstorefetch');
   Route::get('itemwisegroupwisesale', [Reporting::class, 'itemwisegroupwisesale'])->name('itemwisegroupwisesale');
   Route::post('itemwisegroupwisesalefetch', [Reporting::class, 'itemwisegroupwisesalefetch'])->name('itemwisegroupwisesalefetch');
   Route::get('monthoutletwisesale', [Reporting::class, 'monthoutletwisesale'])->name('monthoutletwisesale');
   Route::post('monthoutletwisesalefetch', [Reporting::class, 'monthoutletwisesalefetch'])->name('monthoutletwisesalefetch');

   // ===== P1 CRITICAL: Financial Audit / GST / Night Audit =====
   Route::get('nightauditreport', [Reporting::class, 'nightauditreport'])->name('nightauditreport');
   Route::post('nightauditreportfetch', [Reporting::class, 'nightauditreportfetch'])->name('nightauditreportfetch');
   Route::get('nightauditreporti', [Reporting::class, 'nightauditreporti'])->name('nightauditreporti');
   Route::post('nightauditreportifetch', [Reporting::class, 'nightauditreportifetch'])->name('nightauditreportifetch');
   Route::get('cancelbilldetails', [Reporting::class, 'cancelbilldetails'])->name('cancelbilldetails');
   Route::post('cancelbilldetailsfetch', [Reporting::class, 'cancelbilldetailsfetch'])->name('cancelbilldetailsfetch');
   Route::get('salesregister', [Reporting::class, 'salesregister'])->name('salesregister');
   Route::post('salesregisterfetch', [Reporting::class, 'salesregisterfetch'])->name('salesregisterfetch');
   Route::get('salessummary', [Reporting::class, 'salessummary'])->name('salessummary');
   Route::post('salessummaryfetch', [Reporting::class, 'salessummaryfetch'])->name('salessummaryfetch');
   Route::get('nckotsummary', [Reporting::class, 'nckotsummary'])->name('nckotsummary');
   Route::post('nckotsummaryfetch', [Reporting::class, 'nckotsummaryfetch'])->name('nckotsummaryfetch');
   Route::get('gstr2_3', [Reporting::class, 'gstr2_3'])->name('gstr2_3');
   Route::post('gstr2_3fetch', [Reporting::class, 'gstr2_3fetch'])->name('gstr2_3fetch');
   Route::get('gstr2_4a', [Reporting::class, 'gstr2_4a'])->name('gstr2_4a');
   Route::post('gstr2_4afetch', [Reporting::class, 'gstr2_4afetch'])->name('gstr2_4afetch');
   Route::get('gstr2_4b', [Reporting::class, 'gstr2_4b'])->name('gstr2_4b');
   Route::post('gstr2_4bfetch', [Reporting::class, 'gstr2_4bfetch'])->name('gstr2_4bfetch');
   Route::get('luxurytaxregister', [Reporting::class, 'luxurytaxregister'])->name('luxurytaxregister');
   Route::post('luxurytaxregisterfetch', [Reporting::class, 'luxurytaxregisterfetch'])->name('luxurytaxregisterfetch');
   Route::get('taxinvoicedetail', [Reporting::class, 'taxinvoicedetail'])->name('taxinvoicedetail');
   Route::post('taxinvoicedetailfetch', [Reporting::class, 'taxinvoicedetailfetch'])->name('taxinvoicedetailfetch');
   Route::get('dailysumm', [Reporting::class, 'dailysumm'])->name('dailysumm');
   Route::post('dailysummfetch', [Reporting::class, 'dailysummfetch'])->name('dailysummfetch');
   Route::get('bankbook', [Reporting::class, 'bankbook'])->name('bankbook');
   Route::post('bankbookfetch', [Reporting::class, 'bankbookfetch'])->name('bankbookfetch');
   Route::get('cashbook', [Reporting::class, 'cashbook'])->name('cashbook');
   Route::post('cashbookfetch', [Reporting::class, 'cashbookfetch'])->name('cashbookfetch');
   Route::get('chkinregister', [Reporting::class, 'chkinregister'])->name('chkinregister');
   Route::post('chkinregisterfetch', [Reporting::class, 'chkinregisterfetch'])->name('chkinregisterfetch');
   Route::get('roomrentauditreport', [Reporting::class, 'roomrentauditreport'])->name('roomrentauditreport');
   Route::post('roomrentauditreportfetch', [Reporting::class, 'roomrentauditreportfetch'])->name('roomrentauditreportfetch');

// ── P2 HIGH: Arrival Departure List ──────────────────────────────────────
Route::get('/arrivaldep', [App\Http\Controllers\Reporting::class, 'arrivaldep'])->name('arrivaldep');
Route::post('/arrivaldepfetch', [App\Http\Controllers\Reporting::class, 'arrivaldepfetch'])->name('arrivaldepfetch');

// ── P2 HIGH: Expected Departure ──────────────────────────────────────────
Route::get('/expecteddep', [App\Http\Controllers\Reporting::class, 'expecteddep'])->name('expecteddep');
Route::post('/expecteddepfetch', [App\Http\Controllers\Reporting::class, 'expecteddepfetch'])->name('expecteddepfetch');

// ── P2 HIGH: Room Occupancy Display ──────────────────────────────────────
Route::get('/roomoccdisp', [App\Http\Controllers\Reporting::class, 'roomoccdisp'])->name('roomoccdisp');
Route::post('/roomoccdispfetch', [App\Http\Controllers\Reporting::class, 'roomoccdispfetch'])->name('roomoccdispfetch');

// ── P2 HIGH: Company Analysis ────────────────────────────────────────────
Route::get('/companyanalysis', [App\Http\Controllers\Reporting::class, 'companyanalysis'])->name('companyanalysis');
Route::post('/companyanalysisfetch', [App\Http\Controllers\Reporting::class, 'companyanalysisfetch'])->name('companyanalysisfetch');

// ── P2 HIGH: Revenue Analysis ────────────────────────────────────────────
Route::get('/revanalysis', [App\Http\Controllers\Reporting::class, 'revanalysis'])->name('revanalysis');
Route::post('/revanalysisfetch', [App\Http\Controllers\Reporting::class, 'revanalysisfetch'])->name('revanalysisfetch');

// ── P2 HIGH: Room Type Occupancy Analysis ────────────────────────────────
Route::get('/roomtypeoccupancyanalysis', [App\Http\Controllers\Reporting::class, 'roomtypeoccupancyanalysis'])->name('roomtypeoccupancyanalysis');
Route::post('/roomtypeoccupancyanalysisfetch', [App\Http\Controllers\Reporting::class, 'roomtypeoccupancyanalysisfetch'])->name('roomtypeoccupancyanalysisfetch');

// ── P2 HIGH: Room Type Occupancy (summary) ───────────────────────────────
Route::get('/roomtypeoccupancy', [App\Http\Controllers\Reporting::class, 'roomtypeoccupancy'])->name('roomtypeoccupancy');
Route::post('/roomtypeoccupancyfetch', [App\Http\Controllers\Reporting::class, 'roomtypeoccupancyfetch'])->name('roomtypeoccupancyfetch');
