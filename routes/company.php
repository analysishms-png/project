<?php

use App\Http\Controllers\Finance\Master\TdsCategoryController;
use App\Http\Controllers\FrontOffice\Operations\FetchDataFom;
use App\Http\Controllers\HRPayroll\OverTimeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Banquet;
use App\Http\Controllers\BookingInquiryController;
use App\Http\Controllers\ChargePosting;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\EInvoiceParameter;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\Pointofsale;
use App\Http\Controllers\Reporting;
use App\Http\Controllers\Fetch;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Finance\FinanceEnviro;
use App\Http\Controllers\Finance\Transaction\BankReconcilation;
use App\Http\Controllers\Finance\Transaction\VoucherEntry;
use App\Http\Controllers\Finance\Transaction\VoucherVerification;
use App\Http\Controllers\FinancialPush;
use App\Http\Controllers\Frontend\HotelBookingController;
use App\Http\Controllers\Frontend\HotelsController;
use App\Http\Controllers\Frontend\RoomSettlement;
use App\Http\Controllers\FrontOffice\AddNewProfile;
use App\Http\Controllers\FrontOffice\ChargeController;
use App\Http\Controllers\FrontOffice\InhouseAction;
use App\Http\Controllers\FrontOffice\Operations\CheckinList;
use App\Http\Controllers\FrontOffice\Operations\HouseModelOperations;
use App\Http\Controllers\General\QRCodeController;
use App\Http\Controllers\General\UpgradingExpCrypt;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\HouseKeeping;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Member\MemberCategoryController;
use App\Http\Controllers\Member\MemberFacilityMasterController;
use App\Http\Controllers\Member\MemberMasterController;
use App\Http\Controllers\PartyMaster;
use App\Http\Controllers\Pos;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\Printing;
use App\Http\Controllers\PythonAuth;
use App\Http\Controllers\Reservation;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\FrontOffice\RoomStatus;
// use App\Http\Controllers\SmartCard\CardInitializationController; // REMOVED — stub controller, no implementation
// use App\Http\Controllers\SmartCard\CardRechargeController; // REMOVED — stub controller, no implementation
// use App\Http\Controllers\SmartCard\CardReFundController; // REMOVED — stub controller, no implementation
// use App\Http\Controllers\SmartCard\CardRegistrationController; // REMOVED — stub controller, no implementation
use App\Http\Controllers\UserController; //created by ananya
use App\Http\Controllers\WPParameter;
use Carbon\Cli\Invoker;
use League\Flysystem\Local\FallbackMimeTypeDetector;
use App\Http\Controllers\purchaseorder\PurchaseOrderController;
use App\Http\Controllers\quotation\QuotationController;
use App\Http\Controllers\Reservation\Advance;
use App\Http\Controllers\Reservation\UpdateReservation;
use App\Http\Controllers\GatePassController;
use App\Http\Controllers\GatePassInController;
use App\Http\Controllers\General\PayrollParameter;
use App\Http\Controllers\General\RoomQRController;
use App\Http\Controllers\HRPayroll\LeaveController;
use App\Http\Controllers\HRPayroll\LoanAdvanceEntry;
use App\Http\Controllers\HRPayroll\OverTime;
use App\Http\Controllers\HRPayroll\SalaryController;
use App\Http\Controllers\MainSetup\FrontOffice\FomParameter;
use App\Http\Controllers\MainSetup\Inventory\RecipeMastController;
use App\Http\Controllers\Pos\OrderController;
use Faker\Provider\Company;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// pratap Mehra 
// Password:- Mehra@2024
// Resort Code:- '17526'
// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/storage-link', function () {
    $target_folder = storage_path('app/public');
    $link_folder = public_path('storage');
    if (!file_exists($link_folder)) {
        symlink($target_folder, $link_folder);
    }
});

// Open Time Page
Route::get('time', function () {
    return view('property.time');
});

Route::get('submitloc', [CompanyController::class, 'submitloc'])->name('submitloc');
Route::get('getcompdt', [CompanyController::class, 'getcompdt'])->name('getcompdt');
Route::post('getindex', [Reporting::class, 'getindex'])->name('getindex');
Route::get('/home', [LoginController::class, 'logout'])->name('logout');
// Ncur Date
Route::get('ncurdate', [CompanyController::class, 'ncurdateget']);
// Ncur Date
Route::get('yearmanage/{year?}', [CompanyController::class, 'yearmanage'])->name('yearmanage');
Route::get('yearmanagetodate/{date}', [CompanyController::class, 'yearmanagetodate'])->name('yearmanagetodate');
// Checkout Time
Route::get('checkouttime', [CompanyController::class, 'checkouttimeget']);
// OpenCity Form
Route::get('/cityform2', [CompanyController::class, 'opencity']);
// Load State2
Route::post('/getState2', [CompanyController::class, 'getState2']);
// Submit City Form
Route::post('citystore2', [CompanyController::class, 'submitcity'])->name('citystore2');
// Delete City
route::get('deletecity', [CompanyController::class, 'deletecity']);
// Open State Form
route::get('/stateform2', [CompanyController::class, 'openstate']);
// Submit State Form
route::post('statestore2', [CompanyController::class, 'submitstate'])->name('statestore2');
// Delete State
route::get('deletestate', [CompanyController::class, 'deletestate']);
// Open Country Form
route::get('/countryform2', [CompanyController::class, 'opencountry']);
// Submit Country Form
route::post('countrystore2', [CompanyController::class, 'submitcountry'])->name('countrystore2');
// Delete Country
route::get('deletecountry', [CompanyController::class, 'deletecountry']);
// Update Country Form Open
route::get('updatecountry', [CompanyController::class, 'updatecountry']);
// Update Country
route::post('update_countrystore2', [CompanyController::class, 'update_countrystore'])->name('update_countrystore2');
// Update state Form Open
route::get('updatestateform', [CompanyController::class, 'updatestate']);
// Update state
route::post('statestoreupdate', [CompanyController::class, 'update_statestore'])->name('statestoreupdate');
// Update City Form Open
route::get('updatecityform', [CompanyController::class, 'updatecity']);
// Update City
route::post('citystoreupdate', [CompanyController::class, 'citystoreupdate'])->name('citystoreupdate');
// Open User Master Form
route::get('/usermaster', [CompanyController::class, 'openusermaster']);
// Check auth
Route::post('checkAuth', [CompanyController::class, 'checkauth'])->name('checkAuth');
// Submit User Master Form
route::post('usermasterstore', [CompanyController::class, 'submitusermaster'])->name('usermasterstore');
// Disable User
Route::get('disableusermaster', [CompanyController::class, 'disableusermaster'])->name('id');
// Enable User
Route::get('enableusermaster', [CompanyController::class, 'enableusermaster'])->name('id');
// Update User Master Form Open
route::get('updateusermaster', [CompanyController::class, 'updateusermaster']);
// Update User Master
route::post('update_usermaster', [CompanyController::class, 'update_usermasterstore'])->name('update_usermaster');
// Change Company Detail
route::post('changecompanydetail', [CompanyController::class, 'changecompanydetails'])->name('changecompanydetail');
// Utiliti Page Open
route::get('/utilities', [CompanyController::class, 'Utilityoepn']);
// Open Group Account Entry
Route::get('/groupaccouns', [CompanyController::class, 'opengroupaccountentry']);
// Open Update Group Entry
Route::get('updategroupentry/{group_code}', [CompanyController::class, 'opengroupupdateentry']);
// Save Group Entry
Route::post('savegroupaccountentry', [CompanyController::class, 'savegroupaccountentry'])->name('savegroupaccountentry');
// Update Group Entry
Route::post('updategroupaccountentry', [CompanyController::class, 'updategroupaccountentry'])->name('updategroupaccountentry');
// Delete Group Entry
Route::get('deletegroupentry/{group_code}', [CompanyController::class, 'deletegroupentry']);
// Inconsistency Check Open
route::get('/inconsistency', [CompanyController::class, 'inconsistency']);
// acgroup update
route::get('accountgrp', [CompanyController::class, 'accountupdate']);
// subgroup update
route::get('subgroup', [CompanyController::class, 'subgroupupdate']);
// CountryLoad
route::get('countryload', [CompanyController::class, 'countryloadupdate']);
// Stateload
route::get('stateload', [CompanyController::class, 'stateloadupdate']);
// Cityload
route::get('cityload', [CompanyController::class, 'cityloadupdate']);
// SundryMasterload
route::get('loadsundrymaster', [CompanyController::class, 'sundrymasterloadupdate']);
// Unit Master Load
route::get('/loadunitmaster', [CompanyController::class, 'unitmasterloadupdate']);
// SundryTypeload
route::get('loadsundrytype', [CompanyController::class, 'sundrytypeloadupdate']);
// Open Tax Master
route::get('taxmaster', [CompanyController::class, 'opentaxmaster']);
// Print Tax Master
Route::get('printtaxmaster', [CompanyController::class, 'printTaxMaster'])->name('printtaxmaster');
// Excel Export Tax Master
Route::get('taxmaster/export', [CompanyController::class, 'exportTaxMaster'])->name('taxmaster.export');
// Submit Tax
route::post('taxstore', [CompanyController::class, 'submittax'])->name('taxstore');
// Get Sundry Names
route::post('getsundrynames', [CompanyController::class, 'getsundrynames']);
// Load Taxes
route::get('loadtaxes', [CompanyController::class, 'taxloadupdate']);
// Load Taxes Structure
route::get('loadtaxesstructure', [CompanyController::class, 'taxloadstructureupdate']);
// Get Ledger Names
route::post('getledgernames', [CompanyController::class, 'getledgernames']);
// Get Tax Names
route::post('gettaxnames', [CompanyController::class, 'gettaxnames']);
// Delete Tax
Route::match(['get', 'post'], 'deletetax', [CompanyController::class, 'deletetax']);
// Open Updatetax
Route::get('updatetax', [CompanyController::class, 'openupdatetax']);
// Update Tax
route::post('taxstoreupdate', [CompanyController::class, 'taxstoreupdate'])->name('taxstoreupdate');
// Open Tax Structure
route::get('taxstructure', [CompanyController::class, 'opentaxstructure'])->name('taxstructure');
// Print Tax Structure
Route::get('printtaxstructure', [CompanyController::class, 'printTaxStructure'])->name('printtaxstructure');
// Excel Export Tax Structure
Route::get('taxstructure/export', [CompanyController::class, 'exportTaxStructure'])->name('taxstructure.export');
// Submit Tax Structure
route::post('taxstrustore', [CompanyController::class, 'submittaxstructure'])->name('taxstrustore');
// Open Update Tax Structure
route::get('updatetaxstructure', [CompanyController::class, 'openupdatetaxstru']);
// Update Tax Structure
route::post('taxstrustoreupdate', [CompanyController::class, 'taxstructurestoreupdate'])->name('taxstrustoreupdate');
//Delete Tax Structure
route::get('deletetaxstructure', [CompanyController::class, 'deletetaxstructure']);
// Open Ledger Account
route::get('ledgeraccount', [CompanyController::class, 'openledgeraccount']);
// Submit Ledger Account
route::post('ledgerstore', [CompanyController::class, 'submitledger'])->name('ledgerstore');
// Submit Ledger Account
route::post('ledgerstoreparty', [CompanyController::class, 'ledgerstoreparty'])->name('ledgerstoreparty');
// Delete Ledger
Route::get('deleteledger/{code}', [CompanyController::class, 'deleteledger']);
// Open Update Ledger Account
Route::get('updateledgeraccount', [CompanyController::class, 'openupdateledgeraccount']);
// Update Ledger Account
Route::post('ledgerupdate', [CompanyController::class, 'updateledgerstore'])->name('ledgerupdate');
// Update Ledger Account party
Route::post('ledgerupdateparty', [CompanyController::class, 'ledgerupdateparty'])->name('ledgerupdateparty');
// Open Company Master
route::get('companymaster', [CompanyController::class, 'opencompanymaster']);
// Submit Company Master
route::post('comp_maststore', [CompanyController::class, 'submitcomp_master'])->name('comp_maststore');
// Delete Company Master
route::get('deletecomp_mast', [CompanyController::class, 'deletecomp_mast']);
// Open Update Company Master
route::get('updatecompmaster', [CompanyController::class, 'openupdatecompmaster']);
// Plan Fetch On Room Cat
Route::post('planfetchbycat', [GeneralController::class, 'planfetchbycat'])->name('planfetchbycat');
// Update Company Master
route::post('comp_mastupdate', [CompanyController::class, 'update_compmaster'])->name('comp_mastupdate');
// Load Voucher Prefix
route::get('loadvoucherprefix', [CompanyController::class, 'voucherprefixloadupdate']);
// Load Voucher Type
route::get('loadvouchertype', [CompanyController::class, 'vouchertypeloadupdate']);
// Load Settlement
route::get('settlementload', [CompanyController::class, 'settlementload']);
// Load Travel Agent
route::get('travelagentload', [CompanyController::class, 'travelagentload']);
// Load Booking Source
route::get('bookingsourceload', [CompanyController::class, 'bookingsourceload']);
// Load Fix Charges
route::get('fixchargesload', [CompanyController::class, 'fixchargesload']);
// Open FOM Parameter
route::get('fomparameter', [CompanyController::class, 'openfomparamter']);
// Submit General Parameter
route::post('generalparamstore', [CompanyController::class, 'submitgeneralparam'])->name('generalparamstore');
// Submit Checkout Parameter
route::post('checkoutparamstore', [CompanyController::class, 'submitcheckoutparams'])->name('checkoutparamstore');
// Submit Posting Parameter
route::post('postingparamstore', [CompanyController::class, 'submitpostingparams'])->name('postingparamstore');
// Submit Rate Parameter
route::post('rateparamstore', [CompanyController::class, 'submitrateparams'])->name('rateparamstore');
// Submit Instruction Parameter
route::post('instructionparamstore', [CompanyController::class, 'submitrateinstructionparamstore'])->name('instructionparamstore');
// Submit House Keeping Param
Route::post('housekeepingparamstore', [FomParameter::class, 'housekeepingparamstore'])->name('housekeepingparamstore');
// Open Business Source
route::get('businesssource', [CompanyController::class, 'openbusinesssource']);
// Print Business Source
Route::get('printbusinesssource', [CompanyController::class, 'printBusinessSource'])->name('printbusinesssource');
// Excel Export Business Source
Route::get('businesssource/export', [CompanyController::class, 'exportBusinessSource'])->name('businesssource.export');
// Submit Business Source
route::post('bsourcestore', [CompanyController::class, 'submitbsourcestore'])->name('bsourcestore');
// Get Business Source Names
route::post('getbnames', [CompanyController::class, 'getbnames']);
// Open Update Business Source
route::get('updatebsource', [CompanyController::class, 'openupdatebsource']);
// Update Business Source
route::post('bsourcestoreupdate', [CompanyController::class, 'updatebsourcestore'])->name('bsourcestoreupdate');
// Delete Business Source
route::get('deletebsource', [CompanyController::class, 'deletebsource']);

route::get('bookingsource', [CompanyController::class, 'openbookingsource']);
// Print Booking Source
Route::get('printbookingsource', [CompanyController::class, 'printBookingSource'])->name('printbookingsource');
// Excel Export Booking Source
Route::get('bookingsource/export', [CompanyController::class, 'exportBookingSource'])->name('bookingsource.export');
route::post('bookingsourcestore', [CompanyController::class, 'submitbookingsourcestore'])->name('bookingsourcestore');
route::post('getbookingsourcenames', [CompanyController::class, 'getbookingsourcenames']);
route::get('updatebookingsource', [CompanyController::class, 'openupdatebookingsource']);
route::post('bookingsourcestoreupdate', [CompanyController::class, 'updatebookingsourcestore'])->name('bookingsourcestoreupdate');
route::get('deletebookingsource', [CompanyController::class, 'deletebookingsource']);

