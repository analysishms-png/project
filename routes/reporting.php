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
// Occupancy Forecast Print (DomPDF)
Route::get('printoccupancyforecast', [Reporting::class, 'printoccupancyforecast'])->name('printoccupancyforecast');
// Occupancy Forecast Excel Export
Route::get('exportoccupancyforecast', [Reporting::class, 'exportoccupancyforecast'])->name('exportoccupancyforecast');
