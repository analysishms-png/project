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