// Open Guest Status
route::get('gueststatus', [CompanyController::class, 'opengueststatus']);
// Print Guest Status
Route::get('printgueststatus', [CompanyController::class, 'printGuestStatus'])->name('printgueststatus');
// Excel Export Guest Status
Route::get('gueststatus/export', [CompanyController::class, 'exportGuestStatus'])->name('gueststatus.export');
// Submit Guest Status
route::post('gueststatusstore', [CompanyController::class, 'submitgueststatusstore'])->name('gueststatusstore');
// Get Guest Status Names
route::post('getgnames', [CompanyController::class, 'getgnames']);
// Open Update Guest Status
route::get('updategueststatus', [CompanyController::class, 'openupdategueststatus']);
// Update Guest Status
route::post('gueststatusstoreupdate', [CompanyController::class, 'updategueststatusstore'])->name('gueststatusstoreupdate');
// Delete Guest Status
route::get('deletegueststatus', [CompanyController::class, 'deletegueststatus']);
// Open Charge Master
route::get('chargemaster', [CompanyController::class, 'openchargemaster']);
// Print Charge Master
Route::get('printchargemaster', [CompanyController::class, 'printChargeMaster'])->name('printchargemaster');
// Excel Export Charge Master
Route::get('chargemaster/export', [CompanyController::class, 'exportChargeMaster'])->name('chargemaster.export');
// Submit Charge Master
route::post('chargemasterstore', [CompanyController::class, 'submitchargemaster'])->name('chargemasterstore');
// Get Charge Master Names
route::post('getchargeames', [CompanyController::class, 'getchargeames']);
// Open Update Charge Master
route::get('updatechargemaster', [CompanyController::class, 'openupdatechargemaster']);
// Update Charge Master
route::post('chargemasterstoreupdate', [CompanyController::class, 'updatechargemasterstore'])->name('chargemasterstoreupdate');
// Delete Charge Master
route::get('deletechargemaster', [CompanyController::class, 'deletechargemaster']);
// Open Room Features
route::get('roomfeatures', [CompanyController::class, 'openroomfeatures']);
// Print Room Features
Route::get('printroomfeature', [CompanyController::class, 'printRoomFeature'])->name('printroomfeature');
// Excel Export Room Features
Route::get('roomfeatures/export', [CompanyController::class, 'exportRoomFeature'])->name('roomfeatures.export');
// Submit Room Features
route::post('roomfeaturestore', [CompanyController::class, 'submitroomfeaturetore'])->name('roomfeaturestore');
// Get Room Features Names
route::post('getrnames', [CompanyController::class, 'getrnames']);
// Open Update Room Features
route::get('updateroomfeature', [CompanyController::class, 'openupdateroomfeature']);
// Update Room Features
route::post('roomfeaturetoreupdate', [CompanyController::class, 'updateroomfeaturetore'])->name('roomfeaturetoreupdate');
// Delete Room Features
route::get('deleteroomfeature', [CompanyController::class, 'deleteroomfeature']);
// Open Room Category
route::get('roomcategory', [CompanyController::class, 'openroomcat']);
// Print Room Category
Route::get('printroomcategory', [CompanyController::class, 'printRoomCategory'])->name('printroomcategory');
// Excel Export Room Category
Route::get('roomcategory/export', [CompanyController::class, 'exportRoomCategory'])->name('roomcategory.export');
// Submit Room Category
route::post('roomcatstore', [CompanyController::class, 'submitroomcat'])->name('roomcatstore');
// Remove Image Room Category
Route::post('removeimage', [CompanyController::class, 'removeimage'])->name('roomcategory.removeImage');
// Get Room Category Names
route::post('getchargeames', [CompanyController::class, 'getchargeames']);
// Open Update Room Category
route::get('updateroomcategory', [CompanyController::class, 'openupdateroomcat']);
// Update Room Category
route::post('roomcatupdate', [CompanyController::class, 'updateroomcat'])->name('roomcatupdate');
// Delete Room Category
route::get('deleteroomcat', [CompanyController::class, 'deleteroomcat']);
// Open Room Master
route::get('roommaster', [CompanyController::class, 'openroommaster']);
// Print Room Master
Route::get('printroommaster', [CompanyController::class, 'printRoomMaster'])->name('printroommaster');
// Excel Export Room Master
Route::get('roommaster/export', [CompanyController::class, 'exportRoomMaster'])->name('roommaster.export');
// Room Qr Code Generate
Route::post('roomqrgenerator', [RoomQRController::class, 'roomqrgenerater'])->name('roomqrgenerator');
// Open Page for generated qr
Route::get('order/outlet/{propertyid}/{outlet_code}/{rcode}/{comp_name}', [GeneralController::class, 'outletwiseitemshow'])->name('outlet.outletcode.compname');
// Order Place
Route::post('placeorder', [OrderController::class, 'placeorder'])->name('place.order');
// Submit Room Master
route::post('roommaststore', [CompanyController::class, 'submitroommast'])->name('roommaststore');
// Get Room Master Names
route::post('getroomnames', [CompanyController::class, 'getroomnames']);
// Open Update Room Master
route::get('updateroommast', [CompanyController::class, 'openupdateroommast']);
// Update Room Master
route::post('roommastupdate', [CompanyController::class, 'updateroommaster'])->name('roommastupdate');
// Delete Room Master
route::get('deleteroommaster', [CompanyController::class, 'deleteroommaster']);
// Open Plan Master
route::get('planmaster', [CompanyController::class, 'openplanaster']);
// Submit Plan Master
route::post('planststore', [CompanyController::class, 'submitplanmaster'])->name('planststore');
// Get Plan Master Names
route::post('getplannames', [CompanyController::class, 'getplannames']);
// Open Update Plan Master
route::get('updateplanmast', [CompanyController::class, 'openupdateplanmast']);
// Update Plan Master
route::post('planmastupdate', [CompanyController::class, 'updateplanmaster'])->name('planmastupdate');
// Delete Plan Master
route::get('deletplanmast', [CompanyController::class, 'deletplanmast']);
// Open Walk In Check In
route::get('walkincheckin', [CompanyController::class, 'openwalkin']);
// Fetch All Empty Rooms
Route::post('fetchallemptyrooms', [GeneralController::class, 'fetchallemptyrooms'])->name('fetchallemptyrooms');
// Fetch Company Details On Walkin Change
Route::post('walkincompdetail', [GeneralController::class, 'walkincompdetail'])->name('walkincompdetail');
// Find Guest History
Route::post('guesthistory', [Fetch::class, 'guesthistory'])->name('guesthistory');
// Open Prefilled Walkin Form
route::get('prefilledwalkin', [CompanyController::class, 'openprefilledwalkin']);
// Open Checkin List
route::get('checkinlist', [CheckinList::class, 'opencheckinlist']);
Route::get('checkinlist/data', [CheckinList::class, 'checkinlistData'])->name('checkinlist.data');
// Open Update Walkin
route::get('updatewalkin', [CompanyController::class, 'openupdatewalkin']);
// Update Checkout Time
Route::post('updatecheckouttime', [CheckinList::class, 'updatecheckouttime'])->name('updatecheckouttime');
// Fetch Plan Calculatio
Route::post('fetchplancacl', [Fetch::class, 'fetchplancacl'])->name('fetchplancacl');
// Check Edit Arrival Request
Route::get('checkeditarrival', [CompanyController::class, 'checkeditarrival'])->name('checkeditarrival');
// Walkin Submit
route::post('walkinsubmit', [CompanyController::class, 'submitwalkin'])->name('walkinsubmit');
// Submit Signature
Route::post('/save-signature', [CompanyController::class, 'saveSignature']);
// Update Walkin
route::post('walkinupdate', [CompanyController::class, 'updatewalkin'])->name('walkinupdate');
// Delete Walkin 
route::get('deletewalkin', [CompanyController::class, 'deletewalkin']);
// Open Add New Profile
Route::get('addnewprofile', [AddNewProfile::class, 'openaddnewprofile']);
// SUbmit Update New Profile
Route::post('updatenewprofile', [AddNewProfile::class, 'updatenewprofile'])->name('updatenewprofile');
// Walkin Guest Update Change Profile
route::post('walkinguestupdate', [CompanyController::class, 'walkinupdate'])->name('walkinguestupdate');
// Open Change Room
Route::get('roomchange', [CompanyController::class, 'openchangeroom'])->name('roomchange.route');
// Submit Room Change
Route::post('changeroomstore', [CompanyController::class, 'submitroomchange'])->name('changeroomstore');
// Open Charge Posting
route::get('openchargeposting', [CompanyController::class, 'openchargeposting']);
// Charges Posting 
route::post('chargespostingstore', [CompanyController::class, 'chargesposting'])->name('chargespostingstore');
// Open Advance Charge
Route::get('advcharge', [CompanyController::class, 'openadvancecharge'])->name('advcharge.route');
Route::get('guestledger/advance-receipt', [CompanyController::class, 'guestLedgerAdvanceReceiptPdf'])->name('guestledger.advance.receipt');
Route::get('guestledger/advance-entry', [CompanyController::class, 'guestLedgerAdvanceEntry'])->name('guestledger.advance.entry');
Route::post('guestledger/advance-entry/update', [CompanyController::class, 'updateGuestLedgerAdvanceEntry'])->name('guestledger.advance.update');
// Fetch Advance Amount
route::post('fetchadvamt', [CompanyController::class, 'fetchadvamt']);
// Fetch Advance Sum Amount From Paycharge
route::post('fetchadvamtpay', [CompanyController::class, 'fetchadvamtpay']);
// Fetch Nature Of Charge From Revmast
route::post('fetchrevnature', [CompanyController::class, 'fetchrevnature']);
// Submit Advance Charge
Route::post('advchargeformstore', [CompanyController::class, 'submitadvcahrge'])->name('advchargeformstore');
// Open Room SettleMent
Route::get('billsettle', [CompanyController::class, 'openroomsettlement'])->name('billsettle.route');
// Check Amount Payable
Route::post('checkamountpayable', [InhouseAction::class, 'checkamountpayable'])->name('checkamountpayable');
// Fetch Room SettleMent
route::post('fetchadvamt', [CompanyController::class, 'fetchadvamt']);
// // Fetch Room SettleMent Sum Amount From Paycharge
// route::post('fetchadvamtpay', [CompanyController::class, 'fetchadvamtpay']);
// Submit Room SettleMent
Route::middleware(['protect.route'])->group(function () {
    Route::post('roomsettlestore', [RoomSettlement::class, 'submitroomsettle'])->name('roomsettlestore');
});

// Open Payroll Parameter
Route::get('hrparameter', [PayrollParameter::class, 'index'])->name('hrparameter');
// Update Payroll
Route::post('payrollupdate', [PayrollParameter::class, 'updatepayroll'])->name('payrollupdate');
// Open Leave
Route::get('leave', [LeaveController::class, 'index'])->name('leave');
// Leave Submit
Route::post('leavestore', [LeaveController::class, 'store'])->name('leavestore');
// Show Leave Records
Route::get('showleave', [LeaveController::class, 'show'])->name('showleave');
// Edit Leave
Route::get('leaveedit/{sn}', [LeaveController::class, 'edit'])->name('leaveedit');
// Update Leave
Route::post('leaveupdate/{sn}', [LeaveController::class, 'update'])->name('leaveupdate');
// Delete Leave
Route::get('leavedelete/{sn}', [LeaveController::class, 'destroy'])->name('leavedelete');
// Open OverTime
Route::get('overtimeentry', [OverTimeController::class, 'index'])->name('overtime');
// Overtime Submit
Route::post('overtimestore', [OverTimeController::class, 'store'])->name('overtimestore');
// Open Overtime Edit
Route::get('overtimeedit/{sn}/{empcode}', [OverTimeController::class, 'edit'])->name('overtimeedit');
// Update Overtime
Route::post('overtimeupdate/{sn}/{empcode}', [OverTimeController::class, 'update'])->name('overtimeupdate');
// Delete Overtime
Route::get('overtimedestroy/{sn}/{empcode}/{otdate}', [OverTimeController::class, 'destroy'])->name('overtimedestroy');
// Open Loan Advance Entry
Route::get('loanadvanceentry', [LoanAdvanceEntry::class, 'index'])->name('loanadvanceentry');
// Submit Loan Advance Entry
Route::post('loanadvanceentrystore', [LoanAdvanceEntry::class, 'store'])->name('loanadvanceentrystore');
// Edit Loan Advance Entry
Route::get('loanadvanceentryedit/{vno}/{empcode}', [LoanAdvanceEntry::class, 'edit'])->name('loanadvanceentryedit');
// Update Loan Advance Entry
Route::post('loanadvanceentryupdate/{vno}/{empcode}', [LoanAdvanceEntry::class, 'update'])->name('loanadvanceentryupdate');
// Delete Loan Advance Entry
Route::get('loanadvanceentrydestroy/{vno}/{empcode}', [LoanAdvanceEntry::class, 'destroy'])->name('loanadvanceentrydestroy');
// Open Salary Creation
Route::get('salarycreation', [SalaryController::class, 'index'])->name('salarycreation');
// Get Employee By defaprtment
Route::post('getemployees', [SalaryController::class, 'getemployee'])->name('getemployees');
// Submit Salary Creation
Route::post('salarycreationstore', [SalaryController::class, 'store'])->name('salarycreationstore');
// Salary Deletion
Route::post('salarydeletion', [SalaryController::class, 'destroy'])->name('salarydeletion');
// Submit Nill Settle
Route::post('nillsettle', [Pointofsale::class, 'nillsettle'])->name('nillsettle');
// Open Room Re SettleMent
Route::get('billresettle', [CompanyController::class, 'openbillresettlement']);
// Fetch Room Re Settlement Data
Route::post('fetchroomresettle', [Reporting::class, 'fetchroomresettle'])->name('fetchroomresettle');
// Update Room Re SettleMent
Route::post('roomsettlestoreupdate', [CompanyController::class, 'updateroomsettle'])->name('roomsettlestoreupdate');
// Get Reason Names
route::post('getreasons', [CompanyController::class, 'getreasons']);
// Load Rooms
Route::post('/getrooms', [RoomController::class, 'getRooms']);
// Load Plans
Route::post('/getplans', [RoomController::class, 'getPlans']);
// Load Rooms For Walkin
Route::post('/getroomswalkin', [RoomController::class, 'getRoomswalkin']);
// Get Max Room Allow
Route::post('/getmaxroomallow', [RoomController::class, 'getmaxroomallow'])->name('getmaxroomallow');
// Load Rate
Route::post('/gerate', [CompanyController::class, 'geRate']);
// Load Rate By Child
Route::post('/getrate2', [CompanyController::class, 'geRate2']);
// Load Rate By Room Code And Sum Of Child And Adult
Route::post('/getrate3', [CompanyController::class, 'geRate3']);
// Get City Names
route::post('sendcitycode', [CompanyController::class, 'walkinglocdata']);
// Open Reservations
Route::get('reservation', [CompanyController::class, 'openreservations'])->name('reservation');
// Quick Add Corporate Company from Reservation Page
Route::post('reservation/quick-add-company', [CompanyController::class, 'quickAddCompany'])->name('reservation.quickAddCompany');
// Quick Add Travel Agent from Reservation Page
Route::post('reservation/quick-add-travel-agent', [CompanyController::class, 'quickAddTravelAgent'])->name('reservation.quickAddTravelAgent');
// Quick Add City from Reservation Page
Route::post('reservation/quick-add-city', [CompanyController::class, 'quickAddCity'])->name('reservation.quickAddCity');
// Submit Reservations
Route::post('reservationsubmit', [CompanyController::class, 'reservationsubmit'])->name('reservationsubmit');
// Open Reservation List
route::get('reservationlist', [Reservation::class, 'openreservationlist'])->name('reservationlist');
// Reservation Mail Posting
Route::post('resmailposting', [Reservation::class, 'resmailposting'])->name('resmailposting');
// Open Reservation Letter Page
Route::get('resletter', [Reservation::class, 'openresletter']);
// Open Reservation Card Page
Route::get('rescard', [Reservation::class, 'openrescard']);
// Open Cancel Letter Page
Route::get('cancletter', [Reservation::class, 'opencancelletter']);
// Open Update Reservation
route::get('updatereservation', [CompanyController::class, 'openupdatereservation']);
// Update Reservation
route::post('reservationupdate', [UpdateReservation::class, 'updatereservation'])->name('reservationupdate');
// Delete Reservation
route::get('deletereservation', [CompanyController::class, 'deletereservation']);
// Update Cancel Data
route::get('updatecancel', [Reservation::class, 'updatecancel']);
// Reverse Cancel Data
route::get('revcancel', [CompanyController::class, 'revcancel']);
// Open Unit Master
route::match(['get', 'post'], 'unitmaster', [CompanyController::class, 'openunitmast']);
// Submit Unit Master
route::post('unitmaststore', [CompanyController::class, 'submitbunitmaster'])->name('unitmaststore');
// Get Unit Master Names
route::post('getunitnames', [CompanyController::class, 'getunitnames']);
// Update Unit Master
route::post('unitmaststoreupdate', [CompanyController::class, 'updateunitmaststore'])->name('unitmaststoreupdate');
// Delete Unit Master
route::get('deleteunitmast/{sn}/{ucode}', [CompanyController::class, 'deleteunitmast']);
// Open NC Type Master
route::get('nctypemaster', [CompanyController::class, 'opennctypemast']);
// Print NC Type Master
Route::get('printnctypemaster', [CompanyController::class, 'printNcTypeMaster'])->name('printnctypemaster');
// Excel Export NC Type Master
Route::get('nctypemaster/export', [CompanyController::class, 'exportNcTypeMaster'])->name('nctypemaster.export');
// Submit NC Type Master
route::post('nctypemasterstore', [CompanyController::class, 'submitbnctypemaster'])->name('nctypemasterstore');
// Get NC Type Master
route::post('getnctypenames', [CompanyController::class, 'getnctypenames']);
// Update NC Type Master
route::post('nctypemaststoreupdate', [CompanyController::class, 'updatenctypemaststore'])->name('nctypemaststoreupdate');
// Delete NC Type Master
Route::get('/deletenctypemast/{sn}/{ucode}', [CompanyController::class, 'deletenctypemast'])->name('deletenctypemast');
// Open Session Master
route::get('sessionnmaster', [CompanyController::class, 'opennsessionmast']);
// Print Session Master
Route::get('printsessionmaster', [CompanyController::class, 'printSessionMaster'])->name('printsessionmaster');
// Excel Export Session Master
Route::get('sessionnmaster/export', [CompanyController::class, 'exportSessionMaster'])->name('sessionmaster.export');
// Submit Session Master
route::post('sessionmasterstore', [CompanyController::class, 'submitsessionmaster'])->name('sessionmasterstore');
// Get Session Master
route::post('getsessionnames', [CompanyController::class, 'getsessionnames']);
// Update Session Master
route::post('sessionmaststoreupdate', [CompanyController::class, 'updatesessionmaststore'])->name('sessionmaststoreupdate');
// Delete Session Master
route::get('deletesessionmast/{sn}/{ucode}', [CompanyController::class, 'deletesessionmast']);
// Open Server Master
route::get('servermaster', [CompanyController::class, 'openservermast']);
// Print Server Master
Route::get('printservermaster', [CompanyController::class, 'printServerMaster'])->name('printservermaster');
// Excel Export Server Master
Route::get('servermaster/export', [CompanyController::class, 'exportServerMaster'])->name('servermaster.export');
// Submit Server Master
route::post('servermasterstore', [CompanyController::class, 'submitservermaster'])->name('servermasterstore');
// Get Server Master
route::post('getnctypenames', [CompanyController::class, 'getnctypenames']);
// Update Server Master
route::post('servermastupdateform', [CompanyController::class, 'updateservermaststore'])->name('servermastupdateform');
// Delete Server Master
route::get('deleteservermast/{sn}/{ucode}', [CompanyController::class, 'deleteservermast']);
// Open Sundry Setting
route::get('sundrysetting', [CompanyController::class, 'opensundrysetting']);
// Fetch Sundry Type Data
Route::post('fetchsundrytype', [CompanyController::class, 'fetchsundrytype'])->name('fetchsundrytype');
// Fetch Sundry Type Data2
Route::post('fetchsundrytype2', [CompanyController::class, 'fetchsundrytype2'])->name('fetchsundrytype2');
// Submit Sundry Setting 
Route::post('sundrysetstore', [CompanyController::class, 'sundrysettingsubmit'])->name('sundrysetstore');
// Open Update Sundry Setting
route::get('updatesundrysetting', [CompanyController::class, 'openupdatesundrysetting']);
// Update SUndry Setting
Route::post('updatesundry', [CompanyController::class, 'updatesundry'])->name('updatesundry');
// Open Purchase Sundry Setting
route::get('purchsundry', [CompanyController::class, 'openpurcsundrysetting']);
// Submit Purchase Sundry Setting 
Route::post('purcsundrysetstore', [CompanyController::class, 'purcsundrysettingsubmit'])->name('purcsundrysetstore');
// Open Update Purchase Sundry Setting
route::get('updatepurchasesundrysetting', [CompanyController::class, 'updatepurchasesundrysetting']);
// Update Purchase SUndry Setting
Route::post('updatepurcsundry', [CompanyController::class, 'updatepurcsundry'])->name('updatepurcsundry');
// Open Checklist
route::get('checklist', [CompanyController::class, 'checklist']);
// Submit Checklist
Route::post('checkliststore', [CompanyController::class, 'checklistsubmit'])->name('checkliststore');
// Update Checklist
Route::post('checklistupdate', [CompanyController::class, 'checklistupdate'])->name('checklistupdate');
// Delete Checklist
Route::post('checklistdelete', [CompanyController::class, 'checklistdelete'])->name('checklistdelete');
// Open Feedback Question Master
Route::get('feedbackquestion', [CompanyController::class, 'feedbackquestion'])->name('feedbackquestion');
// Store Feedback Question
Route::post('feedbackquestionstore', [CompanyController::class, 'feedbackquestionstore'])->name('feedbackquestionstore');
// Update Feedback Question
Route::post('feedbackquestionupdate', [CompanyController::class, 'feedbackquestionupdate'])->name('feedbackquestionupdate');
// Delete Feedback Question
Route::post('feedbackquestiondelete', [CompanyController::class, 'feedbackquestiondelete'])->name('feedbackquestiondelete');
// Open Amenities Master
Route::get('amenitiesmaster', [CompanyController::class, 'amenitiesmaster'])->name('amenitiesmaster');
// Get Items by Type (AJAX)
Route::get('amenitiesmaster/items', [CompanyController::class, 'amenitiesGetItems'])->name('amenitiesGetItems');
// Store Amenities Master
Route::post('amenitiesstore', [CompanyController::class, 'amenitiesstore'])->name('amenitiesstore');
// Update Amenities Master
Route::post('amenitiesupdate', [CompanyController::class, 'amenitiesupdate'])->name('amenitiesupdate');
// Delete Amenities Master
Route::post('amenitiesdelete', [CompanyController::class, 'amenitiesdelete'])->name('amenitiesdelete');
// Load House Keeping
route::get('loadhousekeeping', [CompanyController::class, 'housekeepingloadup']);
// Load Cleaning Type (hkcleaningtype table)
Route::get('loadhkcleaning', [HouseKeeping::class, 'cleaningtypeloadup'])->name('loadhkcleaning');
// Open Cleaning Type CRUD
Route::get('cleaningtype', [HouseKeeping::class, 'cleaningtype'])->name('cleaningtype');
// Submit Cleaning Type
Route::post('submitcleaningtype', [HouseKeeping::class, 'submitcleaningtype'])->name('submitcleaningtype');
// Update Cleaning Type
Route::post('updatecleaningtype', [HouseKeeping::class, 'updatecleaningtype'])->name('updatecleaningtype');
// Delete Cleaning Type
Route::post('deletecleaningtype', [HouseKeeping::class, 'deletecleaningtype'])->name('deletecleaningtype');
// Open HK Supervisor
Route::get('hksupervisor', [HouseKeeping::class, 'hksupervisor'])->name('hksupervisor');
// Submit HK Supervisor
Route::post('submithksupervisor', [HouseKeeping::class, 'submithksupervisor'])->name('submithksupervisor');
// Update HK Supervisor
Route::post('updatehksupervisor', [HouseKeeping::class, 'updatehksupervisor'])->name('updatehksupervisor');
// Delete HK Supervisor
Route::post('deletehksupervisor', [HouseKeeping::class, 'deletehksupervisor'])->name('deletehksupervisor');
// Load Store
route::get('loadstore', [CompanyController::class, 'storeloadup']);
// Open Pay Type Master
route::get('paymaster', [CompanyController::class, 'openpaytypemast']);
// Print Pay Master
Route::get('printpaymaster', [CompanyController::class, 'printPayMaster'])->name('printpaymaster');
// Excel Export Pay Master
Route::get('paymaster/export', [CompanyController::class, 'exportPayMaster'])->name('paymaster.export');
// Submit Pay Type Master
route::post('paytypemasterstore', [CompanyController::class, 'submitbpaytypemaster'])->name('paytypemasterstore');
// Get Pay Type Master
route::post('getpaytypenames', [CompanyController::class, 'getpaytypenames']);
// Get List Paytype Checkbox
route::get('getcheckboxes', [CompanyController::class, 'getcheckboxes']);
// Get Perfect Checked Data
route::post('getperfectcheckrows', [CompanyController::class, 'getperfectcheckrows']);
// Get Checked depart Pay Columns
route::get('getcheckeddatadppay', [CompanyController::class, 'getcheckeddatadppay']);
// Update Pay Type Master
route::post('paymaststoreupdate', [CompanyController::class, 'updatepaytypemaststore'])->name('paymaststoreupdate');
// Delete Pay Type Master
route::get('deletepaytype/{sn}/{code}', [CompanyController::class, 'deletepaytype']);
// Load Ledger
route::get('loadledger', [CompanyController::class, 'loadledger']);
// Delete Ledger
route::post('deleteguestledger', [CompanyController::class, 'deleteguestledger']);
// Load Room Service
route::get('loadroomservice', [CompanyController::class, 'loadroomservice']);
// Open Table Master
route::get('tablemaster', [CompanyController::class, 'opentablemast']);
// Submit Table Master
route::post('tablemasterstore', [CompanyController::class, 'submittablemaster'])->name('tablemasterstore');
// Get Table Master
route::post('gettablenames', [CompanyController::class, 'gettablenames']);
// Update Table Master
route::post('tablemastupdateform', [CompanyController::class, 'updatetablemaststore'])->name('tablemastupdateform');
// Delete Table Master
route::get('deletetablemast', [CompanyController::class, 'deletetablemast']);
// Load Outlets
route::get('loadoutlets', [CompanyController::class, 'loadoutlets']);
// permission Table
route::post('checkparam', [CompanyController::class, 'sidemenuperm']);
// Open Setup Outlet
route::get('setupoutlet', [CompanyController::class, 'opensetupoutlet']);
// Outlet COde Generator
Route::post('outletqrgenerater', [QRCodeController::class, 'outletqrgenerater'])->name('outletqrgenerater');
// Open qr outlet page
Route::get('outlet/{propertyid}/{outlet_code}/{comp_name}', [GeneralController::class, 'outletwiseitemshowoutlet'])->name('outlet.outletcode.compname');
// Open Public Item List
// Submit Setup Outlet
route::post('outletmasterstore', [CompanyController::class, 'submitoutlet'])->name('outletmasterstore');
// Get Setup Outlet
route::post('getoutletnames', [CompanyController::class, 'getoutletnames']);
// Update Setup Outlet
route::post('outletsetupupdate', [CompanyController::class, 'outletsetupupdate'])->name('outletsetupupdate');
// Delete Setup Outlet
route::get('deletesetupoutlet/{sn}/{short_name}/{dcode}', [CompanyController::class, 'deleteoutlet'])->name('deleteoutlet');
//Open Update Setup Outlet
route::post('getupdatedata', [CompanyController::class, 'getupdateoutlet']);
// Get Setup Outlet List
route::get('getoutletlist', [CompanyController::class, 'getoutletlist']);
// Open Sale Bill Entry
Route::get('salebillentry', [CompanyController::class, 'salebillentry'])
    ->name('salebillentry.route');
// Open Outlet List Data Table Change Entry
Route::get('tablechangeentry', [Pos::class, 'tablechangeentry'])
    ->name('tablechangeentry.route');
Route::get('pos_tablechangedynamic', [Pos::class, 'pos_tablechangedynamic']);
// Change Table Dynamic Submit
Route::post('changetblxhr', [Pos::class, 'changetblxhr']);
// Submit Table Change Entry
Route::post('tablechangesubmit', [Pos::class, 'tablechangesubmit'])->name('tablechangesubmit');
// Open Outlet List Data Table Booking
Route::get('tablebooking', [CompanyController::class, 'tablebooking'])
    ->name('tablebooking.route');
// Open Outlet List Data Bill Lockup
Route::get('billlockup', [Pos::class, 'billlockup'])
    ->name('billlockup.route');
// Open Outelet List Settlement Summary
Route::get('settlementsummary', [Pos::class, 'settlementsummary'])->name('settlementsummary');
// Open Outlet List Data Display Table
Route::get('displaytable', [Pos::class, 'displaytable'])
    ->name('displaytable.route');
// Submit Outlet List Data Display Table
Route::post('posdisplaysubmit', [CompanyController::class, 'posdiplayhandle'])->name('posdisplaysubmit');
// Open Outlet List Data Payment Received
Route::get('paymentreceived', [CompanyController::class, 'paymentreceived'])
    ->name('paymentreceived.route');
// Open Outlet List Data Split Bill
Route::get('splitbill', [CompanyController::class, 'splitbill'])
    ->name('splitbill.route');
// Open Outlet List Data Settlement Entry
Route::get('settlemententry', [Pos::class, 'settlemententry'])
    ->name('settlemententry.route');
// Fetch Settlement Entry Bill No Record
Route::post('setentrypos', [Pos::class, 'setentrypos']);

// Delete Settlement Bill
Route::post('deletebillxhr', [Pos::class, 'deletebillxhr'])->name('deletebillxhr');
// Open Outlet List Data Order Booking
Route::get('orderbooking', [CompanyController::class, 'orderbooking'])
    ->name('orderbooking.route');
// Open Outlet List Data Order Booking Advance
Route::get('orderbookingadvance', [CompanyController::class, 'orderbookingadvance'])
    ->name('orderbookingadvance.route');
// Open Room Status
Route::get('roomstatus', [CompanyController::class, 'openroomstatus'])->name('roomstatus');
// In House Room Status Open
Route::get('inhoseroomstatus', [RoomStatus::class, 'inhouseroomstatus'])->name('inhoseroomstatus');
// In House Room Status Data Fetch
Route::get('inhoseroomstatusfetch', [RoomStatus::class, 'inhoseroomstatusfetch'])->name('inhoseroomstatusfetch');
// Todays Arrivals
Route::get('todaysarrivals', [RoomStatus::class, 'todaysarrivals'])->name('todaysarrivals');
// Todays Arrivals By Date
Route::post('todaysarrivalsbydate', [RoomStatus::class, 'todaysarrivalsbydate'])->name('todaysarrivalsbydate');
//House keep get
Route::get('housekeepget', [CompanyController::class, 'housekeepget']);
// Test Room Open
route::get('testroom', function () {
    return view('property.testroom');
});
route::get('test', function () {
    return view('property.test');
});
// Get Backend Rooms
Route::post('roomget/', [CompanyController::class, 'roomget']);
// Get Backend Reservations
route::get('backend_reservations', [CompanyController::class, 'backend_reservations']);
route::get('roomtest', function () {
    return view('property.roomtest');
});
// Backend Room Create 
route::post('backendreservationcreate', [CompanyController::class, 'backendreservationcreate']);
// Backend Room Category Get
route::get('backendroomcategory', [CompanyController::class, 'backendroomcategory']);
// Room Category Get
route::get('roomcategoryget', [CompanyController::class, 'roomcategoryget']);
// All Room Count Get
Route::get('allroomcountget', [CompanyController::class, 'allroomcountget']);
// Room Count
route::post('roomcountget', [CompanyController::class, 'roomcountget']);
// Booked Room Get
route::get('bookedroomget', [RoomStatus::class, 'bookedroomget']);
// Checkout Room Get
route::get('checkoutroomget', [RoomStatus::class, 'checkoutroomget']);
// Reserved Room Get
route::get('reservedroomget', [RoomStatus::class, 'reservedroomget']);
// Fetch Availability
Route::post('/get-availability', [RoomStatus::class, 'getAvailability']);
// Fetch Max Voucher Entry
Route::post('getmaxvoucher', [Fetch::class, 'getmaxvoucher'])->name('getmaxvoucher');
// Fetch Room Inclusive
Route::get('/fetch-room-inclusive/{docid}', [RoomStatus::class, 'fetchroominclusive'])->name('fetch-room-inclusive');
// Open Item List
route::get('itemlist', [CompanyController::class, 'openitemlist']);
// Print Item List
Route::get('printitemlist', [CompanyController::class, 'printItemList'])->name('printitemlist');
// Excel Export Item List
Route::get('itemlist/export', [CompanyController::class, 'exportItemList'])->name('itemlist.export');
// Submit Item List
route::post('itemliststore', [CompanyController::class, 'submititemlist'])->name('itemliststore');
//Update Item List
route::post('itemlistupstore', [CompanyController::class, 'updateitemlist'])->name('itemlistupstore');
// Delete Item Master
route::get('deleteitemlist/{sn}/{ucode}', [CompanyController::class, 'deleteitemlist']);
// Open Inventory Item List
route::get('itemlists', [CompanyController::class, 'openitemlist']);
// Open Night Audit
route::get('opennightaudit', [CompanyController::class, 'opennightaudit']);
// Submit Night Audit
route::post('nightauditupgrade', [CompanyController::class, 'submitnightaudit'])->name('nightauditupgrade');
// Fetch Pending Kot Show
Route::post('pendingbillskot', [Pos::class, 'pendingbillskot'])->name('pendingbillskot');
// Fetch Pending Sale Bill Xhr
Route::get('salewarnxhr', [Pos::class, 'salewarnxhr'])->name('salewarnxhr');
// Open Night Audit2
route::get('opennightaudit2', [CompanyController::class, 'opennightaudit2']);
// Submit Night Audit2
route::post('nightauditdegrade', [CompanyController::class, 'submitnightaudit2'])->name('nightauditdegrade');
// Fetch Ncur For Sidebar
Route::get('ncurfetch', [CompanyController::class, 'ncurfetch'])->name('ncurfetch');
// Open Change Profile
Route::get('changeprofile', [HouseModelOperations::class, 'openchangeprofile'])->name('changeprofile.route');
// Open Guest Add Profile
Route::get('guestaddprofile', [HouseModelOperations::class, 'openguestaddprofile'])->name('guestaddprofile.route');
// GM-07: Guest Master (read-only browse)
Route::get('guestmaster', [HouseModelOperations::class, 'guestmaster'])->name('guestmaster');
Route::post('fetchguestmaster', [HouseModelOperations::class, 'fetchguestmaster'])->name('fetchguestmaster');
Route::post('guestmastervisits', [HouseModelOperations::class, 'guestmastervisits'])->name('guestmastervisits');
// Load Total mprof
Route::post('loadtotalmprof', [HouseModelOperations::class, 'loadtotalmprof'])->name('loadtotalmprof');
// Update Mprof
Route::post('updatemprof', [HouseModelOperations::class, 'updatemprof'])->name('updatemprof');
// Add New Guest Profile
Route::post('newguestprofileadd', [HouseModelOperations::class, 'newguestprofileadd'])->name('newguestprofileadd');
// Update New Profile By Guest Code
Route::post('profilechangeguestonly', [HouseModelOperations::class, 'profilechangeguestonly'])->name('profilechangeguestonly');
// Fetch Guest Particular Profile Only
Route::post('fetchguestparticulardata', [HouseModelOperations::class, 'fetchguestparticulardata'])->name('fetchguestparticulardata');
// Open Ammend Stay
Route::get('ammendstay', [CompanyController::class, 'openammendstay'])->name('ammendstay.route');
// Update Ammend Stay
Route::post('ammendstayupdate', [CompanyController::class, 'updateammendstay'])->name('ammendstayupdate');
// Open Guest Ledger
Route::get('guestledger', [CompanyController::class, 'openguestledger'])->name('guestledger.route');
// Open Guest Charge Ledger
Route::get('guestcharge', [CompanyController::class, 'openguestcharge'])->name('guestcharge.route');
// Fetch Charges Sum
Route::post('fetchchargessum', [ChargeController::class, 'fetchchargessum'])->name('fetchchargessum');
// Open Bill Print
Route::get('billprint', [CompanyController::class, 'openbillprint'])->name('billprint.route');
// Check KOT Pending on Bill Print
Route::post('chkkotpendingroom', [RoomController::class, 'chkkotpendingroom'])->name('chkkotpendingroom');
// Update Bill Submit
Route::post('billdatasubmit', [CompanyController::class, 'submitbillprint'])->name('billdatasubmit');
// Get Amount Fetch
route::post('getamountfetch', [CompanyController::class, 'getamountfetch']);
// Get Amount Fetch 2
route::post('getamountfetch2', [CompanyController::class, 'getamountfetch2']);
// Bill Cancel
Route::post('billcancel', [CompanyController::class, 'billcancel'])->name('billcancel');
// Fetch Bill Data
route::post('fetchbilldataledger', [CompanyController::class, 'fetchbilldataledger']);
// Bill Print Receipt Open
// route::get('billprintview', [CompanyController::class, 'billprintview']);
route::get('billprintview', function () {
    return view('property.billprintpdf');
});
// route::get('testprint', function () {
//     return view('property.testprint');
// }); // Removed — view was empty placeholder
// Bill Print Receipt Open 2
route::get('billreprintview2', function () {
    return view('property.prints.fom.billprintpdf2');
});
route::get('billreprintview2type2', function () {
    return view('property.prints.fom.billprintpdf2type2');
});
// Test Table Bill Print
route::get('billprinttable', function () {
    return view('property.billprinttable');
});
// Post Split
Route::post('postsplit', [CompanyController::class, 'postsplit'])->name('postsplit');
// Bill Print Receipt Open 2
// route::get('billreprintview2', [CompanyController::class, 'billreprintview']);
// Data Not Found
route::get('datanotfound', function () {
    return view('property.datanotfound');
})->name('datanotfound');
// Auto Refresh Main Page
route::get('autorefreshmain', function () {
    return view('property.autorefreshmain');
})->name('autorefreshmain');
// Open Bill Reprint
Route::get('/billreprint', [CompanyController::class, 'openbillreprint'])->name('billreprint');
// Update Bill Reprint Submit
Route::post('billredatasubmit', [CompanyController::class, 'submitbillreprint'])->name('billredatasubmit');
// EInvoice Generate
Route::post('generate-einvoice', [EInvoiceParameter::class, 'generateinvoice']);
// EInvoice Generate Sale
Route::post('generate-einvoice-sale', [EInvoiceParameter::class, 'generateinvoicesale']);
// EInvoice Generate Banquet
Route::post('generate-einvoice-banquet', [EInvoiceParameter::class, 'generateinvoicebanquet']);
// Cancel EInvoice
Route::post('cancel-einvoice', [EInvoiceParameter::class, 'canceleinvoice']);
// Get Company Details
route::post('getcompdetails', [CompanyController::class, 'getcompdetails']);
// Get Max Billed No FOM
Route::get('maxbilledno/{year}', [FetchDataFom::class, 'maxbilledno']);
// Get Max Voucher No Bill
route::get('getmaxvoucherbill', [CompanyController::class, 'getmaxvoucherbill']);
// Get Room Occ Data
route::post('getroomoccdata', [CompanyController::class, 'getroomoccdata']);
// Get Subgroup Data
route::post('getsubgroupdata', [CompanyController::class, 'getsubgroupdata']);
// Get Travel Data
route::post('gettraveldata', [CompanyController::class, 'gettraveldata']);
// Opem Lookup Rooms
route::get('openlookuproom', [CompanyController::class, 'openlookuproom']);
// Open Menu Group
route::get('menugroup', [CompanyController::class, 'openmenugroup']);
// Submit Menu Group
route::post('menugroupstore', [CompanyController::class, 'submitmenugroup'])->name('menugroupstore');
// Update Menu Group
route::post('menugroupstoreupdate', [CompanyController::class, 'updatemenugroup'])->name('menugroupstoreupdate');
// Delete Menu Group
route::get('deletemenugroup/{sn}/{code}', [CompanyController::class, 'deletemenugroup']);
// Open Advance Deposit
Route::get('advancedeposit', [CompanyController::class, 'openadvancedeposit'])->name('advancedeposit');
// Get Max ADRES No
route::post('getmaxadresno', [CompanyController::class, 'getmaxadresno'])->name('getmaxadresno');
// Submit Advance Deposit
route::post('advancedeposubmit', [Advance::class, 'submitadvdeposit'])->name('advancedeposubmit');
// Delete Advance Deposit
Route::post('deleteadvancedeposit/{docid}/{vno}', [CompanyController::class, 'deleteadvancedeposit'])->name('deleteadvancedeposit');
// Open Preforma Invoice
Route::get('preforma', [Reservation::class, 'openpreformainvoice'])->name('preforma');
// Get Max Proforma Invoice No
// Open Menu Item
route::get('menuitem', [CompanyController::class, 'openmenuitem']);
// Print Menu Item
route::get('printmenuitem', [CompanyController::class, 'printMenuItem'])->name('printmenuitem');
// Item Group Fetch By Restcode
Route::post('restxhr', [Pos::class, 'restxhr'])->name('restxhr');
// Get Item Data
route::post('getitemdata', [CompanyController::class, 'getitemdata'])->name('getitemdata');
// Submit Menu Item
route::post('menuitemstore', [CompanyController::class, 'submitmenuitem'])->name('menuitemstore');
// Get Update Data Menu Item
route::post('itemmastupdata', [CompanyController::class, 'getupdatemenuitem'])->name('itemmastupdata');
// Fetch Max Itemcode
route::get('getmaxitemcode', [CompanyController::class, 'getmaxitemcode'])->name('getmaxitemcode');
// Update Menu Item
route::post('menuitemstoreupdate', [CompanyController::class, 'updatemenuitem'])->name('menuitemstoreupdate');
// Fetch Current Financial Year
route::get('getcurfinyear', [CompanyController::class, 'getcurfinyear'])->name('getcurfinyear');
// Updating Table OF Sale Bill Entry
Route::post('salebillrows', [Pointofsale::class, 'salebillrows'])->name('salebillrows');
// Delete Menu Item
route::get('deletemenuitem/{sn}/{ucode}', [CompanyController::class, 'deletemenuitem']);
// Open Menu Category
route::get('menucategory', [CompanyController::class, 'openmenucategory']);
// Submit Menu Category
route::post('menucategorystore', [CompanyController::class, 'submitmenucategory'])->name('menucategorystore');
// Update Menu Catgeory Data
route::post('menucatupdata', [CompanyController::class, 'getupdatemenucategory'])->name('menucatupdata');
// Update Menu Category
route::post('menucategoryupdatestr', [CompanyController::class, 'updatemenucategory'])->name('menucategoryupdatestr');
// Delete Menu Category
route::get('deletemenucategory/{sn}/{Code}', [CompanyController::class, 'deletemenucategory']);
// Open Blank Grc
route::get('openblankgrc', [CompanyController::class, 'openblankgrc']);
// Blank Grc Form Print
route::get('blankgrcform', function () {
    $permission = revokeopen(141111);
    if (is_null($permission) || $permission->print == 0) {
        return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
    }
    return view('property.blankgrcform');
});
// Advance Receipt Form Print
route::get('advancereceipt', [CompanyController::class, 'advanceReceiptPdf'])->name('advancereceipt');
// Room Settle Print
route::get('roomsettleprint', function () {
    return view('property.roomsettleprint');
});
// Open Sundry Master
route::get('sundrymaster', [CompanyController::class, 'opensundrymaster']);
// Submit Sundry Master
route::post('sundrymaststore', [CompanyController::class, 'submittsundrymast'])->name('sundrymaststore');
// Update Sundry Master
route::post('sundryupdatestore', [CompanyController::class, 'updatesundrymast'])->name('sundryupdatestore');
// Delete Sundry Master
route::get('deletesundrymast/{sn}/{ucode}', [CompanyController::class, 'deletesundrymast']);
// Return Enviro Form Data
route::get('enviroform', [Reporting::class, 'enviroform']);
// Check Bill already printed
Route::post('checkbillalreadyprinted', [Fetch::class, 'checkbillalreadyprinted'])->name('checkbillalreadyprinted');
// Check Post Charges Count For Single Room
Route::post('checkchargecount', [Fetch::class, 'checkchargecount'])->name('checkchargecount');
// Post Charge For 1 Room
Route::post('postchargesone', [Fetch::class, 'postchargesone'])->name('postchargesone');
// Open Menu Item Copy
Route::get('menuitemcopy', [Pos::class, 'menuitemcopy']);
// Ftech Item Details By Depart Code
Route::post('menuitemxhr', [Pos::class, 'menuitemxhr'])->name('menuitemxhr');
// Menu Item Copy Submit
Route::post('menuitemcopystore', [Pos::class, 'submitmenuitem'])->name('menuitemcopystore');
// Depart XHR
Route::post('departxhr', [Pointofsale::class, 'departxhr']);
Route::get('mobile', [Pos::class, 'mobilefill']);
Route::post('/mobilesubmit', [Pos::class, 'mobilesubmit']);
Route::get('maxmobiledata', [Pos::class, 'maxmobiledata']);
// Load Animation Page
Route::get('animation', function () {
    return view('property.salebillcancel');
});
Route::get('printkot', [Printing::class, 'printkot'])->name('printkot');
// Get Property Details
Route::post('getproperty', [PythonAuth::class, 'getproperty'])->name('getproperty');
// Open Department Master
Route::get('departmaster', [Pos::class, 'opendepartmaster']);
// Submit Department Master
Route::post('submitdepartmast', [Pos::class, 'submitdepartmast'])->name('submitdepartmast');
// Fetch All Depart
Route::get('fetchalldepart', [Pos::class, 'fetchalldepart'])->name('fetchalldepart');
// Delete Depart
Route::get('deletedepart', [Pos::class, 'deletedepart'])->name('deletedepart');
// Open Update Depart
Route::get('updatedepart', [Pos::class, 'openupdatedepart'])->name('updatedepart');
// Update Department Master
Route::post('updatedepartmast', [Pos::class, 'updatedepartmast'])->name('updatedepartmast');
// Open Expense Entry
Route::get('expsheet', [Pos::class, 'expenseentry'])->name('expenseentry');
// Submit Expense Entry
Route::post('expensesubmit', [Pos::class, 'expensesubmit'])->name('expensesubmit');
// Get Voucher Details
Route::post('voucherdetail', [Pos::class, 'voucherdetail'])->name('voucherdetail');
// Get expense Update Data
Route::post('editexpensedata', [Pos::class, 'editexpensedata'])->name('editexpensedata');
// Expense Update SUubmit
Route::post('expenseupdate', [Pos::class, 'expenseupdate'])->name('expenseupdate');
// Delete Expense Entry
Route::get('deleteexpenseentry/{sn}/{ucode}', [Pos::class, 'deleteexpenseentry'])->name('deleteexpenseentry');
// PrintExpense
Route::get('/print-expense/{docid}', [Pos::class, 'printExpense'])->name('print.expense');
// Open Party Master
Route::get('partymaster', [CompanyController::class, 'partymaster'])->name('partymaster');
// Open Update Party Master
Route::get('updatepartymaster', [CompanyController::class, 'updatepartymaster']);
// Delete Ledger
route::get('deletepartymaster/{sn}/{sub_code}', [CompanyController::class, 'deletepartymaster']);
// Open Item Group
route::get('itemgroup', [CompanyController::class, 'openitemgroup']);
// Print Item Group
Route::get('printitemgroup', [PrintController::class, 'printItemGroup'])->name('printitemgroup');
// Excel Export Item Group
Route::get('itemgroup/export', [PrintController::class, 'exportItemGroup'])->name('itemgroup.export');
// Submit Menu Group
route::post('itemgroupstore', [CompanyController::class, 'submititemgroup'])->name('itemgroupstore');
// Update Menu Group
route::post('itemgroupstoreupdate', [CompanyController::class, 'updateitemgroup'])->name('itemgroupstoreupdate');
// Delete item Group
route::get('deleteitemgroup/{sn}/{ucode}', [CompanyController::class, 'deleteitemgroup']);
// Delete Menu Group
route::get('deletemenugroup', [CompanyController::class, 'deletemenugroup']);
// Open Menu Category
route::get('itemcategory', [CompanyController::class, 'openitemcategory']);
// Print Item Category
Route::get('printitemcategory', [PrintController::class, 'printItemCategory'])->name('printitemcategory');
// Excel Export Item Category
Route::get('itemcategory/export', [PrintController::class, 'exportItemCategory'])->name('itemcategory.export');
// Submit Menu Category
route::post('itemcategorystore', [CompanyController::class, 'submititemcategory'])->name('itemcategorystore');
// Update Menu Category
route::post('itemcategoryupdatestr', [CompanyController::class, 'updateitemcategory'])->name('itemcategoryupdatestr');
// Delete item Category
route::get('deleteitemcategory/{sn}/{ucode}', [CompanyController::class, 'deleteitemcategory']);
// Open Inventory Item Entry
route::get('itementery', [CompanyController::class, 'openitementry']);
// Print Item Entry
Route::get('printitementery', [PrintController::class, 'printItemEntery'])->name('printitementery');
// Excel Export Item Entry
Route::get('itementery/export', [PrintController::class, 'exportItemEntery'])->name('itementery.export');
// Submit Inventory Item Entry
route::post('itementrystore', [CompanyController::class, 'itementrysubmit'])->name('itementrystore');
// Get Update Data Menu Item
route::post('itementryupdatedata', [CompanyController::class, 'getupdateitemcategory'])->name('itementryupdatedata');
// Update Inventory Item Entry
route::post('itementrystoreupdate', [CompanyController::class, 'updateitementry'])->name('itementrystoreupdate');
// Delete Menu entry
route::get('deletemenuentry/{sn}/{ucode}', [CompanyController::class, 'deletemenuentry']);
// Open MR Entry
Route::get('mrentry', [InventoryController::class, 'openmrentry']);
Route::get('mrentrylist-data', [InventoryController::class, 'mrEntryData'])->name('mrentry.data');
// Submit MR Entry
Route::post('mrentrysubmit', [InventoryController::class, 'mrentrysubmit'])->name('mrentrysubmit');
// MRCR Data
Route::post('mrentryparty', [InventoryController::class, 'mrentryparty'])->name('mrentryparty');
// Get Pending Po
Route::get('pendingpo', [InventoryController::class, 'pendingpo'])->name('pendingpo');
// Get Po Items
Route::get('pendingpoitems', [InventoryController::class, 'pendingpoitems'])->name('pendingpoitems');
// Check Duplicate Chalan
Route::post('checkduplicatechalan', [InventoryController::class, 'checkduplicatechalan'])->name('checkduplicatechalan');
// Check Duplicate Invoice
Route::post('checkduplicatememinvno', [InventoryController::class, 'checkduplicatememinvno'])->name('checkduplicatememinvno');
// Purchase Items
Route::get('purchaseitems', [InventoryController::class, 'purchaseitems'])->name('purchaseitems');
// Open Update MR Entry
Route::get('updatemrentry', [InventoryController::class, 'updatemrentry'])->name('updatemrentry');
// Update MR Entry
Route::post('mrentryupdate', [InventoryController::class, 'mrentryupdate'])->name('mrentryupdate');
// Delete MR Entry
Route::get('deletemrentry', [InventoryController::class, 'deletemrentry'])->name('deletemrentry');
// Open Purchase Bill
Route::get('purchasebill', [InventoryController::class, 'openpurchasebill'])->name('purchasebill');
// Purchase Bill Data
Route::post('purchasebillno', [InventoryController::class, 'purchasebillno'])->name('purchasebillno');
Route::post('stockissuerequistionbillno', [InventoryController::class, 'stockissuerequistionbillno'])->name('stockissuerequistionbillno');
// Get party Data
Route::post('partydata', [InventoryController::class, 'partydata'])->name('partydata');
// Get Purch INV TYPE
Route::post('getpurchvno', [InventoryController::class, 'getpurchvno'])->name('getpurchvno');
// Check Existingbillno
Route::post('checkbillno', [InventoryController::class, 'checkbillno'])->name('checkbillno');
// Get Mr Items
Route::post('mritems', [InventoryController::class, 'mritems'])->name('mritems');
// Party Wise Item Rate Fetch
Route::post('partywiserate', [InventoryController::class, 'partywiserate'])->name('partywiserate');
// Submit Purchase Bill
Route::post('purchasebillsubmit', [InventoryController::class, 'purchasebillsubmit'])->name('purchasebillsubmit');
// Delete Purchase Bill
Route::get('deletepurchbill', [InventoryController::class, 'deletepurchbill'])->name('deletepurchbill');
// Open Update Purchase Bill
Route::get('updatepurchasebill', [InventoryController::class, 'updatepurchasebill'])->name('updatepurchasebill');
// Update Purchase Bill
Route::post('purchasebillupdate', [InventoryController::class, 'purchasebillupdate'])->name('purchasebillupdate');
// Open Inventory Parameter
Route::get('invparameter', [InventoryController::class, 'invparameter'])->name('invparameter');
// Submit Inventory Parameter
Route::post('enviroentrysubmit', [InventoryController::class, 'enviroentrysubmit'])->name('enviroentrysubmit');
// Delete Inventory Parameter
Route::get('deleteinv', [InventoryController::class, 'deleteinv'])->name('deleteinv');
// invfi
Route::get('config', [ConfigController::class, 'config'])->name('config');
Route::get('/phpinfo', function () {
    return phpinfo();
});
// phpini
Route::get('/php-ini-path', [ConfigController::class, 'phpinipath']);
// Year and Updation
Route::get('yearandupdation', [FinancialPush::class, 'yearandupdation'])->name('yearandupdation');
// Submit Year And Update
Route::post('yearupdatesubmit', [FinancialPush::class, 'yearupdatesubmit'])->name('yearupdatesubmit');
// Open Account Posting
Route::get('accountposting', [ChargePosting::class, 'accountposting'])->name('accountposting');
// Submit Account Posting
Route::post('accountpoststore', [ChargePosting::class, 'accountpoststore'])->name('accountpoststore');
// Open Stock Stransfer
Route::get('stocktransfer', [InventoryController::class, 'stocktransfer'])->name('stocktransfer');
// Check Item Stock Val
Route::post('itemstockval', [InventoryController::class, 'itemstockval'])->name('itemstockval');
// Stock Transfer Submit
Route::post('stocktransfersubmit', [InventoryController::class, 'stocktransfersubmit'])->name('stocktransfersubmit');
// Open Update Stock Transfer
Route::get('updatestocktransfer/{vno}', [InventoryController::class, 'updatestocktransfer']);
// Update Stock Transfer
Route::post('stocktransferupdate', [InventoryController::class, 'stocktransferupdate'])->name('stocktransferupdate');
// Delete Stock Transfer
Route::get('deletestocktransfer/{vno}', [InventoryController::class, 'deletestocktransfer'])->name('deletestocktransfer');
// Print Stock Transfer
Route::get('printstocktransfer/{vno}', [PrintController::class, 'printstocktransfer'])->name('printstocktransfer');
// Open Opening Stock
Route::get('openingstock', [InventoryController::class, 'openingstock'])->name('openingstock');
// Print Opening Stock
Route::get('printopeningstock', [PrintController::class, 'printOpeningStock'])->name('printopeningstock');
// Excel Export Opening Stock
Route::get('openingstock/export', [PrintController::class, 'exportOpeningStock'])->name('openingstock.export');
// Submit Opening Stock
Route::post('openingstocksubmit', [InventoryController::class, 'openingstocksubmit'])->name('openingstocksubmit');
// Open Update Opening Stock
Route::get('updateopeningstock', [InventoryController::class, 'updateopeningstock']);
// Submit Update Opening Stock
Route::post('openingstockupdatesubmit', [InventoryController::class, 'openingstockupdatesubmit'])->name('openingstockupdatesubmit');
// Delete Opening Stock
Route::get('deleteopeningstock', [InventoryController::class, 'deleteopeningstock']);
// Department Existance Fetch
Route::post('departmentwise', [InventoryController::class, 'departmentwise'])->name('departmentwise');
// Open House Keeping
Route::get('housekeepingscreen', [HouseKeeping::class, 'housekeepingscreen']);
// HK QR Code Generator (Housekeeping Screen ke liye)
Route::post('hkqrgenerator', [HouseKeeping::class, 'hkQrGenerate'])->name('hkqrgenerator');
// Open House Keeping Master
Route::get('housemaster', [HouseKeeping::class, 'housemaster']);
// Submit House Master
Route::post('submithousemaster', [HouseKeeping::class, 'submithousemaster'])->name('submithousemaster');
// Update House Master
Route::post('updatehousemaster', [HouseKeeping::class, 'updatehousemaster'])->name('updatehousemaster');
// Delete House Keeping Master
Route::get('deletehousekeepingmaster/{sn}/{ucode}', [HouseKeeping::class, 'deletehousekeepingmaster'])->name('deletehousekeepingmaster');
// Open Floor Master
Route::get('floormaster', [HouseKeeping::class, 'floormaster'])->name('floormaster');
// Submit Floor Master
Route::post('submitfloormaster', [HouseKeeping::class, 'submitfloormaster'])->name('submitfloormaster');
// Update Floor Master
Route::post('updatefloormaster', [HouseKeeping::class, 'updatefloormaster'])->name('updatefloormaster');
// Delete Floor Master
Route::post('deletefloormaster', [HouseKeeping::class, 'deletefloormaster'])->name('deletefloormaster');
// Load HK Floors (Inconsistency Import)
Route::get('loadhkfloors', [HouseKeeping::class, 'floorloadup'])->name('loadhkfloors');
// Load HK Checklist (Inconsistency Import)
Route::get('loadhkchecklist', [HouseKeeping::class, 'checklistloadup'])->name('loadhkchecklist');
// Load HK Feedback Master (Inconsistency Import)
Route::get('loadhkfeedback', [HouseKeeping::class, 'feedbackloadup'])->name('loadhkfeedback');
// Save House Cleaning
Route::post('savehousecleaning', [HouseKeeping::class, 'savehousecleaning'])->name('savehousecleaning');
// Open update log form
Route::get('updatelogform', [HouseKeeping::class, 'updatelogform'])->name('updatelogform');
// Submit update log form
Route::post('submitupdatelogform', [HouseKeeping::class, 'submitupdatelogform'])->name('submitupdatelogform');
//post submenu 
Route::post('submenufetch', [HouseKeeping::class, 'submenufetch'])->name('submenufetch');
//pagenamefetch
Route::post('pagenamefetch', [HouseKeeping::class, 'pagenamefetch'])->name('pagenamefetch');
//delete updatelogform
Route::delete('deleteupdatelog', [HouseKeeping::class, 'deleteupdatelog'])->name('deleteupdatelog');
// Housekeeper Assignment Screen
Route::get('assignments', [HouseKeeping::class, 'assignmentreportprint'])->name('assignments');
Route::post('assignments/save', [HouseKeeping::class, 'saveAssignmentReport'])->name('assignments.save');
Route::post('assignments/unassign', [HouseKeeping::class, 'unassignRooms'])->name('assignments.unassign');
Route::get('assignments/print', [HouseKeeping::class, 'printAssignment'])->name('assignments.print');

//assignment view report
Route::post('assignments/view-report', [HouseKeeping::class, 'assignmentViewReport'])->name('assignments.viewreport');
Route::get('assignments/view', [HouseKeeping::class, 'assignmentViewPage'])->name('assignments.view');


// Open Housekeeping Status Report
Route::get('housekeepingstatusreport', [CompanyController::class, 'housekeepingstatusreport'])->name('housekeepingstatusreport');
// Fetch Housekeeping Status Report Data
Route::post('fetchhousekeepingstatusreport', [CompanyController::class, 'fetchhousekeepingstatusreport'])->name('fetchhousekeepingstatusreport')->middleware('report.cache');

Route::get('getpurchaseamount', [CompanyController::class, 'getPurchaseAmount'])->name('getPurchaseAmount');
// Fetch Purchase Amount
Route::post('getpurchaseamountsubmit', [CompanyController::class, 'getpurchaseamountsubmit'])->name('getpurchaseamountsubmit');
Route::get('printpurchaseamount', [CompanyController::class, 'printpurchaseamount'])->name('printpurchaseamount');
// Open GrcPrinting 
route::get('grcprinting', [CompanyController::class, 'opengrcprinting']);
// Print GRC
Route::get('printwalkin/{docid}', [PrintController::class, 'printwalkin'])->name('printwalkin');
// Open Merge Folio
Route::get('mergefolio', [RoomController::class, 'mergefolio'])->name('mergefolio');
// Fetch Merge Room Data
Route::get('mergeroomdata', [RoomController::class, 'mergeroomdata'])->name('mergeroomdata');
// Submit Merge Room
Route::post('mergeroompost', [RoomController::class, 'mergeroompost'])->name('mergeroompost');

// Print purchbill
Route::get('printpurchbill/{docid}', [PrintController::class, 'printpurchbill'])->name('printpurchbill');
// Open Reverse Merge Folio
Route::get('reversemergefolio', [RoomController::class, 'reversemergefolio'])->name('reversemergefolio');
// Fetch Merge Reverse Room Data
Route::get('mergereverseroomdata', [RoomController::class, 'mergereverseroomdata'])->name('mergereverseroomdata');
// Submit Reverse Merge Room
Route::post('mergereverseroompost', [RoomController::class, 'mergereverseroompost'])->name('mergereverseroompost');
// M R printing
Route::get('mrprinting/{docid}', [PrintController::class, 'mrprinting'])->name('mrprinting');
// stock register
Route::get('stockregister', [PrintController::class, 'stockregister'])->name('stockregister');
//stockreporttrade
Route::get('stockreporttrade', [PrintController::class, 'stockreporttrade'])->name('stockreporttrade');
// stock movement report
Route::get('stockmovementreport', [PrintController::class, 'stockmovementreport'])->name('stockmovementreport');
Route::post('getstockmovementdata', [PrintController::class, 'getstockmovementdata'])->name('getstockmovementdata');
// get Report Stock Trade type
Route::post('getreportstocktradetype', [PrintController::class, 'getreportstocktradetype'])->name('getreportstocktradetype');
// get Report Stock Trade type
Route::post('getdepartbytype', [PrintController::class, 'getdepartbytype'])->name('getdepartbytype');
// fetchGodownByStoreType 
Route::post('fetchGodownByStoreType', [PrintController::class, 'fetchGodownByStoreType'])->name('fetchGodownByStoreType');
// getitemsbygrouptread
Route::post('getitemsbygrouptread', [PrintController::class, 'getitemsbygrouptread'])->name('getitemsbygrouptread');
// getitemsbygrouptreadstocktrade
Route::post('getitemsbygrouptreadstocktrade', [PrintController::class, 'getitemsbygrouptreadstocktrade'])->name('getitemsbygrouptreadstocktrade');
// gettradeitemslist
Route::post('gettradeitemslist', [PrintController::class, 'gettradeitemslist'])->name('gettradeitemslist');
// Get Items By Group Name
Route::post('getitemsbygroup', [Fetch::class, 'getitemsbygroup'])->name('getitemsbygroup');
Route::get('actualdata', [PrintController::class, 'actualdata'])->name('actualdata');
Route::get('lprdata', [PrintController::class, 'lprdata'])->name('lprdata');
Route::post('/fetchValuationData', [PrintController::class, 'fetchValuationData'])->name('fetchValuationData');
// Open Requistion Slip Entry
Route::get('requisitionslip', [InventoryController::class, 'requisitionslip'])->name('requisitionslip');
// Print Requisition Slip
Route::get('printrequistionslip/{docid}', [PrintController::class, 'printrequistionslip'])->name('printrequistionslip');
// Print Stock Issue Requisition
Route::get('printstockissuerequisition/{docid}', [PrintController::class, 'printstockissuerequisition'])->name('printstockissuerequisition');
// Check Item Stock Val
Route::post('itemstockval', action: [InventoryController::class, 'itemstockval'])->name('itemstockval');
// Requistion Submit
Route::post('requisitionslipsubmit', [InventoryController::class, 'requisitionslipsubmit'])->name('requisitionslipsubmit');
// Open Update Requistion
Route::get('updaterequisitionslip/{vno}/{vprefix}/{vtype}', [InventoryController::class, 'updaterequisitionslip']);
// Update Requistion
Route::post('requisitionslipupdate', [InventoryController::class, 'requisitionslipupdate'])->name('requisitionslipupdate');
// Delete Requistion
Route::get('requisitionslipdelete/{docid}', [InventoryController::class, 'requisitionslipdelete'])->name('requisitionslipdelete');
// Open Verify Requistion Slip Entry
Route::get('verifyrequisition', [InventoryController::class, 'verifyrequisition'])->name('verifyrequisition');
// Open Verify Requistion
Route::get('requisitionslipverify/{docid}', [InventoryController::class, 'requisitionslipverify']);
// Submit Verify Requistion
Route::post('requisitionslipverifysub', [InventoryController::class, 'requisitionslipverifysub'])->name('requisitionslipverifysub');
// Open Stock Issue On Requistion
Route::get('stockissuerequisition', [InventoryController::class, 'stockissuerequisition']);
// Fetch Indent Items
Route::post('indentitems', [InventoryController::class, 'indentitems'])->name('indentitems');
// Submit Requistion Stock Issue
Route::post('requisitionstocksubmit', [InventoryController::class, 'requisitionstocksubmit'])->name('requisitionstocksubmit');
// Open Update Requistion Stock Issue
Route::get('updaterequisitionstockissue/{vno}/{vprefix}', [InventoryController::class, 'updaterequisitionstockissue']);
// Update Submit Requistion Stock Issue
Route::post('requisitionstockupsubmit', [InventoryController::class, 'requisitionstockupsubmit'])->name('requisitionstockupsubmit');
// Requistion Stock Issue Delete
Route::get('requisitionstockisuedelete/{vno}/{vprefix}', [InventoryController::class, 'requisitionstockisuedelete']);
// Open Indent Entry
Route::get('indent', [InventoryController::class, 'indent']);
// Indent Submit
Route::post('indentsubmit', [InventoryController::class, 'indentsubmit'])->name('indentsubmit');
// Open Update Indent
Route::get('updateindent/{docid}', [InventoryController::class, 'updateindent'])->name('updateindent');
// Update indent Submit
Route::post('indentupdate', [InventoryController::class, 'indentupdate'])->name('indentupdate');
// Delete Indent
Route::get('deleteindent/{docid}', [InventoryController::class, 'deleteindent'])->name('deleteindent');
// Open Excel View Page
Route::get('gstr1', [ExcelController::class, 'gstr1']);
// Submit Excel Save
Route::post('submitgstr1', [ExcelController::class, 'submitgstr1'])->name('submitgstr1');
// Download Excel
Route::get('excel/download', [ExcelController::class, 'download']);
// Get GSTR1 Data
Route::get('getGSTR1Data/{fromdate}/{todate}', [ExcelController::class, 'getGSTR1Data'])->name('getGSTR1Data');
// Get GSTR1 POS Data
Route::get('getGSTR1Datapos/{fromdate}/{todate}', [ExcelController::class, 'getGSTR1DataPOS'])->name('getGSTR1Datapos');
// Open banquet Master
Route::get('events', [PrintController::class, 'openbanquetmast']);
// Print Events
Route::get('printevents', [PrintController::class, 'printEvents'])->name('printevents');
// Excel Export Events
Route::get('events/export', [PrintController::class, 'exportEvents'])->name('events.export');
// Submit Server Master
Route::post('banquetmasterstore', [PrintController::class, 'submitbanquetmaster'])->name('banquetmasterstore');
// Get Server Master
Route::post('getnctypenames', [PrintController::class, 'getnctypenames']);
// Update Server Master
Route::post('banquetmastupdateform', [PrintController::class, 'updatebanquetmaststore'])->name('banquetmastupdateform');
// Delete Server Master
Route::get('deletebanquetmast/{sn}/{code}', [PrintController::class, 'deletebanquetmast']);
// Open Whatsapp Parameter
Route::get('smsscheduled', [WPParameter::class, 'smsscheduled']);
// Open Whatsapp Enviro
Route::get('whatsappenviro', [WPParameter::class, 'whatsappenviro']);
// Submit Whatsapp Enviro
Route::post('wpenvirosubmit', [WPParameter::class, 'wpenvirosubmit'])->name('wpenvirosubmit');
// Submit Whatsapp Enviro Front Office
Route::post('fomwpparamsubmit', [WPParameter::class, 'fomwpparamsubmit'])->name('fomwpparamsubmit');
// Submit Whatsapp Enviro Reservation
Route::post('reswpenvirosubmit', [WPParameter::class, 'reswpenvirosubmit'])->name('reswpenvirosubmit');
// Submit Whatsapp Enviro POS
Route::post('poswpenvirosubmit', [WPParameter::class, 'poswpenvirosubmit'])->name('poswpenvirosubmit');
// Check Whatsapp Balance
Route::get('/check-whatsapp-balance', [WPParameter::class, 'getwpenviro'])->name('getwpenviro');
// Trial Balance Open
Route::get('trailbalance', [FinanceController::class, 'trailbalance'])->name('trailbalance');
// Fetch Main Query Trial
Route::post('trialmainquery', [FinanceController::class, 'trialmainquery'])->name('trialmainquery');
// Month Wise Trial Fetch
Route::post('monthwisetrialfetch', [FinanceController::class, 'monthwisetrialfetch'])->name('monthwisetrialfetch');
// Trial Balance Month Row Fetch
Route::post('monthrowfetch', [FinanceController::class, 'monthrowfetch'])->name('monthrowfetch');
// Open Trial Group Balance 
Route::get('trailgroup', [FinanceController::class, 'trialgroupbalance'])->name('trailgroup');
// Fetch Main Query Trial Group
Route::post('trialgroupmainquery', [FinanceController::class, 'trialgroupmainquery'])->name('trialgroupmainquery');
// Subgroup row fetch
Route::post('fetchsubgroupdetails', [FinanceController::class, 'fetchsubgroupdetails'])->name('fetchsubgroupdetails');
// Profit Loss Open
Route::get('profitloss', [FinanceController::class, 'profitloss'])->name('profitloss');
// Fetch Main Query Profit Loss
Route::match(['get', 'post'], 'profitlossmainquery', [FinanceController::class, 'profitlossmainquery'])->name('profitlossmainquery');
// Profit Loss Second Query One Half
Route::match(['get', 'post'], 'profitlosssecondqueryhf', [FinanceController::class, 'profitlosssecondqueryhf'])->name('profitlosssecondqueryhf');
// Open Balance Sheet
Route::get('balancesheet', [FinanceController::class, 'balancesheet'])->name('balancesheet');
// Fetch Main Query Balance Sheet
Route::post('balancesheetmainquery', [FinanceController::class, 'balancesheetmainquery'])->name('balancesheetmainquery');
// Fetch SUbgrooup Details 2
Route::post('fetchsubgroupdetails2', [FinanceController::class, 'fetchsubgroupdetails2'])->name('fetchsubgroupdetails2');
// Open  Vneue Features
Route::get('venuefeatures', [PrintController::class, 'openvenuefeatures']);
// Print Venue Features
Route::get('printvenuefeatures', [PrintController::class, 'printVenueFeatures'])->name('printvenuefeatures');
// Excel Export Venue Features
Route::get('venuefeatures/export', [PrintController::class, 'exportVenueFeatures'])->name('venuefeatures.export');
// Submit Venue Features
Route::post('venuefeaturesstore', [PrintController::class, 'submitvenuefeatures'])->name('venuefeaturesstore');
// Update Venue Features
Route::post('venuefeaturesupdateform', [PrintController::class, 'updatevenuefeaturesstore'])->name('venuefeaturesupdateform');
// Delete Venue Features
Route::get('deletevenuefeatures/{sn}/{ucode}', [PrintController::class, 'deletevenuefeatures']);

// Open  Vneue master
Route::get('venuemaster', [PrintController::class, 'openvenuemaster']);
// Print Venue Master
Route::get('printvenuemaster', [PrintController::class, 'printVenueMaster'])->name('printvenuemaster');
// Excel Export Venue Master
Route::get('venuemaster/export', [PrintController::class, 'exportVenueMaster'])->name('venuemaster.export');
// Submit Venue master
Route::post('venuemasterstore', [PrintController::class, 'submitvenuemaster'])->name('venuemasterstore');
// Update Venue master
Route::post('venuemasterupdateform', [PrintController::class, 'updatevenuemasterstore'])->name('venuemasterupdateform');
// Delete Venue master
Route::get('deletevenuemaster/{sn}/{ucode}', [PrintController::class, 'deletevenuemaster']);
Route::get('banquetload', [CompanyController::class, 'banquetload']);

// Open Item Group
Route::get('itemgroups', [PrintController::class, 'openitemgroups']);
// Print Item Groups
Route::get('printitemgroups', [PrintController::class, 'printItemGroups'])->name('printitemgroups');
// Excel Export Item Groups
Route::get('itemgroups/export', [PrintController::class, 'exportItemGroups'])->name('itemgroups.export');
// Submit Menu Group
Route::post('itemgroupsstore', [PrintController::class, 'submititemgroups'])->name('itemgroupsstore');
// Update Menu Group
Route::post('itemgroupsstoreupdate', [PrintController::class, 'updateitemgroups'])->name('itemgroupsstoreupdate');
// Delete Menu Group
Route::get('deletemenugrou/{sn}/{ucode}', [PrintController::class, 'deletemenugroup']);

// Open Banquet Sundry Setting
Route::get('banquetbillsundrysetting', [PrintController::class, 'openbanqsundrysetting']);
// Submit Banquet Sundry Setting 
Route::post('banqsundrysetstore', [PrintController::class, 'banqsundrysettingsubmit'])->name('banqsundrysetstore');
// Open Update Banquet Sundry Setting
Route::get('updatebanquetsundrysetting', [PrintController::class, 'updatebanquetsundrysetting']);
// Update Banquet SUndry Setting
Route::post('updatebanqsundry', [PrintController::class, 'updatebanqsundry'])->name('updatebanqsundry');
// Open Menu Item
Route::get('menuitems', [PrintController::class, 'openmenuitems']);
// Item Group Fetch By Restcode
// Route::post('restxhr', [PrintController::class, 'restxhr'])->name('restxhr');
// Get Item Data
Route::post('getitemdata', [PrintController::class, 'getitemdata'])->name('getitemdata');
// Submit Menu Item
Route::post('menuitemsstore', [PrintController::class, 'submitmenuitems'])->name('menuitemsstore');
// Get Update Data Menu Item
Route::post('itemmastupdatabnq', [PrintController::class, 'getupdatemenuitems'])->name('itemmastupdatabnq');
// Fetch Max Itemcode
Route::get('getmaxitemcode', [PrintController::class, 'getmaxitemcode'])->name('getmaxitemcode');
// Update Menu Item
Route::post('menuitemsstoreupdate', [PrintController::class, 'updatemenuitems'])->name('menuitemsstoreupdate');
// Fetch Current Financial Year
Route::get('getcurfinyear', [PrintController::class, 'getcurfinyear'])->name('getcurfinyear');
// Updating Table OF Sale Bill Entry
Route::post('salebillrows', [Pointofsale::class, 'salebillrows'])->name('salebillrows');
// Delete Menu Item
Route::get('deletemenuitems/{sn}/{ucode}', [PrintController::class, 'deletemenuitems']);

// Open Menu Category
route::get('menucategorys', [PrintController::class, 'openmenucat']);
// Print Menu Categorys
Route::get('printmenucategorys', [PrintController::class, 'printMenuCategorys'])->name('printmenucategorys');
// Excel Export Menu Categorys
Route::get('menucategorys/export', [PrintController::class, 'exportMenuCategorys'])->name('menucategorys.export');
// Submit Menu Category
route::post('menucatstore', [PrintController::class, 'submitmenucat'])->name('menucatstore');
// Update Menu Category
route::post('menucatupdatestr', [PrintController::class, 'updatemenucat'])->name('itemcategoryupdatestr');
// Delete Menu Category
route::get('deletemenucat/{sn}/{ucode}', [PrintController::class, 'deletemenucategory']);
// Open Banquet Booking
Route::get('banquetbooking', [Banquet::class, 'openbanquetbooking'])->name('banquetbooking');
// Check Venue Duplicacy
Route::post('checkvenuduplicate', [Banquet::class, 'checkvenuduplicate'])->name('checkvenuduplicate');
// Check Venue Duplicacy
Route::post('checkvenuduplicateup', [Banquet::class, 'checkvenuduplicateup'])->name('checkvenuduplicateup');
// Submit Banquet Booking
Route::post('banquetbookingsubmit', [Banquet::class, 'banquetbookingsubmit'])->name('banquetbookingsubmit');
// Booking Enquiry Fetch
Route::post('banqenquieryfetch', [Banquet::class, 'banqenquieryfetch'])->name('banqenquieryfetch');
// Open Banquet Parameter
Route::get('banquetparameter', [Banquet::class, 'banquetparameter'])->name('banquetparameter');
// Submit Banquet Parameter
Route::post('submitbanquetparameter', [Banquet::class, 'submitbanquetparameter'])->name('submitbanquetparameter');
Route::post('submitbanquetparameterfp', [Banquet::class, 'submitbanquetparameterfp'])->name('submitbanquetparameterfp');
Route::post('/banquetadvinstruction', [Banquet::class, 'banquetadvinstruction'])->name('banquetadvinstruction');
Route::post('banquetparambillno', [Banquet::class, 'banquetparambillno'])->name('banquetparambillno');
// Open Advance banquetparambillno
Route::get('advanceabanquet/{docid}', [Banquet::class, 'advanceabanquet'])->name('advanceabanquet');
// Submit Advance Banquet
Route::post('advancebanquetsubmit', [Banquet::class, 'advancebanquetsubmit'])->name('advancebanquetsubmit');
// Delete Advance Banquet
Route::post('deleteadvancebanquet/{docid}', [Banquet::class, 'deleteadvancebanquet'])->name('deleteadvancebanquet');
// Open Banquet Advance Receipt
route::get('banquetadvancereceipt', function () {
    return view('property.banquetadvancereceipt');
});

// Open Update Banquet Booking
Route::get('updatebanquet/{docid}', [Banquet::class, 'updatebanquet'])->name('updatebanquet');
// Delete Banquet
Route::get('deletebanquet/{docid}', [Banquet::class, 'deletebanquet'])->name('deletebanquet');
// Update Banquet Booking
Route::post('banquetbookingupdate', [Banquet::class, 'banquetbookingupdate'])->name('banquetbookingupdate');
// Open Banquet Billing
Route::get('banquetbilling', [Banquet::class, 'banquetbilling'])->name('banquetbilling');
// Deepak Route  
// Open Performa Invoice
Route::get('performainvoice', [Banquet::class, 'performaInvoice'])->name('performainvoice');
// Hall Book Fetch
Route::get('hallbookfetch/{docid}', [Banquet::class, 'hallbookfetch'])->name('hallbookfetch');
// Fetch Banquet Items
Route::get('banquetitems', [Banquet::class, 'banquetitems'])->name('banquetitems');
// Submit Banquet Billing
Route::post('banquetbillingsubmit', [Banquet::class, 'banquetbillingsubmit'])->name('banquetbillingsubmit');
// Deepak Route 
// Open Performa Invoice Submit
Route::post('performainvoicesubmit', [Banquet::class, 'performaInvoiceSubmit'])->name('performainvoicesubmit');
// Update Banquet Billing
Route::post('banquetbillingupdate', [Banquet::class, 'banquetbillingupdate'])->name('banquetbillingupdate');
// Deepak Route 
// Open Performa Invoice Update
Route::post('performainvoiceupdate', [Banquet::class, 'performaInvoiceUpdate'])->name('performainvoiceupdate');
// Delete Banquet Bill
Route::post('deletebanquetbill', [Banquet::class, 'deletebanquetbill'])->name('deletebanquetbill');
// Deepak Route
// Delete Performa Invoice Delete
Route::post('deleteperformainvoice', [Banquet::class, 'deletePerformaInvoice'])->name('deleteperformainvoice');
// Hall Sale Fetch
Route::get('hallsalefetch/{docid}', [Banquet::class, 'hallsalefetch'])->name('hallsalefetch');
// Hall Performa Invoice Sale Fetch
Route::get('performahallsalefetch/{docid}', [Banquet::class, 'performaHallsalefetch'])->name('performahallsalefetch');

// Open Hall Bill Settlement
Route::get('hallbillsettle/{docid}', [Banquet::class, 'hallbillsettle'])->name('hallbillsettle');
// Fetch Advance Sum Amount From PaychargeH
Route::post('fetchadvamtpayhall', [Banquet::class, 'fetchadvamtpayhall']);
// Banquet Bill Submit
Route::post('banquetbillsubmit', [Banquet::class, 'banquetbillsubmit'])->name('banquetbillsubmit');
// Banquet Bill Print
Route::get('banquetbillprint/{docid}', [Banquet::class, 'banquetbillprint'])->name('banquetbillprint');
// Deepak Route 
// Open Performa Invoice Print
Route::get('performainvoiceprint/{docid}', [Banquet::class, 'performaInvoicePrint'])->name('performainvoiceprint');
//open Print fp
Route::get('printfp/{docid}', [PrintController::class, 'openprintfp'])->name('printfp');
//open Print fp
// Route::get('printfp/{docid}', [PrintController::class, 'openprintfp'])->name('printfp');
//Open Sales Register
Route::get('banqsalesreg', [PrintController::class, 'opensalesregister'])->name('banqsalesreg');
// Fetch Sale Register
Route::post('fetchsalesregister', [PrintController::class, 'fetchsalesregister'])->name('fetchsalesregister');
// Open Outelet List Settlement Summary
Route::get('banqsettlementsummary', [PrintController::class, 'banqsettlementsummary'])->name('banqsettlementsummary');
// Fetch Banquet Settlement
Route::post('banqsettlefetch', [Banquet::class, 'banqsettlefetch'])->name('banqsettlefetch');
// Open Venu Availability
Route::get('venueavailability', [Banquet::class, 'venueavailability'])->name('venueavailability');
// Fetch Availibility Banquet
Route::post('availablitybanquet', [Banquet::class, 'availablitybanquet'])->name('availablitybanquet');
// Open Venue Availability Day Wise
Route::get('venuestatus', [Banquet::class, 'venueavailabilitydaywise'])->name('venuestatus');
// Fetch Availability Day Wise
Route::post('availablitybanquetdaywise', [Banquet::class, 'availablitybanquetdaywise'])->name('availablitybanquetdaywise');
// Open Banquet Outstanding Report
Route::get('banqoutstanding', [Banquet::class, 'banqoutstanding'])->name('banqoutstanding');
// Fetch Banquet Outstanding
Route::post('banqoutstandingfetch', [Banquet::class, 'banqoutstandingfetch'])->name('banqoutstandingfetch');
// Open Booking Enquiry
Route::get('bookinginquiry', [BookingInquiryController::class, 'bookingenquiry'])->name('bookingenquiry');
// Submit Booking Enquiry
Route::post('/booking-inquiry', [BookingInquiryController::class, 'store'])->name('bookinginquiry.store');
// Open Update Booking Enquiry
Route::get('updatebanquetenquiry/{inqno}', [BookingInquiryController::class, 'updatebanquetenquiry']);
// Update Banquet Enquiry
Route::post('/bookinginquiry//update', [BookingInquiryController::class, 'update'])->name('bookinginquiry.update');
// Delete Banquet Enquiry
Route::get('deletebanquetenquiry/{inqno}', [BookingInquiryController::class, 'deletebanquetenquiry']);
// Calculate Round Sale Bill Entry
Route::post('calculateroundoffpos', [GeneralController::class, 'calculateroundoffpos'])->name('calculateroundoffpos');
// Calculate Round Off Purchase Bill
Route::post('calculateroundpurch', [GeneralController::class, 'calculateroundpurch'])->name('calculateroundpurch');
// Calculate Round Off Banquet Billing
Route::post('calculateroundbanquet', [GeneralController::class, 'calculateroundbanquet'])->name('calculateroundbanquet');
// Open Member Category
Route::get('member/category', [MemberCategoryController::class, 'openmembercategory']);
// Member Category Submit
Route::post('/member/category/store', [MemberCategoryController::class, 'categorystore'])->name('member.categorystore');
// Open Member Category Update
Route::get('member/category/update/{id}', [MemberCategoryController::class, 'editcategory'])->name('member.category.edit');
// Update submit Member Category
Route::put('category/update/{code}', [MemberCategoryController::class, 'updatecategory'])->name('member.category.update');
// Delete member category
Route::get('member/category/delete/{code}', [MemberCategoryController::class, 'deletecategory'])->name('member.category.delete');
// Open Member Master
Route::get('member/master', [MemberMasterController::class, 'openmembermaster'])->name('member.master');
// Submit Member Master
Route::post('/member/store', [MemberMasterController::class, 'store'])->name('member.store');
// Open Member Master Update
Route::get('member/master/update/{id}', [MemberMasterController::class, 'editmaster'])->name('member.master.edit');
// Update submit Member Master
Route::put('master/update/{code}', [MemberMasterController::class, 'updatemaster'])->name('member.master.update');
// Delete member master
Route::get('member/master/delete/{code}', [MemberMasterController::class, 'deletemaster'])->name('member.master.delete');
// Open Member Facility Master
Route::get('member/memfacilitymast', [MemberFacilityMasterController::class, 'index'])->name('member.memfacilitymast');
// Submit Member Facility Master
Route::post('/member/facilitymast/store', [MemberFacilityMasterController::class, 'store'])->name('member.facilitymast.store');
// Open Update MemberFacility Master
Route::get('/member/memberfacility/update/{code}', [MemberFacilityMasterController::class, 'update'])->name('member.memberfacility.update');
// Submit Update MemberFacility Master
Route::put('/member/facilitymast/updatestore/{code}', [MemberFacilityMasterController::class, 'updatestore'])->name('member.facilitymast.updatestore');
// Delete MemberFacility Master
Route::get('/member/memberfacility/delete/{code}', [MemberFacilityMasterController::class, 'delete'])->name('member.memberfacility.delete');
// Open Card Initilization
// REMOVED — Smart Card stubs (no implementation, no model, no DB table)
// Route::get('smartcard/cardinitialization', ...);
// Route::post('smartcard/cardinitialization/store', ...);
// Route::get('smartcard/cardregistration', ...);
// Route::post('smartcard/cardregistration/store', ...);
// Route::get('smartcard/cardrecharge', ...);
// Route::post('smartcard/cardrecharge/store', ...);
// Route::get('smartcard/cardrefund', ...);
// Route::post('smartcard/cardrefund/store', ...);
// Open Public Property Page
Route::get('hotels/{propertyid}', [HotelsController::class, 'index'])->name('hotels.list');
// Open Hotel Booking Page
// Route::get('hotelbooking/{propertyid}/booking', [HotelsController::class, 'hotelbooking'])->name('hotelbooking');
Route::get('/hotels/{propertyid}/booking', [HotelsController::class, 'hotelbooking'])->name('hotelbooking');
// Submit Hotel Booking
Route::post('hotelbookingsubmit', [HotelBookingController::class, 'store'])->name('hotelbookingsubmit');
// Fetch Datewise Empty Room Categories list
Route::post('fetchdatewiseemptyroomcat/{propertyid}', [HotelsController::class, 'fetchdatewiseemptyroomcat'])->name('fetchdatewiseemptyroomcat');
// Call Crypto Encryption
// Route::get('cryptoencryption', [UpgradingExpCrypt::class, 'encryptexp'])->name('cryptoencryption');
// Test Email Send
Route::get('testemailsend', [MailController::class, 'sendTestMail'])->name('testemailsend');
// Hotel booking voucher download - Frontend Route
Route::get('hotels/{propertyid}/hotelbooking/voucher/{docid}', [HotelBookingController::class, 'downloadVoucher'])->name('hotelbookingvoucher');
// Hotel Booking Thank You Page - Frontend Route
Route::get('hotels/{propertyid}/hotelbooking/thankyou', [HotelBookingController::class, 'thankyou'])->name('hotelbookingthankyou');
// Aadhar Front Scanner
Route::get('aadharfrontscanner', function () {
    return view('test.aadharfrontscanner');
});
// Open Finance Parameter
Route::get('financeparameter', [FinanceEnviro::class, 'financeparameter'])->name('financeparameter');
// Submit Finance Parameter
Route::post('financeparametersubmit', [FinanceEnviro::class, 'financeparametersubmit'])->name('financeparametersubmit');
// Get Voucher Enviro Data
Route::post('getvoucherentrydata', [FinanceEnviro::class, 'getvoucherentrydata'])->name('getvoucherentrydata');
// Submit Voucher Enviro
Route::post('voucherentryupdate', [FinanceEnviro::class, 'voucherentryupdate'])->name('voucherentryupdate');
// Open Voucher Entry
Route::get('voucherentry', [VoucherEntry::class, 'voucherentry'])->name('voucherentry');
// Tds Entry Checkup
Route::post('tdsentrycheckup', [VoucherEntry::class, 'tdsentrycheckup'])->name('tdsentrycheckup');
// Get Voucher Entry Data
Route::post('getvoucherentrydatavr', [VoucherEntry::class, 'getvoucherentrydatavr'])->name('getvoucherentrydatavr');
// Get Voucher Entry Data Vno
Route::post('getvoucherentrydatavno', [VoucherEntry::class, 'getvoucherentrydatavno'])->name('getvoucherentrydatavno');
// Get Particular Sum
Route::post('checksubledger', [VoucherEntry::class, 'checksubledger'])->name('checksubledger');
// Submit Voucher Entry
Route::post('savevoucherentry', [VoucherEntry::class, 'savevoucherentry'])->name('savevoucherentry');
// Edit Voucher Entry
Route::get('editvoucherentry/{docid}', [VoucherEntry::class, 'editvoucherentry'])->name('editvoucherentry');
// Update Voucher Entry
Route::post('updatevoucherentry', [VoucherEntry::class, 'updatevoucherentry'])->name('updatevoucherentry');
// Delete Voucher Entry
Route::get('deletevoucherentry/{docid}', [VoucherEntry::class, 'deletevoucherentry'])->name('deletevoucherentry');
// Print Voucher Entry
Route::get('printvoucherentry/{docid}', [VoucherEntry::class, 'printvoucherentry'])->name('printvoucherentry');
// Print Voucher Cheque
Route::get('printvouchercheque/{docid}', [VoucherEntry::class, 'printvouchercheque'])->name('printvouchercheque');
// Purchase Order
Route::get('purchaseorder', [PurchaseOrderController::class, 'purchaseorder'])->name('purchaseorder');
// Fetch Indent Items
Route::post('pendingindentitems', [PurchaseOrderController::class, 'pendingindentitems'])->name('pendingindentitems');
// Submit Purchase Order
Route::post('purchaseordersubmit', [PurchaseOrderController::class, 'purchaseordersubmit'])->name('purchaseordersubmit');
// Open Update Purchase Order
Route::get('updatepurchaseorder/{docid}', [PurchaseOrderController::class, 'updatepurchaseorder'])->name('updatepurchaseorder');
// Update Purchase Order
Route::post('purchaseorderupdate', [PurchaseOrderController::class, 'purchaseorderupdate'])->name('purchaseorderupdate');
// Delete Purchase Order
Route::get('deletepurchaseorder/{docid}', [PurchaseOrderController::class, 'deletepurchaseorder'])->name('deletepurchaseorder');
// Get Purchase Order Print
Route::get('purchaseorderprint/{docid}', [PurchaseOrderController::class, 'purchaseorderprint'])->name('purchaseorderprint');

// Quotation
Route::get('quotation', [QuotationController::class, 'quotation'])->name('quotation');
// Submit Quotation
Route::post('quotationsubmit', [QuotationController::class, 'quotationsubmit'])->name('quotationsubmit');
// Open Update Quotation
Route::get('updatequotation/{docid}', [QuotationController::class, 'updatequotation'])->name('updatequotation');
// Update Quotation
Route::post('quotationupdate', [QuotationController::class, 'quotationupdate'])->name('quotationupdate');
// Delete Quotation
Route::get('deletequotation/{docid}', [QuotationController::class, 'deletequotation'])->name('deletequotation');
// Get Quotation Print
Route::get('quotationprint/{docid}', [QuotationController::class, 'quotationprint'])->name('quotationprint');
// Party wise Quotation Rate
Route::post('partywisequotationrate', [QuotationController::class, 'partywisequotationrate'])->name('partywisequotationrate');

// stock summary
Route::get('stocksummary', [PrintController::class, 'stocksummary'])->name('stocksummary');
// fetchGodown 
Route::post('fetchGodown', [PrintController::class, 'fetchGodown'])->name('fetchGodown');
// Get Items By Group Name
Route::post('getitems', [Fetch::class, 'getitems'])->name('getitems');
Route::get('actData', [PrintController::class, 'actData'])->name('actData');
Route::get('lprateData', [PrintController::class, 'lprateData'])->name('lprateData');
Route::post('/fetchValuation', [PrintController::class, 'fetchValuation'])->name('fetchValuation');

// stock in hand
Route::get('stockinhand', [PrintController::class, 'stockinhand'])->name('stockinhand');
// fetchstockingodown 
Route::post('fetchstockingodown', [PrintController::class, 'fetchstockingodown'])->name('fetchstockingodown');
// Get stockitems By Group Name
Route::post('getstockitems', [Fetch::class, 'getstockitems'])->name('getstockitems');
Route::post('stockInHandFinal', [PrintController::class, 'stockInHandFinal'])->name('stockInHandFinal');
// issue check in
Route::get('issuechecklist', [PrintController::class, 'issuechecklist'])->name('issuechecklist');
Route::get('/getItemsByType', [PrintController::class, 'getItemsByType'])->name('getItemsByType');
Route::get('/getStockReport', [PrintController::class, 'getStockReport'])->name('getStockReport');

//Purchase register
Route::get('purchaseregister', [PrintController::class, 'purchaseregister'])->name('purchaseregister');
Route::post('/finalpurchaseregister', [PrintController::class, 'finalpurchaseregister'])->name('finalpurchaseregister');
// Open E Invoice Parameter
Route::get('einvoiceparameter', [EInvoiceParameter::class, 'einvoiceparameter'])->name('einvoiceparameter');
// Submit E Invoice Parameter
Route::post('einvoiceparametersubmit', [EInvoiceParameter::class, 'einvoiceparametersubmit'])->name('einvoiceparametersubmit');
// Route::post('/finalpurchaseregister', [PrintController::class, 'finalpurchaseregister'])->name('finalpurchaseregister');

//Purchase summary
Route::get('purchasesummary', [PrintController::class, 'purchasesummary'])->name('purchasesummary');
Route::post('/finalpurchasesummary', [PrintController::class, 'finalpurchasesummary'])->name('finalpurchasesummary');

//Pending Indent 
Route::get('pendingindent', [PrintController::class, 'pendingindent'])->name('pendingindent');
Route::post('/finalpendingindent', [PrintController::class, 'finalpendingindent'])->name('finalpendingindent');
Route::get('/printpendingindent', [PrintController::class, 'printpendingindent'])->name('printpendingindent');
// Gate Pass Out Routes
Route::resource('gatepassout', GatePassController::class);

// Gate Pass IN Routes (Inward Details)
Route::resource('inwarddetails', GatePassInController::class);

//pending purchase order
Route::get('pendingpurchaseorder', [PrintController::class, 'pendingpurchaseorder'])->name('pendingpurchaseorder');
Route::post('/finalpendingpurchaseorder', [PrintController::class, 'finalpendingpurchaseorder'])->name('finalpendingpurchaseorder');
Route::get('/printpendingpurchaseorder', [PrintController::class, 'printpendingpurchaseorder'])->name('printpendingpurchaseorder');

//supplier wise purchase
Route::get('supplierwisepurchase', [PrintController::class, 'supplierwisepurchase'])->name('supplierwisepurchase');
Route::get('printsupplierwisepurchase', [PrintController::class, 'printsupplierwisepurchase'])->name('printsupplierwisepurchase');

//minius stock
Route::get('miniusstock', [PrintController::class, 'miniusstock'])->name('miniusstock');
Route::get('printminiusstock', [PrintController::class, 'printminiusstock'])->name('printminiusstock');

Route::prefix('finance')->group(function () {

    Route::prefix('master')->group(function () {

        Route::get('/tdscategory', [TdsCategoryController::class, 'index'])
            ->name('finance.master.tdscategory');
        Route::post('/tdscategory/store', [TdsCategoryController::class, 'store'])
            ->name('finance.master.tdscategory.store');
        Route::get('/tdscategory/edit/{id}', [TdsCategoryController::class, 'edit'])
            ->name('finance.master.tdscategory.edit');
        Route::put('/tdscategory/update/{id}', [TdsCategoryController::class, 'update'])
            ->name('finance.master.tdscategory.update');
        Route::delete('/tdscategory/destroy/{id}', [TdsCategoryController::class, 'destroy'])
            ->name('finance.master.tdscategory.destroy');
    });

    Route::prefix('transaction')->group(function () {
        Route::get('bankreconciliation', [BankReconcilation::class, 'index'])->name('finance.transaction.bankreconciliation');
        Route::post('bankreconciliation/fetch', [BankReconcilation::class, 'ledgerfetch'])->name('finance.transaction.bankreconciliation.ledgerfetch')->middleware('report.cache');
        Route::post('bankreconciliation/ledgeramtfetch', [BankReconcilation::class, 'ledgeramtfetch'])->name('finance.transaction.bankreconciliation.ledgeramtfetch');
        Route::post('/bankreconciliation/updateledger', [BankReconcilation::class, 'updateledger'])->name('finance.transaction.bankreconciliation.updateledger');
    });
});

// TDS Report Routes (Outside finance prefix for simple URL)
Route::get('tdsreport', [FinanceController::class, 'tdsreport'])->name('tdsreport');
Route::post('tdsreport/fetch', [FinanceController::class, 'tdsreportdata'])->name('tdsreport.fetch')->middleware('report.cache');

//extra/einvoicereport
Route::get('einvoicereport', [EInvoiceParameter::class, 'einvoicereport'])->name('einvoicereport');
Route::post('einvoicereportdata', [EInvoiceParameter::class, 'einvoicereportdata'])->name('einvoicereportdata');


// Open advance List
route::get('advancelist', [Banquet::class, 'openadvancelist'])->name('advancelist');
Route::get('advancelist/data', [Banquet::class, 'advancelistData'])->name('advancelist.data');
Route::get('advancelist/delete/{docid}', [Banquet::class, 'deleteAdvance'])->name('advance.delete');
Route::get('advancelist/edit/{docid}', [Banquet::class, 'openEditAdvance'])->name('advance.edit.open');
Route::post('advancelist/editsubmit', [Banquet::class, 'editAdvanceSubmit'])->name('advance.edit.submit');
Route::get('advance/print/{docid}', [Banquet::class, 'printAdvance'])->name('advance.print');


// Tax Report Inventory
Route::get('taxreportinv', [PrintController::class, 'taxreportinv'])->name('taxreportinv');
Route::post('taxreportinvtaxcodes', [PrintController::class, 'taxreportinvtaxcodes'])->name('taxreportinvtaxcodes');
Route::post('taxreportinvdata', [PrintController::class, 'taxreportinvdata'])->name('taxreportinvdata');

// Voucher Verification Dashboard
Route::get('voucherverification', [VoucherVerification::class, 'dashboard'])->name('voucherverification');
Route::get('voucherverification/pending', [VoucherVerification::class, 'pendingVouchers'])->name('voucherverification.pending');
Route::get('/voucher-detail', [VoucherVerification::class, 'getVoucherDetail'])->name('voucher.detail');
Route::post('/voucher-verify', [VoucherVerification::class, 'verifyVoucher'])->name('voucher.verify');
Route::get('voucherverification/approved', [VoucherVerification::class, 'approvedVouchers'])->name('voucherverification.approved');
Route::get('voucherverification/rejected', [VoucherVerification::class, 'rejectedVouchers'])->name('voucherverification.rejected');
Route::post('/voucher-resubmit',       [VoucherVerification::class, 'resubmitVoucher'])->name('voucher.resubmit');
Route::post('/voucher-send-verification', [VoucherVerification::class, 'sendForVerification'])->name('voucher.sendverification');
Route::get('voucherverification/userwise',        [VoucherVerification::class, 'userWiseEntry'])->name('voucherverification.userwise');
Route::get('voucherverification/userwise-detail', [VoucherVerification::class, 'userWiseDetail'])->name('userwise.detail');


// Detailed Trial Ledger Open
Route::get('detailedtrialledger', [FinanceController::class, 'detailedTrialLedger'])->name('detailedtrialledger');
// Detailed Trial Ledger Fetch
Route::post('detailedtrialledger/fetch', [FinanceController::class, 'detailedTrialLedgerQuery'])->name('detailedtrialledger.fetch')->middleware('report.cache');
// Detailed Trial Ledger Print
Route::get('printdetailedtrialledger', [FinanceController::class, 'printDetailedTrialLedger'])->name('printdetailedtrialledger');
// Detailed Trial Ledger Excel Export
Route::get('detailedtrialledger/export', [FinanceController::class, 'exportDetailedTrialLedger'])->name('detailedtrialledger.export');

// General Ledger Open
Route::get('generalledger', [FinanceController::class, 'generalLedger'])->name('generalledger');
// General Ledger Fetch
Route::post('generalledger/fetch', [FinanceController::class, 'generalLedgerQuery'])->name('generalledger.fetch')->middleware('report.cache');
// General Ledger Print
Route::get('printgeneralledger', [FinanceController::class, 'printGeneralLedger'])->name('printgeneralledger');
// General Ledger Excel Export
Route::get('generalledger/export', [FinanceController::class, 'exportGeneralLedger'])->name('generalledger.export');
// General Ledger Account List (for filter dropdown)
Route::post('generalledger/accounts', [FinanceController::class, 'generalLedgerAccounts'])->name('generalledger.accounts');

// Day Book Open
Route::get('daybook', [FinanceController::class, 'dayBook'])->name('daybook');
// Day Book Fetch
Route::post('daybook/fetch', [FinanceController::class, 'dayBookQuery'])->name('daybook.fetch')->middleware('report.cache');
// Day Book Voucher Types (for filter dropdown)
Route::post('daybook/vtypes', [FinanceController::class, 'dayBookVtypes'])->name('daybook.vtypes');
// Day Book Print
Route::get('printdaybook', [FinanceController::class, 'printDayBook'])->name('printdaybook');
// Day Book Excel Export
Route::get('daybook/export', [FinanceController::class, 'exportDayBook'])->name('daybook.export');

// Journal Book Open (legacy JournalBook — ledger postings for a voucher type, default JV)
Route::get('journalbook', [FinanceController::class, 'journalBook'])->name('journalbook');
// Journal Book Fetch
Route::post('journalbook/fetch', [FinanceController::class, 'journalBookQuery'])->name('journalbook.fetch')->middleware('report.cache');
// Journal Book Voucher Types (for filter dropdown)
Route::post('journalbook/vtypes', [FinanceController::class, 'journalBookVtypes'])->name('journalbook.vtypes');
// Journal Book Print
Route::get('printjournalbook', [FinanceController::class, 'printJournalBook'])->name('printjournalbook');
// Journal Book Excel Export
Route::get('journalbook/export', [FinanceController::class, 'exportJournalBook'])->name('journalbook.export');

// Cash Book / Bank Book Open
Route::get('cashbankbook', [FinanceController::class, 'cashBankBook'])->name('cashbankbook');
// Cash Book / Bank Book Fetch
Route::post('cashbankbook/fetch', [FinanceController::class, 'cashBankBookQuery'])->name('cashbankbook.fetch')->middleware('report.cache');
// Cash Book / Bank Book Accounts (for filter dropdown)
Route::post('cashbankbook/accounts', [FinanceController::class, 'cashBankBookAccounts'])->name('cashbankbook.accounts');
// Cash Book / Bank Book Print
Route::get('printcashbankbook', [FinanceController::class, 'printCashBankBook'])->name('printcashbankbook');
// Cash Book / Bank Book Excel Export
Route::get('cashbankbook/export', [FinanceController::class, 'exportCashBankBook'])->name('cashbankbook.export');

// Recipe Master
Route::get('recipemaster', [RecipeMastController::class, 'recipemaster'])->name('recipemaster');
// Submit Recipe Master (new entries)
Route::post('recipemastersubmit', [RecipeMastController::class, 'recipemastersubmit'])->name('recipemastersubmit');
// Open Update Recipe Master
Route::get('updaterecipemaster/{finishcode}', [RecipeMastController::class, 'updaterecipemaster'])->name('updaterecipemaster');
// Update Recipe Master Submit
Route::post('recipemasterupdate', [RecipeMastController::class, 'recipemasterupdate'])->name('recipemasterupdate');
// Delete Recipe Master row
Route::get('deleterecipemaster/{sn}', [RecipeMastController::class, 'deleterecipemaster'])->name('deleterecipemaster');
// Print Recipe Master
Route::get('printrecipemaster', [RecipeMastController::class, 'printRecipeMaster'])->name('printrecipemaster');
// Excel Export Recipe Master
Route::get('recipemaster/export', [RecipeMastController::class, 'exportRecipeMaster'])->name('recipemaster.export');

//Open Room Status Board
Route::get('roomstatusboard', [HouseKeeping::class, 'roomstatusboard'])->name('roomstatusboard');
// Fetch Room Status Board Data
// Start Room Cleaning
Route::get('startcleaning', [HouseKeeping::class, 'startcleaning'])->name('startcleaning');
Route::post('startcleaningfetch', [HouseKeeping::class, 'startcleaningfetch'])->name('startcleaningfetch');
Route::post('submitstartcleaning', [HouseKeeping::class, 'submitstartcleaning'])->name('submitstartcleaning');

// ─── Lost & Found ────────────────────────────────────────────────────────────
Route::get('lostfound', [HouseKeeping::class, 'lostfoundform'])->name('lostfoundform');
Route::post('storelostfound', [HouseKeeping::class, 'storelostfound'])->name('storelostfound');
Route::get('lostfoundlist', [HouseKeeping::class, 'lostfoundlist'])->name('lostfoundlist');
Route::get('lostfoundedit/{id}', [HouseKeeping::class, 'editlostfound'])->name('lostfoundedit');
Route::post('updatelostfound', [HouseKeeping::class, 'updatelostfound'])->name('updatelostfound');
Route::get('lostfoundprint/{id}', [HouseKeeping::class, 'printlostfound'])->name('lostfoundprint');
Route::get('lostfoundregister', [HouseKeeping::class, 'lostfoundregister'])->name('lostfoundregister');

// ─── Laundry Send / Receive (menu link fix) ─────────────────────────────
Route::get('laundrysend', [HouseKeeping::class, 'laundrysend'])->name('laundrysend');
Route::post('storelaundrysend', [HouseKeeping::class, 'storelaundrysend'])->name('storelaundrysend');
Route::get('laundryreceive', [HouseKeeping::class, 'laundryreceive'])->name('laundryreceive');
Route::post('storelaundryreceive', [HouseKeeping::class, 'storelaundryreceive'])->name('storelaundryreceive');
Route::get('laundrysendedit/{id}', [HouseKeeping::class, 'laundrysendedit'])->name('laundrysendedit');
Route::post('updatelaundrysend', [HouseKeeping::class, 'updatelaundrysend'])->name('updatelaundrysend');
Route::get('laundryreceiveedit/{id}', [HouseKeeping::class, 'laundryreceiveedit'])->name('laundryreceiveedit');
Route::post('updatelaundryreceive', [HouseKeeping::class, 'updatelaundryreceive'])->name('updatelaundryreceive');

// ─── Damage Report ───────────────────────────────────────────────────────────
// Damage entry UI lives inside Start Cleaning (modal). The menu "Damage Entry"
// item historically pointed at /storedamagereport (a POST-only route) which
// returned 405 on GET — redirect to Start Cleaning so the menu link works.
Route::get('storedamagereport', function () {
    return redirect()->route('startcleaning');
});
Route::post('storedamagereport',  [HouseKeeping::class, 'storedamagereport'])->name('storedamagereport');
Route::get('fetchdamagereports',  [HouseKeeping::class, 'fetchdamagereports'])->name('fetchdamagereports');
Route::post('storeoutofororder',  [HouseKeeping::class, 'storeoutofororder'])->name('storeoutofororder');
Route::get('damagereport',        [HouseKeeping::class, 'damagereport'])->name('damagereport');
Route::post('fetchdamagereport',  [HouseKeeping::class, 'fetchdamagereport'])->name('fetchdamagereport');
Route::post('fetchdamagereportdata', [HouseKeeping::class, 'fetchdamagereportdata'])->name('fetchdamagereportdata');
Route::post('updatedamagereport',    [HouseKeeping::class, 'updatedamagereport'])->name('updatedamagereport');

// ─── Amenities Usage Report ──────────────────────────────────────────────────
Route::get('assignmentreport',        [HouseKeeping::class, 'assignmentreport'])->name('assignmentreport');
Route::post('fetchassignmentreport',  [HouseKeeping::class, 'fetchassignmentreport'])->name('fetchassignmentreport');

// ─── Cleaning Register Report ────────────────────────────────────────────────
Route::get('cleaningregister',        [HouseKeeping::class, 'cleaningregister'])->name('cleaningregister');
Route::post('fetchcleaningregister',  [HouseKeeping::class, 'fetchcleaningregister'])->name('fetchcleaningregister');

// ─── HK Stock Report ──────────────────────────────────────────────────────────
Route::get('hkstockreport',           [HouseKeeping::class, 'hkstockreport'])->name('hkstockreport');
Route::post('fetchhkstockreport',     [HouseKeeping::class, 'fetchhkstockreport'])->name('fetchhkstockreport');

// ─── Room Cleaning Entry ──────────────────────────────────────────────────────
Route::get('roomcleaningentry', [HouseKeeping::class, 'roomcleaningentry'])->name('roomcleaningentry');
Route::post('fetchcleaningentry', [HouseKeeping::class, 'fetchcleaningentry'])->name('fetchcleaningentry');
Route::post('submitcleaningentry', [HouseKeeping::class, 'submitcleaningentry'])->name('submitcleaningentry');
Route::get('fetchcleaningftrlist', [HouseKeeping::class, 'fetchcleaningftrlist'])->name('fetchcleaningftrlist');


// Open Service Facilities Master
Route::get('servicefacailities', [CompanyController::class, 'servicefacilities'])->name('servicefacilities');
// Store Service Facilities
Route::post('servicefacailitiesstore', [CompanyController::class, 'servicefacilitiesstore'])->name('servicefacilitiesstore');
// Update Service Facilities
Route::post('servicefacailitiesupdate', [CompanyController::class, 'servicefacilitiesupdate'])->name('servicefacilitiesupdate');
// Delete Service Facilities
Route::post('servicefacailitiesdelete', [CompanyController::class, 'servicefacilitiesdelete'])->name('servicefacilitiesdelete');

// Load Service Facilities (Inconsistency Import)
Route::get('loadservicefacilities', [HouseKeeping::class, 'servicefacilitiesloadup'])->name('loadservicefacilities');

// ─── Inspection Entry ─────────────────────────────────────────────────────────
    Route::get('Inspection',          [HouseKeeping::class, 'inspection'])->name('inspection');
    Route::post('fetchinspection',    [HouseKeeping::class, 'fetchinspection'])->name('fetchinspection');
    Route::post('submitinspection',   [HouseKeeping::class, 'submitinspection'])->name('submitinspection');

    // GM-01: Wake-up Call Booking
    Route::get('wakeuplist', [HouseKeeping::class, 'wakeuplist'])->name('wakeuplist');
    Route::post('fetchwakeupdata', [HouseKeeping::class, 'fetchwakeupdata'])->name('fetchwakeupdata');
    Route::get('openwakeupentry', [HouseKeeping::class, 'openwakeupentry'])->name('openwakeupentry');
    Route::post('submitwakeup', [HouseKeeping::class, 'submitwakeup'])->name('submitwakeup');
    Route::post('deletewakeup', [HouseKeeping::class, 'deletewakeup'])->name('deletewakeup');
    Route::get('printwakeuplist', [HouseKeeping::class, 'printwakeuplist'])->name('printwakeuplist');

    // GM-02: House Guest Messages
    Route::get('guestmessagelist', [HouseKeeping::class, 'guestmessagelist'])->name('guestmessagelist');
    Route::post('fetchguestmessagedata', [HouseKeeping::class, 'fetchguestmessagedata'])->name('fetchguestmessagedata');
    Route::get('openguestmessageentry', [HouseKeeping::class, 'openguestmessageentry'])->name('openguestmessageentry');
    Route::post('submitguestmessage', [HouseKeeping::class, 'submitguestmessage'])->name('submitguestmessage');
    Route::post('markmessagedelivered', [HouseKeeping::class, 'markmessagedelivered'])->name('markmessagedelivered');
    Route::post('deleteguestmessage', [HouseKeeping::class, 'deleteguestmessage'])->name('deleteguestmessage');
    Route::get('printguestmessagelist', [HouseKeeping::class, 'printguestmessagelist'])->name('printguestmessagelist');

// ═══════════════════════════════════════════════════════════════════════════
// MISSING REPORTS — Aging, DueList, GuestPayments (added by AI migration)
// ═══════════════════════════════════════════════════════════════════════════

// Aging Report — Debtors
Route::get('agingdr', [FinanceController::class, 'agingDr'])->name('agingdr');
Route::post('agingdr/fetch', [FinanceController::class, 'agingDrFetch'])->name('agingdr.fetch')->middleware('report.cache');

// Aging Report — Creditors
Route::get('agingcr', [FinanceController::class, 'agingCr'])->name('agingcr');
Route::post('agingcr/fetch', [FinanceController::class, 'agingCrFetch'])->name('agingcr.fetch')->middleware('report.cache');

// Aging Report — Debtors (detailed with bucket columns)
Route::get('agingrepdr', [FinanceController::class, 'agingRepDr'])->name('agingrepdr');
Route::post('agingrepdr/fetch', [FinanceController::class, 'agingRepDrFetch'])->name('agingrepdr.fetch')->middleware('report.cache');

// Aging Report — Creditors (detailed with bucket columns)
Route::get('agingrepcr', [FinanceController::class, 'agingRepCr'])->name('agingrepcr');
Route::post('agingrepcr/fetch', [FinanceController::class, 'agingRepCrFetch'])->name('agingrepcr.fetch')->middleware('report.cache');

// Due List — Customer/Debtor
Route::get('duelist', [FinanceController::class, 'dueList'])->name('duelist');
Route::post('duelist/fetch', [FinanceController::class, 'dueListFetch'])->name('duelist.fetch')->middleware('report.cache');

// Due List — Creditor Overlay
Route::get('duelistcreditoroverlay', [FinanceController::class, 'dueListCreditorOverlay'])->name('duelistcreditoroverlay');
Route::post('duelistcreditoroverlay/fetch', [FinanceController::class, 'dueListCreditorOverlayFetch'])->name('duelistcreditoroverlay.fetch')->middleware('report.cache');

// Guest Payments Report
Route::get('guestpayments', [FinanceController::class, 'guestPayments'])->name('guestpayments');
Route::post('guestpayments/fetch', [FinanceController::class, 'guestPaymentsFetch'])->name('guestpayments.fetch')->middleware('report.cache');

// Non-Transferable Accounts
Route::get('nontrans', [FinanceController::class, 'nonTrans'])->name('nontrans');
Route::post('nontrans/fetch', [FinanceController::class, 'nonTransFetch'])->name('nontrans.fetch')->middleware('report.cache');

// Loan Summary, Ledger, Register
Route::get('loanadvsumm', [FinanceController::class, 'loanAdvSumm'])->name('loanadvsumm');
Route::post('loanadvsumm/fetch', [FinanceController::class, 'loanAdvSummFetch'])->name('loanadvsumm.fetch')->middleware('report.cache');
Route::get('loanledger', [FinanceController::class, 'loanLedg'])->name('loanledger');
Route::post('loanledger/fetch', [FinanceController::class, 'loanLedgFetch'])->name('loanledger.fetch')->middleware('report.cache');
Route::get('loanregister', [FinanceController::class, 'loanReg'])->name('loanregister');
Route::post('loanregister/fetch', [FinanceController::class, 'loanRegFetch'])->name('loanregister.fetch')->middleware('report.cache');

// Customer Detail
Route::get('customerdetail', [FinanceController::class, 'customerDetail'])->name('customerdetail');
Route::post('customerdetail/fetch', [FinanceController::class, 'customerDetailFetch'])->name('customerdetail.fetch')->middleware('report.cache');
